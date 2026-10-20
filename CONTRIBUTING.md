# Guía de desarrollo y contribución — Hampi

Todo lo necesario para modificar el portal sin romper lo existente. Para instalar y ejecutar el proyecto, empieza por el [`README.md`](README.md).

## Índice

1. [Arquitectura](#1-arquitectura)
2. [Sistema de diseño](#2-sistema-de-diseño)
3. [Recetas: cambios frecuentes](#3-recetas-cambios-frecuentes)
4. [Reglas de oro](#4-reglas-de-oro)
5. [Flujo de trabajo con Git](#5-flujo-de-trabajo-con-git)
6. [Checklist antes de cada commit](#6-checklist-antes-de-cada-commit)
7. [Solución de problemas](#7-solución-de-problemas)
8. [Deuda técnica conocida](#8-deuda-técnica-conocida)

---

## 1. Arquitectura

Flujo de una petición:

```
routes/web.php → Controlador → Modelo (Eloquent) → Vista Blade (layout + componentes) → Assets compilados con Vite
```

### Carpetas y responsabilidades

| Archivo / carpeta | Qué contiene |
| --- | --- |
| `routes/web.php` | Rutas públicas, del personal de salud y de administración. |
| `app/Http/Controllers/HampiController.php` | Portada, acceso ciudadano, páginas públicas y panel administrativo general. |
| `app/Http/Controllers/AuthController.php` | Login/logout administrativo por correo y rate limiting. |
| `app/Http/Controllers/EmpleadoController.php` | Login por DNI, panel y perfil del personal de salud. |
| `app/Http/Controllers/EmpleadoCitaController.php` | Agenda de citas del médico: búsqueda de paciente, disponibilidad y cierre de citas. |
| `app/Http/Controllers/Admin/EquipoController.php` | CRUD de cuentas de médicos y enfermeros. |
| `app/Http/Controllers/Admin/HorarioController.php` | Consulta, generación y eliminación de horarios mensuales. |
| `app/Http/Controllers/Admin/CampanaController.php` | Crear, publicar/ocultar y eliminar campañas preventivas. |
| `app/Http/Middleware/EnsureRole.php` | Middleware `role:...`; redirige a cada panel según el rol. |
| `app/Services/HorarioGenerator.php` | Generador aleatorio que garantiza exactamente 150 h/mes. |
| `app/Services/AgendaMedica.php` + `MedicoDelDia.php` | Une horarios generados y rol manual para la agenda de médicos del día. |
| `app/Services/AgendaCitas.php` | Calcula bloques de 30 min dentro del turno y valida que una hora sea reservable. |
| `app/Models/` | `Posta`, `Ciudadano`, `VacunaAviso`, `TurnoMedico`, `Cita`, `User`, `Horario`, `CampanaPreventiva`. |
| `resources/views/hampi/layout.blade.php` | Header, menú móvil, footer y barra inferior. |
| `resources/views/hampi/partials/` | `calendario.blade.php` y `admin-nav.blade.php`. |
| `resources/views/empleado/` | Login, panel del trabajador y vistas de `citas/` (listado, formulario y parcial de cita). |
| `resources/views/hampi/admin/` | Vistas de equipo, horarios y campañas. |
| `resources/views/components/` | `x-icon`, `x-badge`, `x-page-hero`, `x-empty-state`. |
| `resources/css/app.css` | Tema Tailwind 4, componentes, fondo animado y animaciones. |
| `resources/js/app.js` | Interacciones progresivas (menú, reveal, filtros, contraseña). |
| `database/migrations/` + `database/seeders/` | Esquema MySQL y datos de demostración. |
| `database/factories/UserFactory.php` | Factory para pruebas y datos de ejemplo. |
| `docker-compose.yml` | MySQL 8 de desarrollo opcional. |
| `public/images/`, `public/favicon.svg` | Recursos estáticos. |

### Roles y autenticación

El guard `web` es el único guard. El rol se guarda en `users.role`:

| Rol | Acceso | Login |
| --- | --- | --- |
| `admin`, `admision` | `/admin/*` | `/personal/login` con correo |
| `doctor`, `enfermero`, `enfermera` | `/empleado/*` | `/equipo/login` con DNI |
| `doctor` (adicional) | `/empleado/citas/*` | Agenda de citas; enfermería no tiene acceso |
| Ciudadanía (sin cuenta `User`) | Portal público y `/mi-panel` | Formulario de DNI en la portada |

Rutas protegidas:

```php
Route::middleware(['auth', 'role:doctor,enfermero,enfermera'])
    ->prefix('empleado')
    ->name('empleado.')
    ->group(function () {
        // ...
    });

Route::middleware(['auth', 'role:admin,admision'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // ...
    });
```

`EnsureRole` también redirige a los invitados al login correcto (`/equipo/login` o `/personal/login`) y evita que un rol entre al panel de otro. Si agregas un rol nuevo, actualiza los arrays `ROLES_ADMIN` / `ROLES_PERSONAL` del modelo `User` y las rutas correspondientes.

### Base de datos

- **Desarrollo/producción:** MySQL 8 o MariaDB 10.4+.
- **Tests:** SQLite en memoria (`phpunit.xml`).
- No hay archivo SQLite versionado. `database/laravel` se retiró del repositorio y está en `database/.gitignore`.
- Las migraciones nuevas son:
  - `2026_10_19_120000_add_staff_fields_to_users_table.php`
  - `2026_10_19_120100_create_horarios_table.php`
  - `2026_10_19_120200_create_campanas_preventivas_table.php`

---

## 2. Sistema de diseño

### Paleta (definida en `resources/css/app.css`, bloque `@theme`)

| Token | Uso | Referencias clave |
| --- | --- | --- |
| `brand-*` | Turquesa sanitaria (acciones, acentos) | 500 `#14b8a6` · 600 `#0d9488` · 700 `#0f766e` |
| `ink-*` | Azul profundo (texto y contraste) | 500 `#5c7489` · 800 `#24394a` · 900 `#0f2a3d` |
| `mist-*` | Fondo celeste claro | 50 `#f0f9ff` · 100 `#e0f2fe` · 200 `#bae6fd` |
| `sand-*` | Acento cálido secundario | 100 `#f7f1e5` |

Sombras: `shadow-card`, `shadow-lift`, `shadow-glow`. Radios amplios: `rounded-4xl`, `rounded-5xl`.
Tipografía: **Instrument Sans** (400–700), cargada en `vite.config.js`.

El fondo general combina `bg-mist-50` con la capa decorativa `.bg-aurora` (blobs y puntos animados) del layout. Las secciones principales usan blanco translúcido (`bg-white/65`, `backdrop-blur-sm`) para que el fondo se perciba sin sacrificar legibilidad. Respeta `prefers-reduced-motion`.

### Componentes Blade

```blade
{{-- Icono (set local, sin CDN) --}}
<x-icon name="syringe" class="h-5 w-5 text-brand-600" />

{{-- Insignia de estado: teal (por defecto), sky, amber, red, slate --}}
<x-badge color="amber">Stock bajo</x-badge>

{{-- Encabezado de página con acción opcional --}}
<x-page-hero eyebrow="Microred Socabaya" title="Título" description="Descripción" icon="map-pin">
    <x-slot:actions>
        <a class="btn btn-primary" href="#">Acción</a>
    </x-slot:actions>
</x-page-hero>

{{-- Estado vacío --}}
<x-empty-state icon="calendar" title="Sin citas" description="Texto de apoyo.">
    <x-slot:action>
        <a class="btn btn-secondary" href="/">Volver</a>
    </x-slot:action>
</x-empty-state>
```

### Clases CSS reutilizables

| Grupo | Clases |
| --- | --- |
| Contenedor | `.container-page` |
| Botones | `.btn` + `.btn-primary` `.btn-secondary` `.btn-ghost` `.btn-danger` + `.btn-sm` `.btn-lg` |
| Tarjetas | `.card`, `.card-hover` |
| Jerarquía de texto | `.eyebrow`, `.section-title`, `.section-lead` |
| Formularios | `.input`, `.label` |
| Insignias | `.badge` + `.badge-teal` `.badge-sky` `.badge-amber` `.badge-red` `.badge-slate` |
| Navegación | `.nav-link`, `.nav-link-active` |
| Indicadores | `.stat-number`, `.stat-label` |
| Filtros | `.filter-pill` (estado activo vía `aria-pressed="true"`) |
| Fondo | `.bg-aurora`, `.bg-hero-glow`, `.bg-dot-grid` |
| Utilidades | `.mask-fade-b`, `.text-balance`, `.animate-float-soft`, `.skip-link` |

### JavaScript (atributos `data-` en `resources/js/app.js`)

| Atributo | Función |
| --- | --- |
| `data-menu-toggle` + `#mobile-menu` + `data-menu-icon-open/close` | Abrir/cerrar menú móvil (Escape, foco y `aria-expanded` incluidos). |
| `data-site-header` | Añade `.is-scrolled` al hacer scroll. |
| `data-reveal` | Animación de entrada al entrar en pantalla (opcional `style="--reveal-delay:80ms"`). |
| `data-password-toggle="idDelInput"` + `data-password-label` | Mostrar/ocultar contraseña. |
| `data-stock-filter` · `data-vaccine-card` (`data-stock`) · `data-vaccine-count` · `data-filter-empty` | Filtros del pizarrín de vacunas. |

Todo el JS es **progresivo**: si no carga, la página sigue funcionando.

### Navegación móvil

- La navbar completa se muestra desde `lg`; por debajo se usa el botón hamburguesa.
- La barra inferior de cinco accesos se muestra en todo el portal público, **incluido el inicio**, y se oculta en `/admin/*`, `/personal/*` y `/empleado/*`.
- El estado activo usa `request()->routeIs(...)`. Si agregas una página pública importante, actualiza tanto el menú de escritorio como la barra inferior en `layout.blade.php`.

---

## 3. Recetas: cambios frecuentes

### 3.1 Cambiar colores o tipografía

Edita los valores hex en el bloque `@theme` de `resources/css/app.css` y recompila:

```bash
npm run build
```

Todo el portal usa los tokens `brand-*`, `ink-*`, `mist-*` y `sand-*`, así que no hay que tocar las vistas.

### 3.2 Mapa de páginas (qué archivo editar)

| Página | Ruta | Método | Vista |
| --- | --- | --- | --- |
| Portada + acceso DNI | `/` · `inicio` | `HampiController@index` | `hampi/login.blade.php` |
| Panel ciudadano | `/mi-panel` · `ciudadano.panel` | `panelCiudadano` | `hampi/panel_paciente.blade.php` |
| Médicos del día | `/medicos-hoy` · `medicos.index` | `verMedicos` | `hampi/medicos.blade.php` |
| Campañas de vacunación | `/pizarrin-vacunas` · `pizarrin.index` | `verPizarrin` | `hampi/pizarrin.blade.php` |
| Campañas preventivas | `/campanas-preventivas` · `campanas.index` | `verCampanasPreventivas` | `hampi/campanas.blade.php` |
| Directorio de postas | `/directorio-postas` · `postas.index` | `verPostas` | `hampi/postas.blade.php` |
| Login administrativo | `/personal/login` · `login` | `AuthController@showLoginForm` | `hampi/auth_login.blade.php` |
| Login del personal | `/equipo/login` · `empleado.login` | `EmpleadoController@showLogin` | `empleado/login.blade.php` |
| Panel del trabajador | `/empleado/panel` · `empleado.panel` | `EmpleadoController@panel` | `empleado/panel.blade.php` |
| Mis citas (médico) | `/empleado/citas` · `empleado.citas.index` | `EmpleadoCitaController@index` | `empleado/citas/index.blade.php` |
| Agendar cita | `/empleado/citas/crear` · `empleado.citas.create` | `EmpleadoCitaController@create` | `empleado/citas/create.blade.php` |
| Panel admin | `/admin/dashboard` · `admin.dashboard` | `adminDashboard` | `hampi/admin_dashboard.blade.php` |
| Equipo de salud | `/admin/equipo` · `admin.equipo.index` | `Admin\EquipoController@index` | `hampi/admin/equipo.blade.php` |
| Horarios mensuales | `/admin/horarios` · `admin.horarios.index` | `Admin\HorarioController@index` | `hampi/admin/horarios.blade.php` |
| Campañas preventivas (admin) | `/admin/campanas` · `admin.campanas.index` | `Admin\CampanaController@index` | `hampi/admin/campanas.blade.php` |
| Error 429 | — | — | `errors/429.blade.php` |

### 3.3 Agregar una página nueva

1. Ruta en `routes/web.php`:

   ```php
   Route::get('/mi-pagina', [HampiController::class, 'miPagina'])->name('mi.pagina');
   ```

2. Método en `HampiController`:

   ```php
   public function miPagina()
   {
       $datos = Posta::where('estado_activo', true)->get();

       return view('hampi.mi_pagina', compact('datos'));
   }
   ```

3. Vista nueva `resources/views/hampi/mi_pagina.blade.php`:

   ```blade
   @extends('hampi.layout')

   @section('title', 'Mi página · Hampi')
   @section('meta_description', 'Descripción para buscadores.')

   @section('content')
       <x-page-hero eyebrow="Microred Socabaya" title="Mi página" description="..." icon="heart-pulse" />

       <section class="container-page py-10">
           {{-- contenido --}}
       </section>
   @endsection
   ```

4. (Opcional) Enlázala desde el menú y/o la barra inferior en `layout.blade.php`.

### 3.4 Agregar un icono

1. Añade la entrada al arreglo `$icons` de `resources/views/components/icon.blade.php` con los `<path>` de un SVG de 24×24 a trazo (stroke).
2. Úsalo: `<x-icon name="mi-icono" class="h-5 w-5" />`.

### 3.5 Formularios

- Mantén siempre `@csrf`. Para PUT/DELETE añade `@method('PUT')` o `@method('DELETE')`.
- Valida en el controlador y muestra errores en la vista:

  ```php
  $datos = $request->validate([
      'nombre' => ['required', 'string', 'max:120'],
  ]);
  ```

  ```blade
  <form method="POST" action="{{ route('admin.turno.store') }}">
      @csrf
      <input class="input" name="nombre" value="{{ old('nombre') }}" required>
      @error('nombre') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
      <button class="btn btn-primary" type="submit">Guardar</button>
  </form>
  ```

- No cambies los nombres de ruta existentes; hay enlaces y redirecciones que dependen de ellos.
- Para acciones destructivas usa `onsubmit="return confirm('...')"` y explica el alcance.

### 3.6 Imágenes y fotos de perfil

- Imágenes estáticas del portal: `public/images/` (JPG + WebP cuando sea posible), con `width`, `height` y `alt` descriptivo.
- Fotos del personal: se suben desde `/empleado/panel` y se guardan en `storage/app/public/perfiles` a través del disco `public`.
- Requiere `php artisan storage:link`.
- Validación actual: `image`, `mimes:jpg,jpeg,png,webp`, máximo 2 MB.
- Para mostrar la foto usa el accesor `$user->foto_url`; no construyas rutas a mano.
- Al reemplazar una foto, el controlador elimina la anterior.

### 3.7 Agregar un campo o tabla en la base de datos

```bash
php artisan make:migration agregar_campo_a_tabla
php artisan migrate
```

Luego actualiza: modelo (si requiere cast o relación), validación del controlador, formularios/vistas y, si aplica, `database/seeders/DatabaseSeeder.php` y `database/factories/UserFactory.php` para que la demo y las pruebas también lo incluyan.

> Nunca edites migraciones ya ejecutadas: crea una nueva. Para probar el esquema desde cero en local puedes usar `php artisan migrate:fresh --seed`, pero **nunca** en producción.

### 3.8 Menú, footer y navegación inferior

Todo vive en `resources/views/hampi/layout.blade.php`. Para marcar el enlace activo usa la clase condicional existente:

```blade
<a href="/medicos-hoy" @class(['nav-link', 'nav-link-active' => request()->routeIs('medicos.index')])>Médicos</a>
```

La barra inferior móvil tiene cinco accesos; si agregas una sección, revisa que no se desborde en 390 px. La barra se oculta en los paneles mediante la variable `$esPanel` del layout.

### 3.9 Indicadores de la portada

Se calculan en `HampiController@index` (variable `$stats`) y se muestran en `hampi/login.blade.php`. Si agregas un indicador, edita ambos a la vez y usa datos reales de la base de datos. Lo mismo aplica a las métricas del panel admin (`$personalCount`, `$horariosMes`).

### 3.10 Crear una cuenta de personal y generar su horario

1. Entra con una cuenta administrativa a `/admin/equipo`.
2. Completa nombre, DNI, correo, rol (`doctor` o `enfermero`) y contraseña.
3. El trabajador ya puede entrar en `/equipo/login` con su DNI.
4. En `/admin/horarios`, selecciónalo, elige mes/año y pulsa **Generar horario**.
5. El generador crea turnos hasta sumar exactamente 150 h; si vuelves a generar, reemplaza los turnos de ese mes.

Si cambias las reglas de turnos, edita únicamente `Horario::TURNOS` y `HorarioGenerator`; las vistas leen las etiquetas y horas desde el modelo.

### 3.11 Campañas preventivas

- Modelo: `App\Models\CampanaPreventiva`.
- Página pública: `HampiController@verCampanasPreventivas` → `hampi/campanas.blade.php`, solo campañas `activa = true`.
- Administración: `Admin\CampanaController` + `hampi/admin/campanas.blade.php`.
- Rutas: `admin.campanas.index`, `store`, `estado` (publicar/ocultar) y `destroy`.
- Al crear una campaña, valida fechas (`fecha_fin >= fecha_inicio`) y usa `activa` para controlar la visibilidad.

No confundir con `VacunaAviso`, que alimenta el pizarrín de vacunación y se administra desde el panel general.

### 3.12 Roles y permisos

- Para permitir un rol nuevo, agrégalo a `User::ROLES_ADMIN` o `User::ROLES_PERSONAL` según corresponda.
- Actualiza el middleware de la ruta y el login que redirige a cada panel.
- No basta con ocultar un enlace en la vista: la ruta debe tener `role:...`.
- El login del personal valida `activo`, rol y contraseña; las cuentas inactivas no pueden entrar.
- La agenda de citas usa `role:doctor`; el enlace **Mis citas** solo aparece para médicos y cada médico solo puede consultar o cerrar sus propias citas.

### 3.13 Agendar citas desde el panel médico

- Controlador: `App\Http\Controllers\EmpleadoCitaController`.
- Servicio: `App\Services\AgendaCitas` (`horarioDelDia`, `slots`, `slotValido`, `fechaHora`).
- Rutas: `empleado.citas.index`, `create`, `store`, `disponibilidad` y `estado`.
- Vistas: `resources/views/empleado/citas/index.blade.php`, `create.blade.php` y `partials/cita.blade.php`.
- El formulario busca por DNI. Si el ciudadano no existe, el mismo `store` lo crea dentro de una transacción antes de guardar la cita.
- La disponibilidad se genera en bloques de 30 minutos dentro del turno de `horarios`. El selector de hora se actualiza con `resources/js/app.js` (`data-citas-*`) y el endpoint JSON.
- Los estados activos son `Confirmada` y `Pendiente`; `Atendida` y `Cancelada` liberan el bloque. Un médico no puede cerrar citas de otro (404).
- Si cambias las reglas de bloques, edita `AgendaCitas` y agrega pruebas en `tests/Feature/CitaMedicaTest.php`. No dupliques la lógica en la vista.
- Si administración regenera o elimina un mes, `Admin\HorarioController` bloquea la operación cuando hay citas futuras activas asignadas a ese médico y mes.

---

## 4. Reglas de oro

1. **No toques `.env`** ni subas tokens o credenciales al repositorio.
2. **No renombres ni elimines rutas existentes**: los `name(...)` se usan con `route()` y `request()->routeIs()`.
3. Todo formulario conserva `@csrf` (y `@method` cuando corresponde).
4. **Sin CDNs nuevos**: los estilos y scripts van por Vite (`resources/...` + `npm run build`).
5. Las migraciones ya ejecutadas **no se editan**; se crea una nueva.
6. **No uses `migrate:fresh` en producción** ni sobre datos que no puedas recuperar. En local/demo es aceptable con `--seed`.
7. Mantén la accesibilidad: `alt`, `aria-*`, foco visible, contraste. Los componentes ya lo implementan; consérvalo.
8. Prueba siempre en **móvil (~390 px)** y **escritorio (~1440 px)**.
9. La app usa **MySQL/MariaDB**; no vuelvas a introducir SQLite para desarrollo o producción. SQLite queda reservado para `phpunit.xml`.
10. Si cambias el esquema, actualiza migración, modelo, validaciones, vistas, seeder/factory y pruebas.

---

## 5. Flujo de trabajo con Git

```bash
# 1. Crea una rama para tu cambio (recomendado)
git checkout -b mejora-horarios

# 2. Haz tus cambios y revisa qué se subirá
git status
git diff

# 3. Compila los assets antes de confirmar (si tocaste CSS/JS/Blade)
npm run build

# 4. Confirma y sube
git add -A
git commit -m "feat: agrega horarios mensuales del personal"
git push -u origin mejora-horarios

# 5. Abre un Pull Request en GitHub
```

Si trabajas directo sobre `main` (proyecto pequeño):

```bash
git add -A
git commit -m "feat: agrega horarios mensuales del personal"
git push origin main
```

### Convención de mensajes de commit

| Prefijo | Uso |
| --- | --- |
| `feat:` | Nueva funcionalidad o pantalla |
| `fix:` | Corrección de errores |
| `docs:` | Documentación |
| `style:` | Cambios visuales sin lógica |
| `refactor:` | Reorganización sin cambiar comportamiento |
| `test:` | Pruebas |
| `chore:` | Tareas de mantenimiento (dependencias, build) |

Estructura: primera línea corta (≤ 72 caracteres) y, si hace falta, un cuerpo con viñetas explicando el por qué.

---

## 6. Checklist antes de cada commit

- [ ] `php artisan test` termina en verde (suite completa, 30 pruebas).
- [ ] `npm run build` termina sin errores.
- [ ] `php artisan view:clear` si cambiaste vistas (y `php artisan view:cache` si quieres verificar que compilan).
- [ ] `php artisan route:list --except-vendor` no muestra rutas duplicadas ni errores.
- [ ] Si tocaste fotos o `storage`, verificaste `php artisan storage:link` y que la imagen cargue.
- [ ] Probaste el flujo correspondiente en MySQL, no solo con tests SQLite.
- [ ] Revisaste en el navegador las rutas que tocaste, en móvil y escritorio.
- [ ] `git status` no incluye `.env`, `node_modules`, `public/build`, `database/laravel` ni fotos subidas.
- [ ] Si agregaste datos demo, actualizaste `DatabaseSeeder`.
- [ ] Si agregaste reglas de negocio, agregaste o actualizaste pruebas.

---

## 7. Solución de problemas

| Síntoma | Causa | Solución |
| --- | --- | --- |
| `Vite manifest not found at ...` | Assets sin compilar | `npm install && npm run build` |
| Los estilos no reflejan tus cambios | Build antiguo | `npm run build` (o `npm run dev`) |
| Una clase de Tailwind no aplica | No existe literalmente en Blade/JS | Escribe la clase completa en el archivo; el build escanea `resources/views` |
| `php` no se reconoce en Windows | PHP fuera del PATH | Usa `C:\xampp\php\php.exe` o agrégalo al PATH |
| `SQLSTATE[HY000] [2002] Connection refused` | MySQL detenido / host incorrecto | Inicia MySQL y revisa `DB_HOST`/`DB_PORT` |
| `Unknown database 'hampi'` | Base no creada | Crea la base o ejecuta `docker compose up -d` |
| `SQLSTATE[HY000] [1045] Access denied` | Credenciales incorrectas | Ajusta `DB_USERNAME`/`DB_PASSWORD`; en XAMPP root suele estar vacío |
| `could not find driver` | Falta `pdo_mysql` | Habilítalo en `php.ini` y reinicia PHP |
| `419 Page Expired` | Falta `@csrf` o sesión vencida | Añádelo al formulario; limpia config/caché si cambió el dominio |
| `429 Too Many Requests` en el login | Rate limiter (5 intentos/min) | Espera un minuto o `php artisan cache:clear` |
| El personal no puede entrar con correo | El acceso del personal es por DNI | Usa `/equipo/login`; el correo es solo para administración |
| Un médico entra a `/admin` y vuelve a su panel | El middleware de roles lo redirige | Es el comportamiento esperado; usa una cuenta admin |
| “Sin conexión” al validar DNI | RENIEC no responde o falta token | Revisa `RENIEC_API_TOKEN`; el formulario permite completar a mano |
| Foto 404 | Falta el enlace de storage | `php artisan storage:link` |
| No se genera el horario | Mes/año fuera de rango o error del generador | Usa 2020–2100 y meses 1–12; revisa `storage/logs/laravel.log` |
| Cambios de vistas no se ven en producción | Vistas/optimización cacheadas | `php artisan view:clear && php artisan optimize` |
| Se pierde la sesión | Driver de sesión | `SESSION_DRIVER=database` requiere la tabla `sessions` (ya incluida) |

---

## 8. Deuda técnica conocida

1. **`storeVacuna` y `storeTurno` fijan `posta_id = 1`**: si se registran más postas, hay que permitir elegir la posta en el formulario y validarla.
2. **Modelo `Posta` con relación autorreferente `posta()`**: código heredado; revísalo antes de usarlo.
3. **Ruta `/test`** devuelve un string de depuración; puedes eliminarla cuando ya no la necesites.
4. **Campañas preventivas sin edición completa**: actualmente se crean, publican/ocultan y eliminan. Si se necesita corregir textos o fechas, conviene agregar `update` y un formulario de edición.
5. **Horarios aleatorios sin feriados/licencias**: el generador cumple 150 h y las reglas de turnos, pero no descuenta feriados ni ausencias. Una mejora futura es cargar un calendario laboral y bloqueos por trabajador.
6. **Fotos en almacenamiento local**: para varios servidores o despliegues efímeros (Codespaces), usa S3 u otro disco compartido y ajusta `foto_url`/`store`.
7. **Sin recuperación de contraseña por correo**: administración asigna una nueva contraseña desde `/admin/equipo`. Se puede añadir un flujo de restablecimiento si el proyecto lo requiere.
8. **Acceso ciudadano por DNI sin validación fuerte en servidor**: el flujo existente prioriza la demo y la consulta RENIEC; endurecerlo requiere validar formato, verificar identidad y evitar crear ciudadanos con datos arbitrarios.
9. **Sin auditoría de cambios**: no se registra quién generó/regeneró un horario o publicó una campaña. Si el sistema crece, añade una tabla de auditoría.
