# Hampi · Portal de Salud Comunitaria (Microred Socabaya)

Aplicación web desarrollada con **Laravel** para la Microred Socabaya (Arequipa, Perú). Centraliza la información de la comunidad y el trabajo del personal de salud en tres accesos:

- **Ciudadanía:** ingreso con DNI, panel de citas, médicos de turno, campañas de vacunación, campañas preventivas y directorio de postas.
- **Personal de salud (médicos y enfermeros):** ingreso con **DNI + contraseña**, horario mensual, calendario de turnos y edición de su perfil profesional (foto, biografía, títulos, experiencia y especialidad).
- **Administración (enfermería / admisión):** panel para gestionar el rol de médicos, el pizarrín de vacunas, las cuentas del equipo, los horarios mensuales y las campañas preventivas.

> Proyecto académico. Los datos y credenciales incluidos son **de demostración**. No uses las contraseñas demo en producción.

> **Demo rápida para revisión:** abre el proyecto en [Codespaces](#codespaces). La API de RENIEC es opcional: sin token, el formulario permite escribir los nombres manualmente. El token no se incluye en el repositorio por seguridad.

---

## Tabla de contenidos

1. [Stack tecnológico](#stack-tecnológico)
2. [Requisitos](#requisitos)
3. [Instalación local](#instalación-local)
4. [Codespaces](#codespaces)
5. [Uso y rutas](#uso-y-rutas)
6. [Credenciales demo](#credenciales-demo)
7. [Horarios mensuales](#horarios-mensuales)
8. [Citas médicas](#citas-médicas)
9. [Campañas preventivas](#campañas-preventivas)
10. [Compilar assets](#compilar-assets)
11. [Tests](#tests)
12. [Base de datos](#base-de-datos)
13. [Despliegue en producción](#despliegue-en-producción)
14. [Solución rápida de problemas](#solución-rápida-de-problemas)
15. [Documentación adicional](#documentación-adicional)

---

## Stack tecnológico

| Componente | Tecnología |
| --- | --- |
| Backend | Laravel 11.57 · PHP 8.2+ con `pdo_mysql` |
| Base de datos | **MySQL 8 / MariaDB 10.4+** en desarrollo y producción |
| Tests | PHPUnit con SQLite `:memory:` (solo pruebas automáticas) |
| Frontend | Blade + Tailwind CSS 4 + Vite 8 · Node.js 22.12+ |
| Autenticación | Guard `web`, sesiones de Laravel y middleware de roles |
| Archivos | Disco `public` de Laravel para fotos de perfil |
| Entorno de desarrollo | GitHub Codespaces, XAMPP o Docker Compose |
| API externa | RENIEC vía `apis.net.pe` (opcional; sin token se ingresan los nombres manualmente) |
| Herramientas de desarrollo | Laravel Boost (dev), Composer, npm |

SQLite **ya no se usa para la aplicación**. El archivo `database/laravel` dejó de versionarse y la app espera una conexión MySQL/MariaDB. Los tests conservan SQLite en memoria porque es rápido y no requiere un servidor; esa configuración vive en `phpunit.xml`.

---

## Requisitos

- PHP **8.2 o superior** con las extensiones habituales de Laravel (`mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`) y **`pdo_mysql`**.
  - En GitHub Codespaces, el PHP preinstalado puede no incluir `pdo_mysql`; la sección [Codespaces](#codespaces) explica cómo compilarlo o usar PHP 8.3.
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
```

En XAMPP, si `php`, `composer` o `mysql` no están en el `PATH`, usa las rutas completas. Por ejemplo, en Windows:

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

Esta guía deja el proyecto funcionando en **GitHub Codespaces**: PHP, la extensión `pdo_mysql`, MySQL, dependencias de Composer, assets de Vite y el servidor de Laravel.

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
php -m | grep -i -E 'pdo|mysql'
```

Debes ver al menos:

```text
PDO
pdo_mysql
mysqlnd
```

Si **no aparece `pdo_mysql`**, sigue el paso 3. Si ya aparece, salta al paso 4.

Si no aparece `docker`, usa la opción con MariaDB del paso 4.

### 3. Instalar `pdo_mysql` si falta

En algunos Codespaces, PHP viene compilado en `/opt/php/<versión>` y **no es el PHP de Ubuntu**. En ese caso:

- `sudo apt-get install php<versión>-mysql` no funciona si Ubuntu no tiene paquetes para esa versión exacta.
- `docker-php-ext-install` no existe porque no es la imagen oficial de PHP.
- Hay que compilar la extensión con `phpize`.

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

> La extensión se pierde si reconstruyes el Codespace desde cero. Para que quede permanente, se puede agregar un `.devcontainer` con PHP y `pdo_mysql`, o repetir este paso.

#### Alternativa: usar PHP 8.3 de Ubuntu

Si no quieres compilar, puedes usar el PHP 8.3 que sí está empaquetado en Ubuntu:

```bash
sudo apt-get update
sudo apt-get install -y php8.3-cli php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-mysql
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

| Síntoma | Causa probable | Solución |
| --- | --- | --- |
| `could not find driver` | Falta `pdo_mysql` | Compila la extensión o usa `php8.3-mysql` (paso 3). |
| `php8.5-mysql` no existe en apt | El PHP del Codespace no es el de Ubuntu | Sigue el bloque de compilación del paso 3. |
| `Could not open input file: artisan` | Estás fuera de la carpeta del proyecto | `cd /workspaces/hampi-web`. |
| `Connection refused` | MySQL no está corriendo o no está listo | `docker compose ps`, espera y reintenta. |
| `Vite manifest not found` | Assets sin compilar | `npm ci && npm run build`. |
| `npm run build` falla por Node | Versión antigua de Node | `nvm install 22 && nvm use 22`. |
| `419 Page Expired` | `APP_URL` o sesión desincronizada | Ajusta `APP_URL` y ejecuta `php artisan config:clear`. |
| El puerto 8000 no abre | Visibilidad del puerto | Ponlo en **Public** en la pestaña **Ports**. |
| `Permission denied` en storage | Permisos de escritura | `chmod -R ug+rwx storage bootstrap/cache`. |
| `docker: command not found` | Docker no está disponible en ese Codespace | Usa la opción MariaDB del paso 4. |

### 11. Resumen rápido

Primera vez, después de resolver `pdo_mysql`:

```bash
cd /workspaces/hampi-web
cp .env.example .env
composer install
php artisan key:generate

docker compose up -d
php artisan migrate --seed
php artisan storage:link

npm ci
npm run build
php artisan serve --host=0.0.0.0 --port=8000
```

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

| Página | Ruta | Controlador / vista |
| --- | --- | --- |
| Inicio y acceso con DNI | `/` · `inicio` | `HampiController@index` · `hampi/login.blade.php` |
| Panel ciudadano | `/mi-panel` · `ciudadano.panel` | `HampiController@panelCiudadano` |
| Médicos del día | `/medicos-hoy` · `medicos.index` | `HampiController@verMedicos` |
| Campañas de vacunación | `/pizarrin-vacunas` · `pizarrin.index` | `HampiController@verPizarrin` |
| Campañas preventivas | `/campanas-preventivas` · `campanas.index` | `HampiController@verCampanasPreventivas` |
| Directorio de postas | `/directorio-postas` · `postas.index` | `HampiController@verPostas` |

En el menú, **“Vacunas”** lleva al pizarrín de vacunación y **“Campañas preventivas”** a la página de campañas. En la barra inferior móvil se abrevia como **“Preventivas”**. En móvil también está disponible el menú hamburguesa del encabezado. Los enlaces del portal se definen una sola vez en `config/navegacion.php`.

### Personal de salud

| Acción | Ruta | Protección |
| --- | --- | --- |
| Formulario de acceso | `/equipo/login` | Público |
| Iniciar sesión con DNI | `POST /equipo/login` | Rate limit de 5 intentos/min |
| Panel y calendario | `/empleado/panel` | `auth` + `role:doctor,enfermero,enfermera` |
| Actualizar perfil | `POST /empleado/perfil` | `auth` + rol de personal |
| Mis citas (solo médicos) | `/empleado/citas` · `empleado.citas.index` | `auth` + `role:doctor` |
| Agendar cita | `/empleado/citas/crear` · `empleado.citas.create` | `auth` + `role:doctor` |
| Guardar cita | `POST /empleado/citas` · `empleado.citas.store` | `auth` + `role:doctor` |
| Cerrar cita (atendida/cancelada) | `POST /empleado/citas/{cita}/estado` | `auth` + `role:doctor` |
| Disponibilidad JSON | `/empleado/citas/disponibilidad` | `auth` + `role:doctor` |
| Cerrar sesión | `POST /equipo/logout` | `auth` |

El trabajador solo ve **su propio** horario, su perfil y sus propias citas. Un médico o enfermero que intente entrar a `/admin/*` es redirigido a su panel. La agenda de citas está reservada al rol `doctor`; enfermería no puede crear ni cerrar citas.

### Administración

| Acción | Ruta | Protección |
| --- | --- | --- |
| Login administrativo por correo | `/personal/login` | Público |
| Panel general | `/admin/dashboard` | `auth` + `role:admin,admision` |
| Equipo de salud | `/admin/equipo` | Administración |
| Horarios mensuales | `/admin/horarios` | Administración |
| Campañas preventivas | `/admin/campanas` | Administración |

Las rutas del panel administrativo conservan los flujos existentes de rol de médicos y pizarrín de vacunas. La subnavegación `Panel · Equipo de salud · Horarios · Campañas preventivas` se comparte mediante `hampi/partials/admin-nav.blade.php`.

---

## Credenciales demo

### Administración

| Nombre | Correo | Contraseña | Dónde ingresar |
| --- | --- | --- | --- |
| Lic. Carmen Alfaro | `enfermeria@hampi.pe` | `socabaya2024` | `/personal/login` |

### Personal de salud (ingreso por DNI)

| Nombre | DNI | Contraseña | Rol |
| --- | --- | --- | --- |
| Dr. Eduardo Flores Quispe | `45678912` | `socabaya2024` | Médico |
| Dra. María Paredes Salas | `41234567` | `socabaya2024` | Médico |
| Lic. Rosa Mendoza Vargas | `47891234` | `socabaya2024` | Enfermero/a |
| Téc. Luis Huamán Ríos | `46789123` | `socabaya2024` | Enfermero/a |

Ingresan en `/equipo/login` con su **DNI** y la contraseña. La administración puede crear nuevas cuentas y cambiar contraseñas desde `/admin/equipo`.

### Ciudadanía

- DNI de demostración: **`72345678`** (Rosa Elena Quispe Mamani).
- Se ingresa desde el formulario de la portada; no requiere contraseña.
- El seeder incluye una cita de demostración para este DNI.

> Cambia todas las contraseñas demo antes de publicar el sistema. El registro de personal es cerrado: solo administración crea cuentas.

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

| Tabla | Propósito |
| --- | --- |
| `horarios` | Turnos mensuales por trabajador y fecha (con horas, inicio y fin). |
| `campanas_preventivas` | Campañas preventivas publicadas por administración. |

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

| Síntoma | Causa probable | Solución |
| --- | --- | --- |
| `SQLSTATE[HY000] [2002] Connection refused` | MySQL no está corriendo o el host/puerto no coincide | Inicia MySQL (XAMPP/Docker/servicio) y revisa `DB_HOST`/`DB_PORT`. |
| `Unknown database 'hampi'` | La base no existe | `CREATE DATABASE hampi ...` o `docker compose up -d`. |
| `SQLSTATE[HY000] [1045] Access denied` | Usuario/contraseña incorrectos | Ajusta `DB_USERNAME` y `DB_PASSWORD`; en XAMPP root suele ir sin contraseña. |
| `could not find driver` | Falta `pdo_mysql` | En local habilítalo en `php.ini` y reinicia PHP/Apache; en Codespaces sigue [Codespaces](#codespaces). |
| `php8.5-mysql` no existe en apt | El PHP del Codespace no es el de Ubuntu | Compila `pdo_mysql` como se explica en [Codespaces](#codespaces) o usa PHP 8.3. |
| `Could not open input file: artisan` | Estás fuera de la carpeta del proyecto | `cd /workspaces/hampi-web`. |
| `Vite manifest not found` | Assets sin compilar | `npm ci && npm run build`. |
| Los estilos nuevos no aparecen | Build antiguo o clase no escaneada | Vuelve a ejecutar `npm run build`. |
| `419 Page Expired` | Falta `@csrf` o sesión vencida | Añade `@csrf`; limpia configuración/caché si cambiaste el dominio. |
| `403` o redirección al entrar a `/admin` | La cuenta no tiene rol administrativo | Usa una cuenta `admin`/`admision`; el personal de salud tiene su propio panel. |
| No puedo entrar a `/empleado` con mi correo | El acceso del personal es por DNI | Entra en `/equipo/login` con DNI y contraseña. |
| `429 Too Many Requests` | Rate limit del login | Espera un minuto o limpia la caché del rate limiter. |
| Foto de perfil 404 | Falta el enlace de storage | `php artisan storage:link`. |
| Error al generar 150 h | Combinación inválida o mes sin días hábiles | Revisa el mes; el generador solo acepta años 2020–2100 y meses 1–12. |
| El puerto 8000 no abre en Codespaces | Visibilidad del puerto | Abre la pestaña **Ports** y pon el puerto en público. |
| `npm run build` falla por versión de Node | Node antiguo | Usa Node 22.12+ (Vite 8). |

---

## Documentación adicional

- [`CONTRIBUTING.md`](CONTRIBUTING.md): arquitectura, sistema de diseño, recetas y checklist de cambios.
- [`CHANGELOG.md`](CHANGELOG.md): historial de versiones y cambios relevantes.
---

## Créditos y licencias

Proyecto académico de la Microred Socabaya. Construido con [Laravel](https://laravel.com), [Tailwind CSS](https://tailwindcss.com) y [Vite](https://vite.dev). Tipografía **Instrument Sans**. Las imágenes locales son de uso demostrativo.
