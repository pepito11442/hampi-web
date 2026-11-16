# Despliegue gratuito de Hampi

Guía paso a paso para publicar Hampi **sin pagar nada**, con base de datos incluida.

| Pieza | Servicio | Plan | Para qué |
| --- | --- | --- | --- |
| Aplicación | [Render](https://render.com) | Free (Docker) | Ejecuta Laravel con PHP 8.3 + Apache + OPcache |
| Base de datos | [Aiven for MySQL](https://aiven.io/mysql) | Free | MySQL gestionado, 1 GB, sin tarjeta y sin fecha de vencimiento |
| Correo | [Brevo](https://www.brevo.com) | Free | Correos de recuperación de contraseña (300 al día) por API HTTPS |
| Mantenerla despierta | GitHub Actions o [cron-job.org](https://cron-job.org) | Free | Visita el portal cada 10 minutos |

> **Fotos de perfil:** se guardan reducidas (400×400 WebP, ~30 KB) **dentro de la base de datos**. El disco de Render se borra cada vez que el servicio se duerme, así que no hace falta Cloudinary ni S3.

---

## Límites del plan gratuito (léelos antes)

- **Render** duerme el servicio tras **15 minutos sin visitas**; la siguiente visita tarda ~1 minuto en despertarlo. El flujo de GitHub Actions incluido (`.github/workflows/mantener-despierto.yml`) lo evita visitando `/up` cada 10 minutos. Un servicio encendido todo el mes gasta ~730 de las 750 horas gratuitas: **no crees un segundo servicio gratuito** en la misma cuenta.
- **Render bloquea SMTP** (puertos 25, 465 y 587) en el plan gratuito. Por eso el correo sale por la API de Brevo (`MAIL_MAILER=brevo`).
- **Aiven Free**: 1 GB de almacenamiento, un solo nodo. Aiven puede apagar servicios gratuitos inactivos; el ping cada 10 minutos también los mantiene en uso. Si se apaga, entra a la consola de Aiven y pulsa *Power on*.
- No es un entorno con SLA: sirve para la demo académica y pilotos pequeños.

---

## 1. Sube el proyecto a GitHub

Si aún no está en GitHub, crea un repositorio (puede ser privado) y sube el código. El archivo `.env` **no** se sube (está en `.gitignore`).

```bash
git add .
git commit -m "Despliegue gratuito: Docker, Render y Aiven"
git push
```

## 2. Crea la base de datos en Aiven

1. Crea una cuenta en <https://console.aiven.io> (no pide tarjeta).
2. **Create service → MySQL → Free plan**. Elige la región más cercana a la de Render (por ejemplo, `do-nyc` o `aws-us-east`).
3. Espera a que el estado diga **Running** (2–5 minutos).
4. En la pestaña **Overview → Connection information** copia:
   - **Host** → `DB_HOST`
   - **Port** → `DB_PORT`
   - **User** (`avnadmin`) → `DB_USERNAME`
   - **Password** → `DB_PASSWORD`
   - **Database name** (`defaultdb`) → `DB_DATABASE`
   - **CA certificate** → pulsa *Show* y copia **todo** el texto (desde `-----BEGIN CERTIFICATE-----` hasta `-----END CERTIFICATE-----`) → `DB_SSL_CA`

## 3. (Opcional) Configura el correo con Brevo

Solo hace falta para el botón **¿Olvidaste tu contraseña?** del personal.

1. Crea una cuenta gratuita en <https://www.brevo.com>.
2. **Senders, Domains & Dedicated IPs → Senders → Add a sender**: agrega y verifica el correo que enviará los mensajes (por ejemplo, el correo del proyecto) → `MAIL_FROM_ADDRESS`.
3. **SMTP & API → API Keys → Generate a new API key** → `BREVO_API_KEY`.

Sin Brevo la app funciona igual; administración puede asignar contraseñas nuevas desde **Admin → Equipo**.

## 4. Crea el servicio en Render

1. Crea una cuenta en <https://render.com> e inicia sesión con GitHub.
2. **New → Blueprint** y elige tu repositorio. Render lee `render.yaml` y propone el servicio **hampi** con plan **Free**.
3. Completa las variables marcadas como `sync: false`:

| Variable | Valor |
| --- | --- |
| `APP_URL` | `https://hampi.onrender.com` (o el nombre que te asigne Render; puedes corregirlo después) |
| `DB_HOST`, `DB_PORT`, `DB_PASSWORD` | Los de Aiven |
| `DB_SSL_CA` | El certificado CA completo de Aiven |
| `BREVO_API_KEY`, `MAIL_FROM_ADDRESS` | Los de Brevo (o déjalos vacíos) |
| `RENIEC_API_TOKEN` | Opcional: token de apis.net.pe para autocompletar nombres |

4. Pulsa **Apply**. La primera construcción tarda ~5–8 minutos (compila los assets, instala Composer y las extensiones de PHP).
5. Al arrancar, el contenedor:
   - ejecuta las migraciones (reintenta si la base tarda en responder),
   - carga los **datos de demostración** solo si la base está vacía (`SEED_DEMO=true`),
   - cachea configuración, rutas y vistas (`php artisan optimize`).

`APP_KEY` la genera Render automáticamente; el arranque la convierte al formato que espera Laravel.

> Si prefieres no usar Blueprint: **New → Web Service → Docker**, plan **Free**, *Health Check Path* `/up`, y copia a mano las variables de `render.yaml`.

## 5. Mantén el portal despierto

**Opción A — GitHub Actions (incluido):**

1. En GitHub: **Settings → Secrets and variables → Actions → Variables → New repository variable**.
2. Nombre `HAMPI_URL`, valor `https://hampi.onrender.com` (sin `/` al final).
3. En la pestaña **Actions**, abre *Mantener despierto* y pulsa **Run workflow** una vez para probarlo.

GitHub puede retrasar unos minutos los cron y los desactiva si el repositorio pasa 60 días sin cambios.

**Opción B — cron-job.org (respaldo recomendado):** crea un *cronjob* gratuito que visite `https://hampi.onrender.com/up` cada 10 minutos.

## 6. Prueba que todo funcione

- `https://hampi.onrender.com/up` responde **200**.
- Portada → ingreso ciudadano con DNI **72345678** y código **246810** (datos demo).
- Personal de salud: DNI **45678912**, contraseña **socabaya2024**.
- Administración: **enfermeria@hampi.pe**, contraseña **socabaya2024**.

Cuando cargues datos reales:

1. Cambia `SEED_DEMO` a `false` en Render.
2. Cambia las contraseñas demo desde **Admin → Equipo** (o borra las cuentas demo).

---

## Actualizaciones

Cada `git push` a la rama principal vuelve a desplegar (autoDeploy). Las migraciones nuevas se aplican solas al arrancar.

## Solución de problemas

| Síntoma | Causa probable | Qué hacer |
| --- | --- | --- |
| El despliegue falla en *migrate* | Datos de Aiven incorrectos o servicio apagado | Revisa `DB_*`, que `DB_SSL_CA` tenga el certificado completo y que Aiven diga *Running* |
| `SQLSTATE[HY000] [2002]` / TLS | Falta el certificado | Pega el CA de Aiven en `DB_SSL_CA` (con las líneas BEGIN/END) |
| Error 500 sin detalle | Ver registros | Render → servicio → **Logs** (`LOG_CHANNEL=stderr`). No actives `APP_DEBUG` en producción salvo un momento para diagnosticar |
| Enlaces o formularios apuntan a `http://` | `APP_URL` mal escrito | Usa la URL exacta con `https://` |
| La primera visita tarda un minuto | El servicio estaba dormido | Configura el paso 5 |
| No llegan los correos de recuperación | Remitente sin verificar en Brevo | Verifica `MAIL_FROM_ADDRESS` en Brevo y revisa *Transactional → Logs* |

## Alternativas gratuitas

- **Base de datos PostgreSQL** (Neon, Supabase o Aiven PostgreSQL): la imagen ya incluye `pdo_pgsql`. Cambia `DB_CONNECTION=pgsql` y los `DB_*`; las migraciones son compatibles.
- **Correo**: cualquier proveedor con API HTTPS; con SMTP necesitarás un plan pago de Render.
