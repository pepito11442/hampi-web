#!/usr/bin/env bash
# Arranque del contenedor de Hampi.
#
# 1. Ajusta Apache al puerto que asigna el hosting ($PORT).
# 2. Prepara APP_KEY y el certificado TLS de la base de datos.
# 3. Migra la base, carga datos demo si está vacía (opcional) y cachea la
#    configuración, rutas y vistas para responder más rápido.
set -euo pipefail
cd /var/www/html

PORT="${PORT:-10000}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# --- APP_KEY -------------------------------------------------------------
# Render puede generar un valor aleatorio (generateValue) sin el prefijo
# "base64:" que espera Laravel; se deriva una clave válida de 32 bytes.
if [ -z "${APP_KEY:-}" ]; then
    echo "ERROR: falta la variable APP_KEY." >&2
    exit 1
fi
case "$APP_KEY" in
    base64:*) ;;
    *) APP_KEY="base64:$(php -r 'echo base64_encode(hash("sha256", getenv("APP_KEY"), true));')"; export APP_KEY ;;
esac

# --- Certificado CA de la base (Aiven, TiDB…) ------------------------------
# Pega el contenido del ca.pem en DB_SSL_CA (o su ruta en MYSQL_ATTR_SSL_CA).
if [ -n "${DB_SSL_CA:-}" ]; then
    printf '%s\n' "$DB_SSL_CA" > /var/www/html/storage/db-ca.pem
    export MYSQL_ATTR_SSL_CA=/var/www/html/storage/db-ca.pem
fi

# --- Base de datos ---------------------------------------------------------
# La base gratuita puede tardar unos segundos en despertar: se reintenta.
intentos=0
until php artisan migrate --force --no-interaction; do
    intentos=$((intentos + 1))
    if [ "$intentos" -ge 5 ]; then
        echo "ERROR: no se pudo migrar la base de datos tras $intentos intentos." >&2
        exit 1
    fi
    echo "La base no responde todavía; reintento en 5 s ($intentos/5)…"
    sleep 5
done

if [ "${SEED_DEMO:-false}" = "true" ]; then
    php artisan hampi:sembrar-si-vacia --no-interaction
fi

# --- Cachés de Laravel -----------------------------------------------------
php artisan storage:link --force >/dev/null 2>&1 || true
php artisan optimize --no-interaction

chown -R www-data:www-data storage bootstrap/cache

exec "$@"
