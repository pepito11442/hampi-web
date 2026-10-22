# Hampi · Portal de Salud Comunitaria (Microred Socabaya)

Aplicación web desarrollada con **Laravel** para la Microred Socabaya (Arequipa, Perú). Centraliza la información de la comunidad y el trabajo del personal de salud en tres accesos:

- **Ciudadanía:** ingreso con DNI, panel de citas, médicos de turno, campañas de vacunación, campañas preventivas y directorio de postas.
- **Personal de salud (médicos y enfermeros):** ingreso con **DNI + contraseña**, horario mensual, calendario de turnos y edición de su perfil profesional (foto, biografía, títulos, experiencia y especialidad).
- **Administración (enfermería / admisión):** panel para gestionar el rol de médicos, el pizarrín de vacunas, las cuentas del equipo, los horarios mensuales y las campañas preventivas.

> Proyecto académico. Los datos y credenciales incluidos son **de demostración**. No uses las contraseñas demo en producción.

> **Demo rápida para revisión:** abre el proyecto en [Codespaces](#codespaces). La API de RENIEC es opcional: sin token, el formulario permite escribir los nombres manualmente. El token no se incluye en el repositorio por seguridad.

> **Rediseño visual:** la demo usa la dirección **“El parte de la posta”** (papel hueso, verde posta, sellos y filas de registro). El sistema completo está documentado en [`DESIGN.md`](DESIGN.md).

---

## Tabla de contenidos

1. [Stack tecnológico](#stack-tecnológico)
2. [Requisitos](#requisitos)
3. [Instalación local](#instalación-local)
4. [Codespaces](#codespaces)
5. [Uso y rutas](#uso-y-rutas)
6. [Credenciales demo](#credenciales-demo)
7. [Sistema visual y UI](#sistema-visual-y-ui)
8. [Accesibilidad y verificación](#accesibilidad-y-verificación)
9. [Horarios mensuales](#horarios-mensuales)
10. [Citas médicas](#citas-médicas)
11. [Campañas preventivas](#campañas-preventivas)
12. [Compilar assets](#compilar-assets)
13. [Tests](#tests)
14. [Base de datos](#base-de-datos)
15. [Despliegue en producción](#despliegue-en-producción)
16. [Solución rápida de problemas](#solución-rápida-de-problemas)
17. [Documentación adicional](#documentación-adicional)
18. [Créditos y licencias](#créditos-y-licencias)

---

## Stack tecnológico

| Componente                 | Tecnología                                                                         |
| -------------------------- | ---------------------------------------------------------------------------------- |
| Backend                    | Laravel 11.57 · PHP 8.2+ con `pdo_mysql` e `intl`                                  |
| Base de datos              | **MySQL 8 / MariaDB 10.4+** en desarrollo y producción                             |
| Tests                      | PHPUnit con SQLite `:memory:` (solo pruebas automáticas)                           |
| Frontend                   | Blade + Tailwind CSS 4 + Vite 8 · Node.js 22.12+ · fuentes autoalojadas              |
| Autenticación              | Guard `web`, sesiones de Laravel y middleware de roles                             |
| Archivos                   | Disco `public` de Laravel para fotos de perfil                                     |
| Entorno de desarrollo      | GitHub Codespaces, XAMPP o Docker Compose                                          |
| API externa                | RENIEC vía `apis.net.pe` (opcional; sin token se ingresan los nombres manualmente) |
| Herramientas de desarrollo | Laravel Boost (dev), Composer, npm                                                 |
| Sistema visual             | “El parte de la posta” · documentado en `DESIGN.md`                                |

SQLite **ya no se usa para la aplicación**. El archivo `database/laravel` dejó de versionarse y la app espera una conexión MySQL/MariaDB. Los tests conservan SQLite en memoria porque es rápido y no requiere un servidor; esa configuración vive en `phpunit.xml`.

---

## Requisitos

- PHP **8.2 o superior** con las extensiones habituales de Laravel (`mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`), **`pdo_mysql`** e **`intl`**.
    - En GitHub Codespaces, el PHP preinstalado puede no incluir `pdo_mysql` ni `intl`; la sección [Codespaces](#codespaces) explica cómo compilarlas o usar PHP 8.3.
- Composer 2.
- Node.js **22.12+** (Vite 8) y npm.
- MySQL 8 o MariaDB 10.4+.
    - En Windows puedes usar XAMPP.
    - En Linux/macOS puedes instalar MySQL/MariaDB o usar Docker.
- Opcional: Docker + Docker Compose para levantar MySQL con `docker compose up -d`.

Verifica las versiones:

```bash
php -v
composer -V
node -v
npm -v
mysql --version
php -m | grep -i -E 'pdo_mysql|intl'
```

La salida de `php -m | grep` debe incluir `pdo_mysql` e `intl`. En XAMPP, si `php`, `composer` o `mysql` no están en el `PATH`, usa las rutas completas. Por ejemplo, en Windows:

```powershell
C:\xampp\php\php.exe -v
C:\xampp\mysql\bin\mysql.exe --version
```

---

## Instalación local

### 1. Clonar e instalar dependencias PHP

```bash
git clone https://github.com/pepito11442/hampi-web.git
cd hampi-web
composer install
```

### 2. Crear la base de datos MySQL

Con XAMPP o un MySQL local (root sin contraseña, configuración habitual de desarrollo):

```bash
mysql -u root -e "CREATE DATABASE hampi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Si tu root tiene contraseña:

```bash
mysql -u root -p -e "CREATE DATABASE hampi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

O con Docker:

```bash
docker compose up -d
```

El `docker-compose.yml` crea la base `hampi` y publica MySQL en el puerto `3306`. Si ya tienes XAMPP/MySQL ocupando ese puerto, detén uno de los dos o cambia el mapeo a `3307:3306` y ajusta `DB_PORT` en el `.env`.

### 3. Configurar el entorno

```bash
cp .env.example .env
php artisan key:generate
```

Edita `.env` con tus datos de conexión. Para XAMPP/Docker con root sin contraseña:

```dotenv
APP_TIMEZONE=America/Lima

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hampi
DB_USERNAME=root
DB_PASSWORD=
```

`APP_TIMEZONE` controla la fecha que usa la agenda de médicos. El valor por defecto es `America/Lima`; si despliegas en otra zona, ajústalo para que “hoy” coincida con la fecha local.

Si usas un usuario distinto, cambia `DB_USERNAME` y `DB_PASSWORD`. Nunca subas `.env` al repositorio.

### 4. Migrar y cargar datos de demostración

```bash
php artisan migrate --seed
```

Esto crea el esquema, el administrador, cuatro cuentas de personal, un ciudadano de prueba, las campañas preventivas y los horarios del mes actual.

> En una base ya existente no uses `migrate:fresh` sin respaldo. Para un entorno local de demostración sí puedes hacer `php artisan migrate:fresh --seed`.

### 5. Enlazar el almacenamiento público

Necesario para las fotos de perfil:

```bash
php artisan storage:link
```

Si Laravel corre dentro de Docker con el proyecto montado desde Windows/WSL, el enlace absoluto puede apuntar a una ruta que no existe dentro del contenedor. En ese caso, crea un enlace **relativo**:

```bash
ln -sfn ../storage/app/public public/storage
```

### 6. Compilar assets

```bash
npm install
npm run build
```

### 7. Levantar el servidor

```bash
php artisan serve
```

Abre `http://127.0.0.1:8000`.

Si el puerto está ocupado:

```bash
php artisan serve --port=8001
```

---

## Codespaces

Esta guía deja el proyecto funcionando en **GitHub Codespaces**: PHP, las extensiones `pdo_mysql` e `intl`, MySQL, dependencias de Composer, assets de Vite y el servidor de Laravel.

> **Ruta recomendada:** crea el Codespace (paso 1) → verifica las extensiones (paso 2) → compila `pdo_mysql` e `intl` solo si faltan (paso 3) → levanta MySQL (paso 4) → configura Laravel y migra (pasos 5 y 6) → compila assets y abre el puerto 8000 (pasos 7 y 8). Al final tienes el [resumen rápido](#11-resumen-rápido).

> **Codespaces es para desarrollo y demos.** No es un hosting permanente: se suspende por inactividad, consume horas de la cuota y su URL pública es temporal. Para un enlace estable, revisa [Despliegue en producción](#despliegue-en-producción).

### 1. Crear el Codespace

1. Sube los cambios a GitHub con `git push`.
2. Abre el repositorio en GitHub.
3. Pulsa **Code** → **Codespaces** → **Create codespace on main**.
4. Espera a que termine de preparar el contenedor.

> El archivo `.env` no se versiona. La base de datos y las fotos de perfil viven dentro del Codespace; si lo eliminas, se pierden.

### 2. Verificar el entorno

En la terminal del Codespace:

```bash
php -v
composer -V
node -v
npm -v
docker --version
php -m | grep -i -E 'pdo|mysql|intl'
```

Debes ver al menos:

```text
PDO
pdo_mysql
mysqlnd
intl
```

Si **no aparece `pdo_mysql` o `intl`**, sigue el paso 3. Si ya aparecen ambos, salta al paso 4.

> Cuando MySQL esté corriendo, `php artisan db:show` debe listar las 16 tablas y terminar sin errores. Esa orden usa `intl` para formatear los números, así que también sirve como comprobación final.

Si `docker --version` falla, usa la opción con MariaDB del paso 4.

### 3. Instalar `pdo_mysql` e `intl` si faltan

En algunos Codespaces, PHP viene compilado en `/opt/php/<versión>` y **no es el PHP de Ubuntu**. En ese caso:

- `sudo apt-get install php<versión>-mysql` no funciona si Ubuntu no tiene paquetes para esa versión exacta.
- `docker-php-ext-install` no existe porque no es la imagen oficial de PHP.
- Hay que compilar cada extensión con `phpize`.

Las dos se compilan de la misma forma. Empieza por `pdo_mysql`, porque sin ella Laravel no puede conectarse a MySQL.

#### Instalar `pdo_mysql`

Primero comprueba que estén los headers de PHP y de `mysqlnd`:

```bash
PHP_CONFIG=$(which php-config)
INCLUDE_DIR=$($PHP_CONFIG --include-dir)

echo "$INCLUDE_DIR"
ls "$INCLUDE_DIR/ext/pdo/php_pdo_driver.h"
ls "$INCLUDE_DIR/ext/mysqlnd/mysqlnd.h"
```

Si `php-config` no existe, salta a la alternativa con PHP 8.3 al final de esta sección.

Instala las herramientas de compilación y compila la extensión:

```bash
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
PHP_CONFIG=$(which php-config)
EXT_DIR=$($PHP_CONFIG --extension-dir)
INI_DIR=$(php --ini | sed -n 's/^Scan for additional .ini files in: //p' | tr -d '"')

sudo apt-get update
sudo apt-get install -y autoconf gcc g++ make pkg-config libtool

cd /tmp
curl -fsSL "https://www.php.net/distributions/php-${PHP_VERSION}.tar.gz" -o "php-${PHP_VERSION}.tar.gz"
tar -xzf "php-${PHP_VERSION}.tar.gz"
cd "php-${PHP_VERSION}/ext/pdo_mysql"

phpize
./configure --with-php-config="$PHP_CONFIG" --with-pdo-mysql=mysqlnd
make -j"$(nproc)"
sudo make install

echo "extension=$EXT_DIR/pdo_mysql.so" | sudo tee "$INI_DIR/pdo_mysql.ini"

php -m | grep -i pdo_mysql
php --ri pdo_mysql
```

La última comprobación debe mostrar `pdo_mysql` y `PDO Driver for MySQL => enabled`.

Si `configure` indica que falta `mysqlnd.h`, instala la librería cliente de MySQL y vuelve a configurar:

```bash
sudo apt-get install -y libmysqlclient-dev

./configure --with-php-config="$PHP_CONFIG" --with-pdo-mysql=/usr/bin/mysql_config
make -j"$(nproc)"
sudo make install
```

#### Instalar `intl`

`intl` es obligatoria para `php artisan db:show` y para que Laravel formatee números y fechas. Si aparece `The "intl" PHP extension is required...`, compílala así:

```bash
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
PHP_CONFIG=$(which php-config)
EXT_DIR=$($PHP_CONFIG --extension-dir)
INI_DIR=$(php --ini | sed -n 's/^Scan for additional .ini files in: //p' | tr -d '"')

sudo apt-get update
sudo apt-get install -y libicu-dev

cd /tmp
curl -fsSL "https://www.php.net/distributions/php-${PHP_VERSION}.tar.gz" -o "php-${PHP_VERSION}.tar.gz"
tar -xzf "php-${PHP_VERSION}.tar.gz"
cd "php-${PHP_VERSION}/ext/intl"

phpize
./configure --with-php-config="$PHP_CONFIG" --enable-intl
make -j"$(nproc)"
sudo make install

echo "extension=$EXT_DIR/intl.so" | sudo tee "$INI_DIR/intl.ini"

php -m | grep -i intl
php --ri intl
```

`php --ri intl` debe mostrar `Internationalization support => enabled` y la versión de ICU (por ejemplo, `ICU version => 74.2`). La compilación tarda varios minutos.

Si `configure` no encuentra ICU, repite la configuración indicando la ruta del sistema:

```bash
./configure --with-php-config="$PHP_CONFIG" --enable-intl --with-icu-dir=/usr
make -j"$(nproc)"
sudo make install
```

> Las extensiones compiladas (`pdo_mysql` e `intl`) se pierden si reconstruyes el Codespace desde cero. Para que queden permanentes, se puede agregar un `.devcontainer` con PHP y ambas extensiones, o repetir estos pasos.

#### Alternativa: usar PHP 8.3 de Ubuntu

Si no quieres compilar, puedes usar el PHP 8.3 que sí está empaquetado en Ubuntu:

```bash
sudo apt-get update
sudo apt-get install -y php8.3-cli php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-mysql php8.3-intl
```

A partir de ahí, ejecuta Artisan con la ruta explícita:

```bash
/usr/bin/php8.3 artisan config:clear
/usr/bin/php8.3 artisan migrate --seed
/usr/bin/php8.3 artisan serve --host=0.0.0.0 --port=8000
```

No uses `php artisan` en este caso, porque `php` seguiría apuntando a la versión del Codespace sin `pdo_mysql`.

### 4. Levantar MySQL

#### Opción A: Docker Compose (recomendada)

```bash
docker compose up -d
docker compose ps
```

El `docker-compose.yml` crea la base `hampi`, publica MySQL en el puerto `3306` y usa `root` sin contraseña. Espera a que `hampi-mysql` aparezca como `healthy`.

Si no arranca o se reinicia:

```bash
docker compose logs mysql
```

#### Opción B: MariaDB instalado en el Codespace

Si Docker no está disponible:

```bash
sudo apt-get update
sudo apt-get install -y mariadb-server
sudo service mariadb start

sudo mariadb -e "CREATE DATABASE hampi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mariadb -e "CREATE USER 'hampi'@'%' IDENTIFIED BY 'hampi'; GRANT ALL PRIVILEGES ON hampi.* TO 'hampi'@'%'; FLUSH PRIVILEGES;"
```

Luego configura `.env`:

```dotenv
APP_TIMEZONE=America/Lima

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hampi
DB_USERNAME=hampi
DB_PASSWORD=hampi
```

### 5. Configurar Laravel y dependencias

```bash
cd /workspaces/hampi-web

cp .env.example .env
composer install
php artisan key:generate
```

Si `.env` ya existe, no lo sobrescribas: solo ejecuta `composer install` y `php artisan key:generate` si aún no tienes `APP_KEY`.

No uses `sudo composer install`: puede dejar `vendor/` con permisos de root.

Revisa en `.env`:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=America/Lima

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hampi
DB_USERNAME=root
DB_PASSWORD=
```

Si usas MariaDB, cambia `DB_USERNAME` y `DB_PASSWORD` por `hampi` / `hampi`.

Para aplicar cambios de configuración:

```bash
php artisan config:clear
```

### 6. Migrar y cargar los datos demo

```bash
php artisan migrate --seed
php artisan storage:link
```

Esto crea las tablas, el administrador, el personal de salud, el ciudadano de prueba, las campañas y los horarios del mes actual. Las credenciales demo están en [Credenciales demo](#credenciales-demo).

Si aparece `Connection refused`, MySQL todavía no está listo. Espera unos segundos y reintenta:

```bash
php artisan migrate --seed
```

No uses `migrate:fresh` sobre una base con datos que quieras conservar.

### 7. Compilar los assets

```bash
node -v
```

Vite 8 necesita **Node.js 22.12 o superior**. Si tienes una versión menor:

```bash
nvm install 22
nvm use 22
```

Luego:

```bash
npm ci
npm run build
```

`public/build` está ignorado por Git. Si no ejecutas `npm run build`, Laravel mostrará `Vite manifest not found`.

### 8. Levantar la aplicación y abrir el puerto

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

1. Abre la pestaña **Ports** del Codespace.
2. Busca el puerto **8000**.
3. Pulsa **Open in Browser**.
4. Para compartir el enlace, cambia la visibilidad del puerto a **Public**.

La URL tendrá una forma parecida a:

```text
https://TU-CODESPACE-8000.app.github.dev
```

Si hay problemas de sesión o `419 Page Expired`, actualiza `APP_URL` con esa URL y ejecuta:

```bash
php artisan config:clear
```

### 9. Token de RENIEC (opcional)

Sin token, el formulario de acceso ciudadano sigue funcionando: permite escribir nombres y apellidos manualmente. Para la revisión académica no se necesita el token; si se quiere probar el autocompletado RENIEC, el docente puede solicitar el token al autor o configurarlo como secreto. Si quieres autocompletar los datos desde RENIEC, configura:

```dotenv
RENIEC_API_TOKEN=tu_token
RENIEC_API_URL=https://api.apis.net.pe/v2/reniec/dni
```

Puedes agregarlo de dos formas:

- Directamente en el `.env` del Codespace.
- Como **Codespaces secret** en GitHub: **Settings** → **Codespaces** → **Secrets** → **New secret**, con el nombre `RENIEC_API_TOKEN`. Luego reinicia el Codespace.

> **No escribas el token real en este README ni en `.env.example`.** El repositorio es público y el token quedaría en el historial de Git; cualquiera podría usarlo y consumir tu cuota. Si el token ya se expuso, regenéralo en `apis.net.pe`.

### 10. Problemas comunes en Codespaces

| Síntoma                                | Causa probable                             | Solución                                               |
| -------------------------------------- | ------------------------------------------ | ------------------------------------------------------ |
| `could not find driver`                | Falta `pdo_mysql`                          | Compila la extensión o usa `php8.3-mysql` (paso 3).    |
| `The "intl" PHP extension is required` | Falta `intl`                               | Compila `intl` (paso 3) o usa `php8.3-intl`.           |
| `php8.5-mysql` no existe en apt        | El PHP del Codespace no es el de Ubuntu    | Sigue el bloque de compilación del paso 3.             |
| `Could not open input file: artisan`   | Estás fuera de la carpeta del proyecto     | `cd /workspaces/hampi-web`.                            |
| `Connection refused`                   | MySQL no está corriendo o no está listo    | `docker compose ps`, espera y reintenta.               |
| `Vite manifest not found`              | Assets sin compilar                        | `npm ci && npm run build`.                             |
| `npm run build` falla por Node         | Versión antigua de Node                    | `nvm install 22 && nvm use 22`.                        |
| `419 Page Expired`                     | `APP_URL` o sesión desincronizada          | Ajusta `APP_URL` y ejecuta `php artisan config:clear`. |
| El puerto 8000 no abre                 | Visibilidad del puerto                     | Ponlo en **Public** en la pestaña **Ports**.           |
| `Permission denied` en storage         | Permisos de escritura                      | `chmod -R ug+rwx storage bootstrap/cache`.             |
| `docker: command not found`            | Docker no está disponible en ese Codespace | Usa la opción MariaDB del paso 4.                      |

### 11. Resumen rápido

Primera vez, después de resolver `pdo_mysql` e `intl`:

```bash
cd /workspaces/hampi-web
cp .env.example .env
composer install
php artisan key:generate

docker compose up -d
php artisan migrate --seed
php artisan storage:link
php artisan db:show

npm ci
npm run build
php artisan serve --host=0.0.0.0 --port=8000
```

`php artisan db:show` debe mostrar las 16 tablas y terminar sin errores. Si falla, revisa el paso 3.

Para volver a levantar el proyecto en una sesión posterior:

```bash
cd /workspaces/hampi-web
docker compose up -d
php artisan serve --host=0.0.0.0 --port=8000
```

Si cambiaste estilos, vistas o archivos de `resources/`, vuelve a compilar antes de recargar:

```bash
npm run build
```

---

## Uso y rutas

### Portal público

| Página                  | Ruta                                       | Controlador / vista                               |
| ----------------------- | ------------------------------------------ | ------------------------------------------------- |
| Inicio y acceso con DNI | `/` · `inicio`                             | `HampiController@index` · `hampi/login.blade.php` |
| Panel ciudadano         | `/mi-panel` · `ciudadano.panel`            | `HampiController@panelCiudadano`                  |
| Médicos del día         | `/medicos-hoy` · `medicos.index`           | `HampiController@verMedicos`                      |
| Campañas de vacunación  | `/pizarrin-vacunas` · `pizarrin.index`     | `HampiController@verPizarrin`                     |
| Campañas preventivas    | `/campanas-preventivas` · `campanas.index` | `HampiController@verCampanasPreventivas`          |
| Directorio de postas    | `/directorio-postas` · `postas.index`      | `HampiController@verPostas`                       |

En el menú, **“Vacunas”** lleva al pizarrín de vacunación y **“Campañas preventivas”** a la página de campañas. En la barra inferior móvil se abrevia como **“Preventivas”**. En móvil también está disponible el menú hamburguesa del encabezado. Los enlaces del portal se definen una sola vez en `config/navegacion.php`.

### Personal de salud

| Acción                           | Ruta                                              | Protección                                 |
| -------------------------------- | ------------------------------------------------- | ------------------------------------------ |
| Formulario de acceso             | `/equipo/login`                                   | Público                                    |
| Iniciar sesión con DNI           | `POST /equipo/login`                              | Rate limit de 5 intentos/min               |
| Panel y calendario               | `/empleado/panel`                                 | `auth` + `role:doctor,enfermero,enfermera` |
| Actualizar perfil                | `POST /empleado/perfil`                           | `auth` + rol de personal                   |
| Mis citas (solo médicos)         | `/empleado/citas` · `empleado.citas.index`        | `auth` + `role:doctor`                     |
| Agendar cita                     | `/empleado/citas/crear` · `empleado.citas.create` | `auth` + `role:doctor`                     |
| Guardar cita                     | `POST /empleado/citas` · `empleado.citas.store`   | `auth` + `role:doctor`                     |
| Cerrar cita (atendida/cancelada) | `POST /empleado/citas/{cita}/estado`              | `auth` + `role:doctor`                     |
| Disponibilidad JSON              | `/empleado/citas/disponibilidad`                  | `auth` + `role:doctor`                     |
| Cerrar sesión                    | `POST /equipo/logout`                             | `auth`                                     |

El trabajador solo ve **su propio** horario, su perfil y sus propias citas. Un médico o enfermero que intente entrar a `/admin/*` es redirigido a su panel. La agenda de citas está reservada al rol `doctor`; enfermería no puede crear ni cerrar citas.

### Administración

| Acción                          | Ruta               | Protección                     |
| ------------------------------- | ------------------ | ------------------------------ |
| Login administrativo por correo | `/personal/login`  | Público                        |
| Panel general                   | `/admin/dashboard` | `auth` + `role:admin,admision` |
| Equipo de salud                 | `/admin/equipo`    | Administración                 |
| Horarios mensuales              | `/admin/horarios`  | Administración                 |
| Campañas preventivas            | `/admin/campanas`  | Administración                 |

Las rutas del panel administrativo conservan los flujos existentes de rol de médicos y pizarrín de vacunas. La subnavegación `Panel · Equipo de salud · Horarios · Campañas preventivas` se comparte mediante `hampi/partials/admin-nav.blade.php`.

---

## Credenciales demo

### Administración

| Nombre             | Correo                | Contraseña     | Dónde ingresar    |
| ------------------ | --------------------- | -------------- | ----------------- |
| Lic. Carmen Alfaro | `enfermeria@hampi.pe` | `socabaya2024` | `/personal/login` |

### Personal de salud (ingreso por DNI)

| Nombre                    | DNI        | Contraseña     | Rol         |
| ------------------------- | ---------- | -------------- | ----------- |
| Dr. Eduardo Flores Quispe | `45678912` | `socabaya2024` | Médico      |
| Dra. María Paredes Salas  | `41234567` | `socabaya2024` | Médico      |
| Lic. Rosa Mendoza Vargas  | `47891234` | `socabaya2024` | Enfermero/a |
| Téc. Luis Huamán Ríos     | `46789123` | `socabaya2024` | Enfermero/a |

Ingresan en `/equipo/login` con su **DNI** y la contraseña. La administración puede crear nuevas cuentas y cambiar contraseñas desde `/admin/equipo`.

### Ciudadanía

- DNI de demostración: **`72345678`** (Rosa Elena Quispe Mamani).
- Se ingresa desde el formulario de la portada; no requiere contraseña.
- El seeder incluye una cita de demostración para este DNI.

> Cambia todas las contraseñas demo antes de publicar el sistema. El registro de personal es cerrado: solo administración crea cuentas.

---

## Sistema visual y UI

La demo se rediseñó con la dirección **“El parte de la posta”**: el portal se comporta como la hoja diaria de una posta de salud, con folio, fecha, filas de registro, sellos y una fotografía prendida con cinta. No es una landing genérica de clínica ni un dashboard SaaS. El documento completo —tokens, componentes, superficies, decisiones y créditos— vive en [`DESIGN.md`](DESIGN.md).

### Identidad y tono

- **Producto:** Hampi, presentado junto a **Microred Socabaya** (Arequipa, Perú).
- **Tono:** claro, humano y directo; sin paternalismo, jerga innecesaria ni hype comercial.
- **Honestidad:** el portal se presenta siempre como **demo académica funcional** con datos simulados. No se inventan testimonios, convenios, métricas oficiales, leyes, certificaciones ni claims de producción.
- **Especificidad local:** *posta*, *microred*, *pizarrín*, *rol de atención* y *campaña preventiva* se usan como parte del dominio, no como decoración.

### Tipografía

| Rol | Fuente | Uso |
| --- | --- | --- |
| Display | **Archivo** | Titulares, nombres, números grandes y títulos de sección. |
| Texto / UI | **Instrument Sans** | Párrafos, botones, formularios y navegación. |
| Dato | **Martian Mono** | Fechas, folios, horas, DNI, códigos y estados. |

Las tres fuentes están **autoalojadas** en `public/fonts/hampi/` como WOFF2, con licencia SIL Open Font License 1.1. No se hacen peticiones a Google Fonts ni a Bunny Fonts en tiempo de ejecución. La configuración de Vite ya no contiene integración remota de fuentes.

### Paleta

| Token | Hex | Uso |
| --- | --- | --- |
| `paper-50` | `#fbfaf6` | Hojas, tarjetas y campos. |
| `paper-100` | `#f4f2ea` | Fondo único del portal. |
| `paper-200` | `#e8e5d9` | Hover y zonas secundarias. |
| `ink-950` | `#0b0f0c` | Tinta principal, cabeceras y texto de máximo contraste. |
| `ink-600` | `#37423a` | Texto secundario. |
| `ink-300` | `#aab1a6` | Reglas y bordes. |
| `brand-700` | `#17522f` | Verde posta: acción principal y enlaces. |
| `brand-900` | `#0d2f1d` | Panel editorial oscuro. |
| `signal-600` | `#c9471b` | Naranja de señal y subrayado activo. |
| `stamp-600` | `#b3261e` | Rojo sello, demo, errores y acciones destructivas. |
| `cobalt-700` | `#234a87` | Información administrativa. |
| `maize-400` | `#e7b32a` | Atención, campañas y cinta de la foto. |

El fondo se define una sola vez con `.bg-ledger` (papel hueso + retícula tenue). No hay secciones full-width opacas que rompan esa hoja. El color nunca es el único portador de un estado: los badges siempre incluyen texto y, cuando aporta, un icono.

### Composición de la portada

El primer viewport contiene, en este orden:

1. Hoja asimétrica con folio, fecha, sección y sello **DEMO ACADÉMICA**.
2. Titular “La salud de tu barrio, hoy.”.
3. Formulario de DNI integrado en la hoja (no en un hero separado).
4. Tres filas de estado: médicos en rol, vacunas disponibles y próxima campaña.
5. Foto comunitaria pequeña con cinta y crédito.
6. Acceso al directorio de postas.

Debajo aparecen los servicios en formato ledger, las preguntas frecuentes y un cierre tipo ventanilla. En 390×844 el formulario de DNI ya es visible sin desplazamiento horizontal.

### Superficies

| Superficie | Tratamiento |
| --- | --- |
| Portada y páginas públicas | Hoja publicada: más aire, foto, ledger y cierre editorial. |
| Accesos | `auth-shell` de dos columnas en escritorio; una columna en móvil. |
| Administración | “Copia de escritorio”: `page-hero`, tabs, tablas, formularios y confirmaciones. |
| Personal de salud | Hoja de turno: resumen de horas, calendario, perfil y agenda. |
| Panel ciudadano | Ficha personal: saludo, próxima cita, historial y accesos rápidos. |

Los paneles internos comparten el header y el fondo, pero no muestran la barra inferior ciudadana. En escritorio, el header incluye una barra superior de utilidades con sede, horario y acceso al panel correspondiente. En móvil, la navegación principal se resuelve con el menú hamburguesa y la barra inferior de cinco accesos.

### Componentes reutilizables

| Componente / clase | Función |
| --- | --- |
| `x-site-logo` | Logo H, wordmark, bajada de Microred y badge de Socabaya. |
| `x-page-hero` | Cabecera editorial con eyebrow, `h1`, descripción, acciones y sello. |
| `x-auth-shell` | Marco compartido de los dos accesos internos. |
| `x-empty-state` | Estado vacío con icono, explicación y acción. |
| `.sheet` / `.sheet-head` | Hoja y banda de folio con datos monoespaciados. |
| `.ledger-row` | Fila de libro para servicios y datos. |
| `.stamp` | Sello demo/estado con rotación leve. |
| `.taped-photo` | Foto con marco de papel y cinta. |
| `.data-table` | Tabla de lectura densa para administración. |
| `.input`, `.label`, `.btn`, `.badge` | Controles base con foco, estados y tamaños táctiles. |
| `.icon-seal` | Icono dentro de un sello cuadrado. |
| `hampi.partials.calendario` | Calendario mensual de escritorio y agenda vertical móvil. |

### Responsive y movimiento

- Mobile-first con revisión en **320, 390, 768, 1024 y 1440 px**.
- Controles con altura mínima de **44px** y campos con `font-size: 16px` para evitar zoom no deseado en iOS.
- En `< 1024px` se usa el menú hamburguesa; en móvil se añade la barra inferior con cinco destinos.
- Las tablas y tabs administrativos permiten desplazamiento horizontal cuando no caben, sin romper el ancho de la página.
- `data-reveal` solo oculta los bloques que están fuera del primer viewport; si JS falla, el contenido sigue visible. La animación es de 500ms y se ejecuta una sola vez.
- `prefers-reduced-motion: reduce` desactiva reveal, sello, contadores y transiciones; el contenido queda visible.
- Las animaciones decorativas no son infinitas y no bloquean la lectura ni la interacción.

### Archivos de diseño

- `resources/css/app.css`: tokens, fuentes, base, componentes, foco, fondo y motion.
- `resources/js/app.js`: menú móvil, reveal, password toggle, filtros y disponibilidad de citas.
- `resources/views/hampi/layout.blade.php`: header, navegación, fondo único, footer y barra móvil.
- `resources/views/hampi/partials/calendario.blade.php`: calendario y agenda móvil.
- `resources/views/components/`: logo, page hero, auth shell, empty state e iconos.
- `public/fonts/hampi/`: fuentes autoalojadas.
- `DESIGN.md`: documento maestro del sistema visual.

### Cómo extender la UI

1. Define si la pantalla **persuade** (público) u **opera** (panel).
2. Reutiliza `x-page-hero`, `.sheet`, `.mono-label`, `.badge`, `.btn` y `.input` antes de crear estilos nuevos.
3. Mantén un solo `h1` y una jerarquía clara de `h2`/`h3`.
4. Incluye estado vacío, error y carga; no rellenes con datos inventados.
5. Verifica teclado, foco, 320px, zoom 200%, contraste y reduced motion.
6. Si aparece un patrón nuevo, actualízalo en `DESIGN.md` y compila con `npm run build`.

---

## Accesibilidad y verificación

Objetivo declarado: **WCAG 2.2 AA** para la demo académica. La accesibilidad se trató como parte del diseño, no como un parche posterior.

### Implementado

- **Estructura:** un `h1` por pantalla, secciones con `h2`/`h3`, landmarks (`header`, `nav`, `main`, `footer`) y skip link “Saltar al contenido principal”.
- **Teclado:** todos los controles son alcanzables; foco visible con outline de 2px signal-600; los campos añaden un halo brand-100; el skip link mueve el foco a `main#contenido`.
- **Menú móvil:** `aria-expanded`, `aria-controls`, foco al primer enlace al abrir, cierre con `Escape` y retorno de foco al botón.
- **Formularios:** labels asociados, ayudas con `aria-describedby`, errores con `role="alert"` y estado de consulta RENIEC con `aria-live="polite"`.
- **Estados:** badges con texto; no se depende solo del color. Los turnos del calendario tienen etiqueta y leyenda.
- **Targets:** mínimo 44px en botones, enlaces de navegación, barra inferior, tabs y controles.
- **Contraste:** texto y estados revisados sobre papel, verde y fondos oscuros; los tamaños pequeños usan tonos ink de alto contraste.
- **Movimiento:** `prefers-reduced-motion` desactiva transiciones y animaciones; `initReveal` muestra todo de inmediato cuando corresponde.
- **Sin JS:** el portal público, los textos, el formulario de acceso y la navegación siguen siendo legibles y utilizables. El JS solo mejora la consulta RENIEC, el menú, el reveal y la carga dinámica de horarios.
- **Imágenes:** `alt` descriptivo; la foto de referencia se acredita y no se presenta como oficial.

### Evidencia de la revisión

| Verificación | Resultado |
| --- | --- |
| axe-core 4.10.2 sobre 15 pantallas | **0 violaciones** de WCAG 2.2 A/AA configuradas. |
| Foco con teclado | Skip link, foco visible, menú móvil y retorno con Escape verificados. |
| Reduced motion | Sin bloques ocultos; transiciones reducidas a ~0.01ms. |
| Responsive | Sin scroll horizontal en 320, 390, 768, 1024 y 1440 px. |
| Navegador real | Rutas públicas, logins, admin, panel del trabajador, panel ciudadano y citas renderizadas con PHP 8.3 + MySQL 8.4. |
| Consola JS | Sin errores de la aplicación en las rutas capturadas. |
| Formularios | Errores, estados vacíos, login de personal, login admin y acceso ciudadano probados. |

La auditoría automatizada no sustituye una revisión con personas usuarias ni una certificación formal. Para publicar un servicio real habría que revisar privacidad, tratamiento de datos personales, contenido institucional y flujos asistidos.

---

## Horarios mensuales

### Reglas de negocio

- **Lunes a viernes:**
    - Mañana: `07:00–13:00` → 6 h.
    - Tarde: `13:00–19:30` → 6.5 h.
    - Todo el día: `07:00–19:00` → 12 h.
- **Sábados:** solo turno completo `07:00–15:00` → 8 h.
- **Domingos:** sin atención. No se generan turnos.
- **Meta mensual:** exactamente **150 horas** por médico/enfermero.

El servicio `app/Services/HorarioGenerator.php` reparte aleatoriamente días completos, sábados, mañanas y tardes hasta sumar 150 h. Al regenerar un mes, primero elimina los turnos existentes de ese trabajador y mes, y luego inserta la nueva combinación. La tabla `horarios` tiene una restricción única por `user_id + fecha`.

### Cómo se usa

1. Administración entra a `/admin/horarios`.
2. Filtra por **Todo el equipo**, **Médicos** o **Enfermería**.
3. Elige trabajador, mes y año.
4. Pulsa **Generar horario** o **Regenerar horario**.
5. El calendario muestra los turnos del mes y el avance hacia las 150 h.
6. Puede eliminar el horario completo del mes con **Eliminar**.

El trabajador ve el mismo calendario desde `/empleado/panel`, puede navegar al mes anterior/siguiente y consultar sus horas y turnos. Si se elimina la cuenta, sus horarios se eliminan en cascada.

### Agenda del día

La página pública `/medicos-hoy`, el indicador “Médicos en el rol de hoy” del inicio y la lista del panel administrativo se construyen con `app/Services/AgendaMedica.php`:

- Toma los horarios de `horarios` para la fecha actual y solo incluye cuentas con rol `doctor` y `activo = true`.
- Conserva el rol manual de `turno_medicos` para médicos visitantes o ajustes puntuales.
- Si un médico aparece en ambos registros, se muestra una vez y se usa el consultorio del registro manual; el turno y las horas del horario mensual prevalecen.
- El estado se calcula con la hora de `APP_TIMEZONE` (`Programado`, `Atendiendo`, `Finalizado`). Un turno `completo` aparece en mañana y tarde.
- Los registros manuales no tienen fecha: representan el rol del día actual.

Los horarios son de demostración y no contemplan feriados, licencias ni cambios de último minuto; esas reglas se pueden agregar más adelante.

---

## Citas médicas

Solo los usuarios con rol `doctor` pueden agendar citas a sus pacientes desde el panel del personal. El flujo es:

1. El médico entra a `/empleado/citas` o pulsa **Mis citas** en `/empleado/panel`.
2. Busca al paciente por DNI en `/empleado/citas/crear`.
3. Si el paciente ya existe, se muestra su ficha. Si no existe, el médico completa nombres, apellidos y teléfono; la ficha se crea al confirmar la cita.
4. Elige el establecimiento, la fecha y uno de los bloques libres de 30 minutos.
5. Al confirmar, la cita queda en estado **Confirmada** y aparece de inmediato en `/mi-panel` del ciudadano.

Reglas de disponibilidad y seguridad:

- El horario se toma de `horarios` para el médico autenticado. Si no tiene turno ese día (incluidos los domingos), no se muestran bloques.
- Cada cita ocupa **30 minutos** dentro del turno y no puede estar en el pasado.
- No se permiten dos citas activas del mismo médico en el mismo bloque ni dos citas activas del mismo paciente a la misma hora.
- Los estados que ocupan agenda son `Confirmada` y `Pendiente`; al **Cancelar**, el bloque vuelve a quedar disponible.
- En **Mis citas** el médico puede marcar una cita como **Atendida** cuando su hora ya pasó, o **Cancelar** una cita activa. Las citas cerradas quedan en el historial.
- El panel ciudadano muestra el nombre del médico y la especialidad de la próxima atención.
- Si RENIEC no responde, el acceso ciudadano conserva los nombres y apellidos que registró el médico; no los reemplaza por datos de respaldo.
- Si administración intenta regenerar o eliminar un mes con citas futuras activas, la operación se bloquea para no dejar citas fuera de turno. Primero deben cancelarse o reprogramarse.

La cita guarda una instantánea de la especialidad del médico en `citas.especialidad`, y `citas.user_id` (nullable) lo relaciona con su agenda. Las citas antiguas sin médico se conservan sin cambios.

---

## Campañas preventivas

- Página pública: `/campanas-preventivas`.
- Administración: `/admin/campanas`.
- Se puede crear una campaña con título, grupo objetivo, descripción, lugar, fechas y estado de publicación.
- Las campañas **activas** aparecen en el portal público; las inactivas quedan ocultas.
- Desde el listado se puede **publicar/ocultar** o **eliminar** una campaña.
- Los estados (`En curso`, `Próxima`, `Finalizada`) se calculan a partir de las fechas.

No confundir con el **pizarrín de vacunas** (`/pizarrin-vacunas`), que sigue administrándose desde el panel general y muestra disponibilidad de vacunas.

---

## Compilar assets

```bash
# Desarrollo con recarga automática
npm run dev

# Producción (genera public/build con hash de caché)
npm run build
```

`public/build` está ignorado por Git. Después de clonar, o cada vez que cambies clases de Tailwind en Blade, vuelve a compilar. Tailwind 4 escanea `resources/views/**/*.php` y `resources/js/**/*.js`; una clase usada solo en una vista nueva necesita un nuevo build.

---

## Tests

La suite usa SQLite en memoria (`phpunit.xml`) para no depender de MySQL durante las pruebas. La aplicación real sigue usando MySQL.

```bash
php artisan test
# o solo las pruebas de funcionalidad:
php artisan test --testsuite=Feature
```

La suite actual cubre:

- Seguridad: inyección SQL en la consulta de DNI, rate limiting del login y hash Bcrypt.
- Generación de horarios: 150 h exactas, reglas de domingo/sábado y todos los meses de varios años.
- Agenda del día: los horarios generados alimentan la vista pública y el panel administrativo, se excluyen enfermeros e inactivos y se conserva el rol manual.
- Citas médicas: búsqueda por DNI, registro de paciente nuevo, bloques de 30 minutos, conflictos de agenda, cierre de citas, visibilidad del médico en el panel ciudadano y bloqueo de regeneración de horarios con citas futuras.
- Login de personal por DNI y acceso a su panel.
- Bloqueo del panel administrativo para cuentas de personal.
- Creación de cuentas de trabajadores desde administración.
- Actualización de perfil y subida de foto.
- Publicación/ocultado de campañas y visibilidad en el portal público.

Antes de un commit:

```bash
php artisan test
npm run build
```

---

## Base de datos

### Motor y configuración

- Motor principal: **MySQL/MariaDB**.
- Configuración en `.env` (`DB_CONNECTION=mysql`, host, puerto, base, usuario y contraseña).
- SQLite solo se usa en tests (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` en `phpunit.xml`).

### Tablas nuevas

| Tabla                  | Propósito                                                          |
| ---------------------- | ------------------------------------------------------------------ |
| `horarios`             | Turnos mensuales por trabajador y fecha (con horas, inicio y fin). |
| `campanas_preventivas` | Campañas preventivas publicadas por administración.                |

La tabla `citas` recibió `user_id` (nullable, FK a `users` con `nullOnDelete`) para asignar cada cita al médico que la agenda. Las citas antiguas conservan `user_id = null` y siguen visibles para el ciudadano.

La tabla `users` se amplió con `dni`, `telefono`, `especialidad`, `biografia`, `titulos`, `experiencia`, `foto_perfil` y `activo`. El campo `role` pasó de un enum cerrado a un string que admite `admin`, `admision`, `doctor`, `enfermero` y `enfermera`.

### Flujo para una base nueva

```bash
php artisan migrate --seed
php artisan storage:link
```

### Si vienes de la versión SQLite

El archivo `database/laravel` ya no se versiona ni se usa. Para conservar datos reales, expórtalos antes de cambiar de motor y migra la información con un proceso de conversión; el seeder solo crea datos de demostración. Para un entorno académico/demo, lo más simple es crear la base MySQL y ejecutar `migrate --seed`.

### Buenas prácticas

- Haz respaldo antes de `migrate:fresh`.
- No edites migraciones ya ejecutadas: crea una nueva.
- En producción usa `php artisan migrate --force` (sin `fresh`).
- Configura un usuario MySQL con permisos solo sobre la base `hampi`, no root.

---

## Despliegue en producción

1. Configura `.env` de producción:

    ```dotenv
    APP_ENV=production
    APP_DEBUG=false
    APP_URL=https://tu-dominio.example
    APP_TIMEZONE=America/Lima
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=hampi
    DB_USERNAME=hampi
    DB_PASSWORD=una-contraseña-segura
    ```

2. Instala dependencias y compila:

    ```bash
    composer install --no-dev --optimize-autoloader
    npm ci
    npm run build
    php artisan migrate --force
    php artisan storage:link
    php artisan optimize
    ```

3. Asegura permisos de escritura en `storage/` y `bootstrap/cache/`.

4. Configura HTTPS y un servidor web (Nginx/Apache) apuntando a `public/`.

5. Mantén respaldos de la base y del directorio `storage/app/public/perfiles`.

### Notas de seguridad

- No ejecutes `db:seed` en producción salvo que quieras datos demo.
- Cambia las contraseñas demo y el usuario root de MySQL.
- Las fotos se guardan en `storage/app/public/perfiles`; en varios servidores conviene moverlas a S3 u otro almacenamiento compartido.
- El sistema no incluye recuperación de contraseña por correo para el personal; administración puede asignar una nueva desde `/admin/equipo`.
- El rate limiting del login es de 5 intentos por minuto y se registra en el log.

---

## Solución rápida de problemas

| Síntoma                                     | Causa probable                                       | Solución                                                                                               |
| ------------------------------------------- | ---------------------------------------------------- | ------------------------------------------------------------------------------------------------------ |
| `SQLSTATE[HY000] [2002] Connection refused` | MySQL no está corriendo o el host/puerto no coincide | Inicia MySQL (XAMPP/Docker/servicio) y revisa `DB_HOST`/`DB_PORT`.                                     |
| `Unknown database 'hampi'`                  | La base no existe                                    | `CREATE DATABASE hampi ...` o `docker compose up -d`.                                                  |
| `SQLSTATE[HY000] [1045] Access denied`      | Usuario/contraseña incorrectos                       | Ajusta `DB_USERNAME` y `DB_PASSWORD`; en XAMPP root suele ir sin contraseña.                           |
| `could not find driver`                     | Falta `pdo_mysql`                                    | En local habilítalo en `php.ini` y reinicia PHP/Apache; en Codespaces sigue [Codespaces](#codespaces). |
| `The "intl" PHP extension is required`      | Falta `intl`                                         | En local habilita `extension=intl` en `php.ini`; en Codespaces compílala (paso 3) o usa `php8.3-intl`. |
| `php8.5-mysql` no existe en apt             | El PHP del Codespace no es el de Ubuntu              | Compila `pdo_mysql` como se explica en [Codespaces](#codespaces) o usa PHP 8.3.                        |
| `Could not open input file: artisan`        | Estás fuera de la carpeta del proyecto               | `cd /workspaces/hampi-web`.                                                                            |
| `Vite manifest not found`                   | Assets sin compilar                                  | `npm ci && npm run build`.                                                                             |
| Los estilos nuevos no aparecen              | Build antiguo o clase no escaneada                   | Vuelve a ejecutar `npm run build`.                                                                     |
| `419 Page Expired`                          | Falta `@csrf` o sesión vencida                       | Añade `@csrf`; limpia configuración/caché si cambiaste el dominio.                                     |
| `403` o redirección al entrar a `/admin`    | La cuenta no tiene rol administrativo                | Usa una cuenta `admin`/`admision`; el personal de salud tiene su propio panel.                         |
| No puedo entrar a `/empleado` con mi correo | El acceso del personal es por DNI                    | Entra en `/equipo/login` con DNI y contraseña.                                                         |
| `429 Too Many Requests`                     | Rate limit del login                                 | Espera un minuto o limpia la caché del rate limiter.                                                   |
| Foto de perfil 404 o 403                    | Falta el enlace de storage o es absoluto y el contenedor no lo resuelve | `php artisan storage:link`; si usas Docker con bind mount, `ln -sfn ../storage/app/public public/storage`. |
| Error al generar 150 h                      | Combinación inválida o mes sin días hábiles          | Revisa el mes; el generador solo acepta años 2020–2100 y meses 1–12.                                   |
| El puerto 8000 no abre en Codespaces        | Visibilidad del puerto                               | Abre la pestaña **Ports** y pon el puerto en público.                                                  |
| `npm run build` falla por versión de Node   | Node antiguo                                         | Usa Node 22.12+ (Vite 8).                                                                              |

---

## Documentación adicional

- [`DESIGN.md`](DESIGN.md): sistema visual completo, tokens, componentes, superficies, accesibilidad, créditos y checklist de demo.
- [`PRODUCT.md`](PRODUCT.md): verdad de producto, usuarios, alcance y restricciones de la demo.
- [`CHANGELOG.md`](CHANGELOG.md): historial de versiones y cambios relevantes.

---

## Créditos y licencias

### Fotografía

- **Obra:** *Young person's hand holding an old person's hand*.
- **Autor:** Kampus Production.
- **Fuente:** Pexels, foto [7551677](https://www.pexels.com/photo/young-persons-hand-holding-an-old-persons-hand-7551677/).
- **Licencia:** [Pexels License](https://www.pexels.com/license/). Permite uso gratuito; el crédito se incluye de forma voluntaria.
- **Transformación:** recorte a 1200×900 y exportación optimizada a JPG/WebP para la portada.
- **Uso:** imagen de referencia. **No** es una fotografía oficial de la Microred Socabaya ni de una posta real.

### Tipografías

- **Archivo** — SIL Open Font License 1.1.
- **Instrument Sans** — SIL Open Font License 1.1.
- **Martian Mono** — SIL Open Font License 1.1.

Las tres se sirven autoalojadas desde `public/fonts/hampi/` (WOFF2). Los textos completos de las licencias están en `public/fonts/hampi/licenses/`. No hay peticiones a Google Fonts ni Bunny Fonts en tiempo de ejecución.

### Software y datos

Proyecto académico de la Microred Socabaya. Construido con [Laravel](https://laravel.com) (MIT), [Tailwind CSS](https://tailwindcss.com) (MIT), [Vite](https://vite.dev) (MIT), [Lucide](https://lucide.dev) (ISC) y PHP. Los establecimientos, campañas, credenciales y citas son datos de demostración; no representan un servicio oficial ni una fuente institucional.
