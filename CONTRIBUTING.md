# Guía de desarrollo y contribución — Hampi

Todo lo necesario para modificar el portal sin romper lo existente.

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

| Archivo / carpeta | Qué contiene |
| --- | --- |
| `routes/web.php` | Todas las rutas del portal y del panel. |
| `app/Http/Controllers/HampiController.php` | Portada, acceso por DNI, panel ciudadano y panel admin. |
| `app/Http/Controllers/AuthController.php` | Login/logout del personal + rate limiting (429). |
| `app/Models/` | `Posta`, `Ciudadano`, `VacunaAviso`, `TurnoMedico`, `Cita`, `User`. |
| `resources/views/hampi/layout.blade.php` | Estructura común: header, menú móvil, footer y navegación inferior. |
| `resources/views/hampi/*.blade.php` | Una vista por página. |
| `resources/views/components/` | Componentes reutilizables: `x-icon`, `x-badge`, `x-page-hero`, `x-empty-state`. |
| `resources/css/app.css` | Tema de Tailwind CSS 4: tokens de color, componentes y animaciones. |
| `resources/js/app.js` | Interacciones de interfaz (menú, reveal, filtros, contraseña). |
| `database/migrations/` + `database/seeders/` | Esquema y datos de demostración. |
| `database/laravel` | Base de datos SQLite (versionada). |
| `public/images/`, `public/favicon.svg` | Recursos estáticos. |
| `vite.config.js` | Entradas de CSS/JS y carga de la tipografía. |

---

## 2. Sistema de diseño

### Paleta (definida en `resources/css/app.css`, bloque `@theme`)

| Token | Uso | Referencias clave |
| --- | --- | --- |
| `brand-*` | Turquesa sanitaria (acciones, acentos) | 500 `#14b8a6` · 600 `#0d9488` · 700 `#0f766e` |
| `ink-*` | Azul profundo (texto y contraste) | 500 `#5c7489` · 800 `#24394a` · 900 `#0f2a3d` |
| `mist-*` | Fondo general claro | 50 `#f7fbfa` |
| `sand-*` | Acento cálido secundario | 100 `#f7f1e5` |

Sombras: `shadow-card`, `shadow-lift`, `shadow-glow`. Radios amplios: `rounded-4xl`, `rounded-5xl`.
Tipografía: **Instrument Sans** (400–700), cargada en `vite.config.js`.

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
| Utilidades | `.bg-hero-glow`, `.bg-dot-grid`, `.mask-fade-b`, `.text-balance`, `.animate-float-soft`, `.skip-link` |

### JavaScript (atributos `data-` en `resources/js/app.js`)

| Atributo | Función |
| --- | --- |
| `data-menu-toggle` + `#mobile-menu` + `data-menu-icon-open/close` | Abrir/cerrar menú móvil (Escape, foco y `aria-expanded` incluidos). |
| `data-site-header` | Añade `.is-scrolled` al hacer scroll. |
| `data-reveal` | Animación de entrada al entrar en pantalla (opcional `style="--reveal-delay:80ms"`). |
| `data-password-toggle="idDelInput"` + `data-password-label` | Mostrar/ocultar contraseña. |
| `data-stock-filter` · `data-vaccine-card` (`data-stock`) · `data-vaccine-count` · `data-filter-empty` | Filtros del pizarrín de vacunas. |

Todo el JS es **progresivo**: si no carga, la página sigue funcionando.

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
| Pizarrín de vacunas | `/pizarrin-vacunas` · `pizarrin.index` | `verPizarrin` | `hampi/pizarrin.blade.php` |
| Directorio de postas | `/directorio-postas` · `postas.index` | `verPostas` | `hampi/postas.blade.php` |
| Login del personal | `/personal/login` · `login` | `AuthController@showLoginForm` | `hampi/auth_login.blade.php` |
| Panel admin | `/admin/dashboard` · `admin.dashboard` | `adminDashboard` | `hampi/admin_dashboard.blade.php` |
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

4. (Opcional) Enlázala desde el menú en `layout.blade.php`.

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

### 3.6 Imágenes

- Guárdalas en `public/images/` (JPG + WebP cuando sea posible).
- En las vistas usa rutas relativas (`/images/...`), con `width`, `height` y `alt` descriptivo.
- El hero mantiene ~1600 px de ancho; no subas archivos de varios MB.

### 3.7 Agregar un campo o tabla en la base de datos

```bash
php artisan make:migration agregar_campo_a_tabla
php artisan migrate
```

Luego actualiza: modelo (si requiere cast o relación), validación del controlador, formularios/vistas y, si aplica, `database/seeders/DatabaseSeeder.php` para que la demo también lo incluya.

> Nunca edites migraciones ya ejecutadas: crea una nueva.

### 3.8 Menú, footer y navegación inferior

Todo vive en `resources/views/hampi/layout.blade.php`. Para marcar el enlace activo usa la clase condicional existente:

```blade
<a href="/medicos-hoy" @class(['nav-link', 'nav-link-active' => request()->routeIs('medicos.index')])>Médicos</a>
```

### 3.9 Indicadores de la portada

Se calculan en `HampiController@index` (variable `$stats`) y se muestran en `hampi/login.blade.php`. Si agregas un indicador, edita ambos a la vez y usa datos reales de la base de datos.

---

## 4. Reglas de oro

1. **No toques `.env`** ni subas tokens o credenciales al repositorio.
2. **No renombres ni elimines rutas existentes**: los `name(...)` se usan con `route()` y `request()->routeIs()`.
3. Todo formulario conserva `@csrf` (y `@method` cuando corresponde).
4. **Sin CDNs nuevos**: los estilos y scripts van por Vite (`resources/...` + `npm run build`).
5. Las migraciones ya ejecutadas **no se editan**; se crea una nueva.
6. **No ejecutes `migrate:fresh`** sobre `database/laravel` versionada: borra los datos de demostración.
7. Mantén la accesibilidad: `alt`, `aria-*`, foco visible, contraste. Los componentes ya lo implementan; consérvalo.
8. Prueba siempre en **móvil (~390 px)** y **escritorio (~1440 px)**.

---

## 5. Flujo de trabajo con Git

```bash
# 1. Crea una rama para tu cambio (recomendado)
git checkout -b mejora-pizarrin

# 2. Haz tus cambios y revisa qué se subirá
git status
git diff

# 3. Compila los assets antes de confirmar (si tocaste CSS/JS/Blade)
npm run build

# 4. Confirma y sube
git add -A
git commit -m "feat: filtra vacunas por stock"
git push -u origin mejora-pizarrin

# 5. Abre un Pull Request en GitHub
```

Si trabajas directo sobre `main` (proyecto pequeño):

```bash
git add -A
git commit -m "feat: filtra vacunas por stock"
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

- [ ] `npm run build` termina sin errores.
- [ ] `php artisan view:clear` si cambiaste vistas (y `php artisan view:cache` si quieres verificar que compilan).
- [ ] `php artisan test --testsuite=Feature` → 3/3 en verde.
- [ ] Revisaste en el navegador las rutas que tocaste, en móvil y escritorio.
- [ ] `git status` no incluye `.env`, `node_modules` ni `public/build`.
- [ ] Si agregaste datos demo, actualizaste `DatabaseSeeder`.

---

## 7. Solución de problemas

| Síntoma | Causa | Solución |
| --- | --- | --- |
| `Vite manifest not found at ...` | Assets sin compilar | `npm install && npm run build` |
| Los estilos no reflejan tus cambios | Build antiguo | `npm run build` (o `npm run dev`) |
| Una clase de Tailwind no aplica | No existe literalmente en Blade/JS | Escribe la clase completa en el archivo; el build escanea `resources/views` |
| `php` no se reconoce en Windows | PHP fuera del PATH | Usa `C:\xampp\php\php.exe` o agrégalo al PATH |
| `419 Page Expired` | Falta `@csrf` | Añádelo al formulario |
| `429 Too Many Requests` en el login | Rate limiter (5 intentos/min) | Espera un minuto o `php artisan cache:clear` |
| “Sin conexión” al validar DNI | RENIEC no responde o falta token | Revisa `RENIEC_API_TOKEN` en `.env`; el formulario permite completar a mano |
| Imagen 404 | Archivo fuera de `public/` o ruta absoluta | Guárdala en `public/images/` y referencia `/images/archivo.jpg` |
| Cambios de vistas no se ven en producción | Vistas cacheadas | `php artisan view:clear && php artisan view:cache` |
| Se pierde la sesión ciudadana | Driver de sesión | `SESSION_DRIVER=database` requiere la tabla `sessions` (ya incluida) |

---

## 8. Deuda técnica conocida

Cosas pendientes o mejorables detectadas durante el rediseño. **Ninguna afecta al funcionamiento actual**, pero conviene resolverlas con calma:

1. **Rutas duplicadas dentro de `/admin`** (`routes/web.php`, líneas 44–51): las copias de `/personal/login`, `/api/consultar-dni` e `/ingreso-dni` con `throttle` quedaron dentro del grupo con prefijo `admin`, por lo que no protegen las rutas públicas reales (las url finales son `/admin/...`). La protección real del login está en `AuthController` (RateLimiter → 429) y la de la API en la validación de 8 dígitos. Antes de limpiarlas, confirma que nadie las use.
2. **`admin.vacuna.update` sin método**: la ruta (línea 33) apunta a `HampiController@updateVacunaEstado`, que no existe. Ninguna vista la usa; para aprovecharla, implementa el método (cambiar `stock_estado`) o elimina la ruta.
3. **`phpunit.xml` referencia `tests/Unit`**, que no existe; por eso los tests se ejecutan con `--testsuite=Feature`. Opciones: crear `tests/Unit/.gitkeep` o quitar la suite del XML.
4. **`storeVacuna` y `storeTurno` fijan `posta_id = 1`**: si se registran más postas, hay que permitir elegir la posta en el formulario.
5. **Modelo `Posta` con relación autorreferente `posta()`**: código heredado; revísalo antes de usarlo.
6. **`database/laravel` versionada** incluye sesiones y caché: cada uso local modifica el archivo. Si en el futuro molesta, considera `git rm --cached database/laravel`, añadirlo al `.gitignore` y documentar `php artisan migrate --seed` para nuevos clones (se pierde el “clonar y funciona”).
7. **Ruta `/test`** devuelve un string de depuración; puedes eliminarla cuando ya no la necesites.
