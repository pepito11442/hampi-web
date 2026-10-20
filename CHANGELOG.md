# Changelog

Todos los cambios relevantes de este proyecto se registran aquí.

Formato inspirado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/). Fechas en `AAAA-MM-DD`.

## [Sin publicar]

### Añadido

- **Agenda de citas para médicos** (`/empleado/citas`):
  - Acceso exclusivo para el rol `doctor`, con enlace **Mis citas** en el panel del personal.
  - Búsqueda de pacientes por DNI; si no existe la ficha, el médico completa nombres, apellidos y teléfono y el ciudadano se crea al confirmar.
  - Selección de establecimiento, fecha y bloques de **30 minutos** calculados dentro del turno mensual del médico.
  - Validación de fechas pasadas, horas fuera de turno, bloques desalineados, conflictos del médico y del paciente.
  - Cierre de citas como **Atendida** o **Cancelada**; al cancelar, el bloque vuelve a quedar disponible.
  - Listado **Mis citas** con secciones de hoy, próximas e historial.
  - Visualización del nombre del médico en `/mi-panel` y en el detalle de las citas del ciudadano.
- **Migración `citas.user_id`** (nullable, FK a `users` con `nullOnDelete`) para relacionar cada cita con su médico. Las citas antiguas se conservan sin médico asignado.
- **Servicio `AgendaCitas`** y endpoint JSON `/empleado/citas/disponibilidad` para actualizar los horarios disponibles sin recargar la página.
- **Pruebas de citas médicas**: búsqueda por DNI, registro de paciente nuevo, disponibilidad, conflictos, permisos, cierre de citas y bloqueo de regeneración de horarios.
- **Agenda diaria unificada** con `app/Services/AgendaMedica.php` y `MedicoDelDia.php`: los horarios mensuales generados alimentan `/medicos-hoy`, el indicador del inicio y la lista del panel administrativo. El rol manual (`turno_medicos`) se conserva para médicos visitantes o ajustes puntuales.
- Pruebas de agenda del día: horarios visibles, exclusión de enfermeros e inactivos, turno completo en ambos bloques y deduplicación con el rol manual.

### Cambiado

- El panel ciudadano prioriza la próxima cita activa (`Confirmada` o `Pendiente`) y muestra el nombre del médico.
- La regeneración o eliminación de un horario mensual se bloquea si existen citas futuras activas en ese mes, para evitar citas fuera de turno.

### Corregido

- El acceso ciudadano conserva los nombres y apellidos de una ficha existente cuando RENIEC no responde, en lugar de reemplazarlos por “Ciudadano(a) de Socabaya”.
- La vista pública y el panel administrativo ya no muestran únicamente el rol manual antiguo: ahora reflejan los médicos programados para la fecha actual.
- Los médicos con horario y registro manual se muestran una sola vez; se usa el consultorio manual y el turno del horario mensual.
- El estado de los médicos programados se calcula según la hora (`Programado`, `Atendiendo`, `Finalizado`) y se actualizaron las horas visibles a 07:00–13:00 y 13:00–19:30.
- La zona horaria de la aplicación es configurable con `APP_TIMEZONE` y queda en `America/Lima` por defecto, para que “hoy” coincida con la fecha local.

## [2026-10-19] — Panel del personal, horarios mensuales y migración a MySQL

### Añadido

- **Panel independiente para médicos y enfermeros** (`/equipo/login` → `/empleado/panel`):
  - Acceso con DNI y contraseña creados desde administración.
  - Vista del horario mensual con calendario, navegación entre meses, total de horas y progreso hacia las 150 h.
  - Edición de foto de perfil, biografía, títulos, experiencia, especialidad, teléfono y correo.
  - Subida de fotos al disco `public` (`storage/app/public/perfiles`) con validación de tipo y tamaño.
- **Panel administrativo de equipo** (`/admin/equipo`):
  - Creación, edición, activación/desactivación y eliminación de cuentas de médicos y enfermeros.
  - Filtros por todo el equipo, médicos y enfermería.
  - Asignación de contraseña inicial y cambio de contraseña.
- **Horarios mensuales** (`/admin/horarios`):
  - Generación aleatoria por trabajador, mes y año con **exactamente 150 horas**.
  - Turnos: mañana 07:00–13:00 (6 h), tarde 13:00–19:30 (6.5 h), todo el día 07:00–19:00 (12 h) y sábados 07:00–15:00 (8 h).
  - Domingos sin atención; restricción única por trabajador y fecha.
  - Regeneración y eliminación del horario del mes; calendario compartido entre administración y personal.
- **Campañas preventivas**:
  - Nueva tabla/modelo `CampanaPreventiva` y página pública `/campanas-preventivas`.
  - Administración en `/admin/campanas`: crear, publicar/ocultar y eliminar campañas.
  - Estados calculados (`En curso`, `Próxima`, `Finalizada`) y datos de demostración en el seeder.
- **Nuevas migraciones** para campos de personal, `horarios` y `campanas_preventivas`.
- **Middleware de roles** `role:...` para separar administración, personal de salud y ciudadanía.
- **Servicio `HorarioGenerator`** con búsqueda de combinaciones válidas y transacción de reemplazo.
- **Factory `UserFactory`** y pruebas de horarios, login por DNI, permisos, perfil con foto y campañas.
- **Documentación actualizada**: README, CONTRIBUTING y este CHANGELOG.
- **Docker Compose** opcional para levantar MySQL 8 en desarrollo.
- **Laravel Boost** como dependencia de desarrollo, siguiendo las instrucciones de `AGENTS.md`.

### Cambiado

- **Migración de la base de datos de SQLite a MySQL/MariaDB**:
  - `.env.example` ahora usa `DB_CONNECTION=mysql`, base `hampi` y configuración de XAMPP/Docker.
  - `database/laravel` se retiró del control de versiones y se agregó a `database/.gitignore`.
  - SQLite queda únicamente para la suite de pruebas (`phpunit.xml`, `:memory:`).
- El campo `users.role` pasó de enum cerrado a string para admitir `admin`, `admision`, `doctor`, `enfermero` y `enfermera`.
- El login administrativo redirige según el rol; el personal de salud tiene un login y un panel propios.
- **Navegación móvil corregida**:
  - La barra inferior de cinco accesos ahora se muestra también en el inicio (antes se perdía en móvil).
  - Se mantiene el menú hamburguesa; la navbar de escritorio aparece desde `lg`.
  - La barra inferior se oculta en `/admin/*`, `/personal/*` y `/empleado/*`.
- Etiquetas de navegación: “Campañas” para el pizarrín de vacunación y “Preventivas” para las campañas preventivas; se agregó el acceso a “Campañas Preventivas”.
- La tarjeta del inicio “Pizarrín de vacunas” ahora se llama “Campañas de vacunación” y se agregó una tarjeta de “Campañas preventivas”.
- **Fondo y tema visual**:
  - La paleta `mist` pasó a celeste claro (`#f0f9ff`, `#e0f2fe`, `#bae6fd`).
  - Nuevo fondo decorativo `bg-aurora` con blobs y puntos animados; secciones principales con blanco translúcido.
  - Se reforzó el contraste de tarjetas y formularios sobre el fondo celeste.
- Se limpiaron rutas duplicadas del grupo administrativo y la ruta muerta `admin.vacuna.update`.
- Se agregó Laravel Boost a las dependencias de desarrollo, se actualizó el lockfile de Composer y se recompilaron los assets con Vite.

### Corregido

- La barra de navegación móvil ya no desaparece en el inicio.
- El acceso administrativo y el acceso del personal están separados y protegidos por rol.
- Los horarios se generan con una restricción de unicidad y se eliminan al borrar la cuenta.
- La página pública de campañas solo muestra campañas activas.

### Notas

- **Cambio incompatible:** la aplicación requiere MySQL/MariaDB; no arranca con SQLite como base principal. Ejecuta `php artisan migrate --seed` sobre una base nueva.
- Ejecuta `php artisan storage:link` para que las fotos de perfil sean accesibles.
- Credenciales demo: administración `enfermeria@hampi.pe` / `socabaya2024`; personal con DNI y contraseña `socabaya2024` (ver README).
- `public/build` sigue ignorado: ejecuta `npm install && npm run build` después de clonar.
- Los horarios no consideran feriados ni licencias; es una mejora futura documentada en CONTRIBUTING.

## [2026-10-19] — Rediseño visual del portal + datos reales

### Añadido

- Sistema de diseño Hampi (turquesa sanitaria + azul profundo) sobre Tailwind CSS 4 compilado con Vite: tokens de color, sombras, radios y componentes reutilizables (`.btn`, `.card`, `.input`, `.badge`, `.filter-pill`, ...).
- Componentes Blade `x-icon` (set de más de 50 iconos SVG locales, sin CDN), `x-badge`, `x-page-hero` y `x-empty-state`.
- Interacciones de interfaz: menú móvil accesible, sombra del encabezado al hacer scroll, animaciones de entrada con respeto por `prefers-reduced-motion`, mostrar/ocultar contraseña y filtros del pizarrín de vacunas.
- Imágenes locales para el hero y la sección comunitaria (`public/images/`, JPG + WebP) y favicon propio.
- Documentación del proyecto: `README.md`, `CONTRIBUTING.md` y este `CHANGELOG.md`.

### Cambiado

- Rediseño integral de las 8 vistas (portada, médicos, pizarrín, postas, panel ciudadano, login del personal, panel admin y error 429) con enfoque mobile-first, accesibilidad y componentes compartidos.
- Tailwind y los iconos pasan de CDN a un build propio con Vite (`npm run build`); el portal ya no depende de red para mostrarse correctamente.
- Los indicadores de la portada y las citas del panel ciudadano se alimentan de la base de datos (antes usaban contenido fijo/ficticio).
- El modelo `Cita` define conversión de `fecha_hora` y relaciones `posta`/`ciudadano` que simplifican las vistas.
- Dependencias del proyecto actualizadas/migradas a Laravel 11 en `composer.json` y `composer.lock`.

### Corregido

- El acceso con DNI ya no se bloquea si la consulta a RENIEC falla: se habilitan campos manuales como respaldo.
- Pluralización correcta de los indicadores (“1 posta” / “N postas”).
- El panel ciudadano verifica la sesión registrada antes de mostrar sus citas.

### Notas

- La consulta RENIEC se configura con `RENIEC_API_TOKEN` en `.env`; nunca se versiona.
- `public/build` está ignorado por git: tras clonar hay que ejecutar `npm install && npm run build`.

## Historial anterior (antes de este changelog)

- `a56222a` (2026-10-19) — feat: proyecto hampi completo con seguridad y panel administrativo.
- `6a891a5` (2026-10-11) — feat: integración de la API RENIEC para consulta de DNI y layout responsivo.
- `09c23f8` (2026-10-10) — commit inicial.
