# Hampi · Portal de Salud Comunitaria (Microred Socabaya)

Aplicación web desarrollada con **Laravel** que centraliza la información de la Microred Socabaya (Arequipa, Perú) para dos públicos:

- **Ciudadanía:** acceso con DNI, panel de citas, médicos de turno del día, pizarrín digital de vacunas y directorio de postas.
- **Personal de salud:** acceso protegido y panel de gestión para publicar turnos médicos y avisos de vacunas.

> Proyecto académico. Los datos y credenciales incluidos son **de demostración**.

---

## Tabla de contenidos

1. [Stack tecnológico](#stack-tecnológico)
2. [Requisitos](#requisitos)
3. [Instalación](#instalación)
4. [Uso](#uso)
5. [Compilar assets](#compilar-assets)
6. [Tests](#tests)
7. [Base de datos](#base-de-datos)
8. [Despliegue en producción](#despliegue-en-producción)
9. [Solución rápida de problemas](#solución-rápida-de-problemas)
10. [Documentación adicional](#documentación-adicional)

---

## Stack tecnológico

| Componente | Tecnología |
| --- | --- |
| Backend | Laravel 11.57 · PHP 8.2+ |
| Base de datos | SQLite (archivo `database/laravel`) |
| Vistas | Blade + componentes propios |
| Estilos | Tailwind CSS 4 (compilado con Vite) |
| Bundler | Vite 8 + `laravel-vite-plugin` |
| Tipografía | Instrument Sans (autoalojada en el build) |
| Iconos | Componente Blade `<x-icon>` con SVG locales (sin CDN) |
| JS | JavaScript progresivo en `resources/js/app.js` (sin frameworks) |

No requiere MySQL ni Livewire/Alpine/Bootstrap.

---

## Requisitos

- **PHP ≥ 8.2** con extensiones: `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`, `curl`, `fileinfo`, `tokenizer`, `xml`.
- **Composer 2**.
- **Node.js 20.19+ o 22.12+** y npm (solo para compilar CSS/JS).
- Opcional: XAMPP, Laragon u otro servidor local con PHP.

En Windows con XAMPP, PHP suele estar en `C:\xampp\php\php.exe`.

---

## Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/pepito11442/hampi-web.git
cd hampi-web

# 2. Dependencias PHP
composer install

# 3. Configuración del entorno
cp .env.example .env        # Windows: copy .env.example .env
php artisan key:generate

# 4. Dependencias frontend y compilación de assets
npm install
npm run build

# 5. Levantar el servidor
php artisan serve
```

Abre `http://localhost:8000`.

### Variable `RENIEC_API_TOKEN`

La consulta automática de DNI usa el servicio de [apis.net.pe](https://apis.net.pe/). El token se configura en `.env` y **nunca se sube al repositorio**:

```env
RENIEC_API_TOKEN=tu_token_aqui
```

Si no hay token o el servicio no responde, el formulario muestra los campos de nombres y apellidos para completarlos manualmente (no se rompe el flujo).

---

## Uso

### Rutas públicas

| Ruta | Nombre | Descripción |
| --- | --- | --- |
| `/` | `inicio` | Portada informativa + acceso ciudadano con DNI. |
| `POST /ingreso-dni` | `ciudadano.login` | Valida el DNI y abre el panel del ciudadano. |
| `/mi-panel` | `ciudadano.panel` | Citas del ciudadano (requiere sesión ciudadana). |
| `/cerrar-sesion-ciudadano` | `ciudadano.logout` | Cierra la sesión ciudadana. |
| `/medicos-hoy` | `medicos.index` | Rol de médicos del día (mañana y tarde). |
| `/pizarrin-vacunas` | `pizarrin.index` | Pizarrín digital de vacunas con filtros por stock. |
| `/directorio-postas` | `postas.index` | Directorio de establecimientos con enlace a mapas. |
| `GET /api/consultar-dni/{dni}` | — | Consulta RENIEC (JSON). Valida 8 dígitos; responde 400/404. |

### Rutas del personal de salud

| Ruta | Nombre | Descripción |
| --- | --- | --- |
| `GET /personal/login` | `login` | Formulario de acceso del personal. |
| `POST /personal/login` | `login.post` | Autenticación (RateLimiter: 5 intentos → HTTP 429). |
| `POST /personal/logout` | `logout` | Cierre de sesión. |
| `GET /admin/dashboard` | `admin.dashboard` | Panel de gestión (protegido por `auth`). |
| `POST /admin/turnos/guardar` | `admin.turno.store` | Registrar médico en el rol de hoy. |
| `POST /admin/turnos/{id}/estado` | `admin.turno.update` | Cambiar estado del médico. |
| `DELETE /admin/turnos/{id}` | `admin.turno.destroy` | Retirar médico del rol. |
| `POST /admin/pizarrin/guardar` | `admin.vacuna.store` | Publicar aviso de vacuna. |
| `DELETE /admin/pizarrin/{id}` | `admin.vacuna.destroy` | Retirar aviso de vacuna. |

### Credenciales y datos de prueba

Definidos en `database/seeders/DatabaseSeeder.php`:

| Acceso | Usuario | Clave |
| --- | --- | --- |
| Ciudadano | DNI `72345678` | — |
| Personal de salud | `enfermeria@hampi.pe` | `socabaya2024` |


### Flujo ciudadano

1. En la portada, escribe un DNI de 8 dígitos. Se consulta RENIEC automáticamente.
2. Si RENIEC responde, los nombres se completan solos; si no, se habilitan los campos manuales.
3. Al enviar, se crea/actualiza el ciudadano y se abre `/mi-panel` con sus citas reales.
4. El botón “Cerrar sesión” elimina la sesión ciudadana.

### Flujo del personal

1. Entrar a `/personal/login` y autenticarse.
2. En `/admin/dashboard` se pueden registrar médicos, cambiar su estado u retirarlos, y publicar/eliminar avisos de vacunas.
3. Los cambios se reflejan de inmediato en las páginas públicas.

---

## Compilar assets

`public/build` está **ignorado por git**: después de clonar o de cambiar CSS/JS hay que compilar.

```bash
# Desarrollo (recarga automática / HMR)
npm run dev

# Producción (genera public/build con hash de caché)
npm run build
```

Si al abrir el sitio aparece `Vite manifest not found at ...`, significa que faltó ejecutar `npm run build`.

---

## Tests

El proyecto incluye pruebas de seguridad en `tests/Feature/SecurityTest.php`:

```bash
php artisan test --testsuite=Feature
```

Pruebas actuales: rechazo de inyección en el DNI, bloqueo de fuerza bruta en el login y hash bcrypt de la contraseña.

> Nota: `phpunit.xml` referencia `tests/Unit`, que aún no existe; por eso se ejecuta por suite. Ver “Deuda técnica” en `CONTRIBUTING.md`.

---

## Base de datos

- Es **SQLite versionado**: el archivo `database/laravel` está en el repositorio con datos de demostración (posta, vacunas, turnos, ciudadano y una cita).
- Por eso, tras clonar **no necesitas migrar ni sembrar**: el sitio funciona al instante.
- El archivo también guarda **sesiones** y **caché** (drivers `database`), así que cada uso normal lo modifica. Evita ejecutar:

  ```bash
  php artisan migrate:fresh   # ⚠️ borra los datos versionados
  ```

- Para añadir o modificar tablas, crea **nuevas migraciones**:

  ```bash
  php artisan make:migration agregar_campo_x_a_tabla_y
  php artisan migrate
  ```

- Si necesitas regenerar la base demo desde cero (solo en tu entorno local):

  ```bash
  php artisan migrate:fresh --seed
  ```

---

## Despliegue en producción

1. **Configura el entorno** (`.env` de producción):

   ```env
   APP_NAME=Hampi
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://tu-dominio.pe
   APP_KEY=...              # php artisan key:generate
   RENIEC_API_TOKEN=...
   ```

2. **Instala y compila**:

   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci
   npm run build
   php artisan migrate --force   # solo si usas migraciones nuevas
   php artisan optimize
   ```

3. **DocumentRoot**: apunta el servidor web a la carpeta `public/` (nunca a la raíz del proyecto). Ejemplo para XAMPP/Apache:

   ```apache
   <VirtualHost *:80>
       ServerName hampi.local
       DocumentRoot "C:/xampp/htdocs/hampi-web/public"
       <Directory "C:/xampp/htdocs/hampi-web/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

4. **Permisos de escritura** para `storage/` y `bootstrap/cache/`.
5. Usa **HTTPS** y cambia las credenciales de demostración.

---

## Solución rápida de problemas

| Síntoma | Causa probable | Solución |
| --- | --- | --- |
| `Vite manifest not found` | Assets sin compilar | `npm install && npm run build` |
| Los estilos no cambian | Build desactualizado | `npm run build` (o `npm run dev`) |
| `php` no se reconoce | PHP fuera del PATH | Usa la ruta completa (ej. `C:\xampp\php\php.exe`) o agrégalo al PATH |
| Error `419 Page Expired` | Falta `@csrf` en un formulario | Añadir `@csrf` |
| Error `429` en el login | Rate limiter activo | Esperar 1 minuto o `php artisan cache:clear` |
| “Sin conexión” al validar DNI | Token/Fuente RENIEC | Revisar `RENIEC_API_TOKEN`; el formulario permite completar manualmente |
| Imagen o asset 404 | Archivo no versionado (build) | `npm run build`; las imágenes deben estar en `public/images/` |

Más detalles en [`CONTRIBUTING.md`](CONTRIBUTING.md).

---

## Documentación adicional

- [`CONTRIBUTING.md`](CONTRIBUTING.md) — arquitectura, sistema de diseño y guía para hacer cambios.
- [`CHANGELOG.md`](CHANGELOG.md) — historial de cambios del proyecto.

---

## Créditos y licencias

- Fotografías: [Unsplash](https://unsplash.com) (licencia Unsplash, uso libre). Reemplazables desde `public/images/`.
- Laravel, Tailwind CSS y Vite: licencias MIT.
- Tipografía Instrument Sans vía Bunny Fonts, autoalojada durante el build.
