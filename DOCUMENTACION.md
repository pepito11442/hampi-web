# Hampi · Documentación completa del código

> **Para qué sirve este documento**
>
> Explica cómo está construido el portal **Hampi** de la Microred Socabaya, qué hace cada archivo y cómo se conectan las piezas. Está escrito para que pueda entenderlo alguien que **nunca ha programado**, y también para que sirva como guion de exposición y de defensa del proyecto.
>
> No hace falta leerlo de principio a fin. Al final hay un **glosario**, un **guion de exposición** y un **índice de archivos** para consultar rápido.

---

## Índice

1. [Qué es Hampi](#1-qué-es-hampi)
2. [La web explicada sin tecnicismos](#2-la-web-explicada-sin-tecnicismos)
3. [Tecnologías del proyecto](#3-tecnologías-del-proyecto)
4. [Mapa de carpetas](#4-mapa-de-carpetas)
5. [El viaje de una petición](#5-el-viaje-de-una-petición)
6. [Rutas](#6-rutas)
7. [Controladores](#7-controladores)
8. [Modelos y base de datos](#8-modelos-y-base-de-datos)
9. [Servicios: la lógica del negocio](#9-servicios-la-lógica-del-negocio)
10. [Middleware y seguridad](#10-middleware-y-seguridad)
11. [Form Requests y validación](#11-form-requests-y-validación)
12. [Vistas Blade](#12-vistas-blade)
13. [CSS, diseño y accesibilidad](#13-css-diseño-y-accesibilidad)
14. [JavaScript del navegador](#14-javascript-del-navegador)
15. [Flujos completos, paso a paso](#15-flujos-completos-paso-a-paso)
16. [Tests y verificaciones](#16-tests-y-verificaciones)
17. [Cómo levantar el proyecto](#17-cómo-levantar-el-proyecto)
18. [Guion para la exposición](#18-guion-para-la-exposición)
19. [Preguntas frecuentes de la exposición](#19-preguntas-frecuentes-de-la-exposición)
20. [Glosario para no programadores](#20-glosario-para-no-programadores)
21. [Índice de archivos](#21-índice-de-archivos)
22. [Límites de la demo y advertencias](#22-límites-de-la-demo-y-advertencias)

---

## 1. Qué es Hampi

**Hampi** es un portal web para la **Microred Socabaya**, en Arequipa, Perú. Reúne en un solo lugar la información que un vecino necesita y las herramientas que usa el personal de salud.

Tiene tres públicos:

| Público | Qué necesita | Cómo entra |
| --- | --- | --- |
| **Ciudadanía** | Ver médicos de turno, vacunas, campañas, postas y sus citas | Con su **DNI**, sin crear cuenta |
| **Personal de salud** | Ver su horario mensual, su agenda y editar su perfil | Con **DNI + contraseña** |
| **Administración** | Gestionar médicos, vacunas, horarios, equipo y campañas | Con **correo + contraseña** |

La portada muestra tres datos que cambian todos los días:

- cuántos médicos están en el rol de hoy,
- cuántas vacunas están disponibles,
- cuál es la próxima campaña.

> **Idea importante:** esto es una **demo académica**. Los datos y las cuentas son de demostración. El portal está diseñado para verse y funcionar como un sistema real, pero no reemplaza un sistema oficial de salud.

---

## 2. La web explicada sin tecnicismos

### 2.1 Cliente y servidor

Cuando abres una página web pasan dos cosas:

1. **Tu navegador** (Chrome, Edge, Firefox) pide una página.
2. **Un servidor** prepara esa página y se la devuelve.

Es como un restaurante:

- Tú eres el **cliente**.
- El navegador es el **mesero** que lleva el pedido.
- El servidor es la **cocina**.
- La página web es el **plato** que llega a tu mesa.

El navegador **no ve** el código PHP ni la base de datos. Solo recibe el resultado final: **HTML** (la estructura), **CSS** (los colores y el diseño) y **JavaScript** (las interacciones).

### 2.2 Qué es una petición HTTP

Cada vez que haces clic en un enlace, el navegador envía una **petición HTTP**. Por ejemplo:

```
GET /medicos-hoy HTTP/1.1
Host: 127.0.0.1:8000
```

Eso significa: “quiero ver la página `/medicos-hoy`”.

El servidor responde algo así:

```
HTTP/1.1 200 OK
Content-Type: text/html

<!DOCTYPE html>
<html lang="es">
  ...la página...
</html>
```

- **GET** = “dame” información.
- **POST** = “envía” información (por ejemplo, un formulario).
- **200** = todo salió bien.
- **302** = redirigir a otra página.
- **404** = no existe.
- **419** = la sesión o el token de seguridad venció.
- **429** = demasiados intentos.

### 2.3 Qué es una sesión

Una sesión es una **pulsera de identificación** que el servidor te da al entrar. El navegador guarda una pequeña cookie con un identificador. En cada petición siguiente, el servidor lee esa pulsera y sabe quién eres.

En Hampi hay dos sesiones distintas:

- **Sesión ciudadana:** guarda el DNI de la persona que entró al panel.
- **Sesión del personal:** guarda la cuenta autenticada del médico, enfermero o administrador.

### 2.4 Qué es una base de datos

Una base de datos es un **archivo ordenado de tablas**, como una hoja de cálculo gigante. Cada tabla guarda un tipo de cosa:

- `users` → cuentas del personal y administración.
- `ciudadanos` → vecinos que consultan sus citas.
- `citas` → citas médicas.
- `horarios` → turnos mensuales del personal.
- `postas` → establecimientos de salud.
- `vacuna_avisos` → avisos del pizarrín.
- `campanas_preventivas` → campañas de salud.
- `turno_medicos` → rol manual de médicos del día.

Las tablas se conectan entre sí mediante **claves foráneas**. Por ejemplo, una cita sabe a qué ciudadano pertenece porque guarda su `ciudadano_id`.

### 2.5 Qué es el HTML, el CSS y el JavaScript

| Lenguaje | Qué hace | Ejemplo en Hampi |
| --- | --- | --- |
| **HTML** | Da estructura y contenido | El título “Médicos de turno hoy” |
| **CSS** | Da color, tipografía, tamaño y diseño | El fondo de papel, los botones verdes, las hojas blancas |
| **JavaScript** | Da comportamiento en el navegador | Abrir el menú móvil, mostrar el spinner al consultar RENIEC |

PHP genera el HTML. El CSS y el JavaScript ya están preparados de antemano por **Node/Vite**.

---

## 3. Tecnologías del proyecto

| Tecnología | Para qué se usa | ¿Se necesita al ejecutar la app? |
| --- | --- | --- |
| **PHP 8.2** | Lenguaje del servidor | Sí |
| **Laravel 11** | Framework que organiza PHP | Sí |
| **MySQL / MariaDB** | Base de datos principal | Sí |
| **Blade** | Motor de plantillas de Laravel | Sí |
| **HTML + CSS + JS** | Lo que recibe el navegador | Sí (los archivos ya compilados) |
| **Tailwind CSS 4** | Diseño visual mediante clases | Solo para construir el CSS |
| **Vite 8** | Empaqueta CSS y JavaScript | Solo para construir |
| **Node.js** | Ejecuta Vite y Tailwind | Solo para construir |
| **Composer** | Instala librerías PHP | Solo al instalar |
| **npm** | Instala herramientas de frontend | Solo al instalar/construir |
| **PHPUnit** | Ejecuta las pruebas automáticas | Solo para probar |
| **RENIEC / apis.net.pe** | Consulta opcional de nombres por DNI | Solo si hay token configurado |

### 3.1 Por qué hay Node si el proyecto es PHP

Esta es una de las preguntas más comunes.

- **PHP** atiende las peticiones y genera el HTML.
- **Node** no atiende las peticiones. Solo se usa **antes**, para “imprimir” el CSS y el JavaScript en archivos estáticos dentro de `public/build`.

Analogía: PHP es el **mesero** que atiende la mesa; Node es la **imprenta** que fabricó el menú y los carteles. Si el menú ya está impreso, no necesitas la imprenta para atender.

Si `public/build` ya existe y no cambias estilos, la app funciona solo con PHP + MySQL.

Si cambias `resources/css`, `resources/js` o una clase de Tailwind en un Blade, hay que volver a ejecutar:

```bash
npm run build
```

### 3.2 Laravel en una frase

Laravel es un **esqueleto** que ya trae resueltas las partes comunes de una aplicación web: rutas, sesiones, seguridad, base de datos, validaciones, plantillas y pruebas. En lugar de inventar todo desde cero, el proyecto se concentra en las reglas de Hampi.

### 3.3 El patrón MVC

Laravel usa **MVC**, que significa:

| Letra | Nombre | En Hampi | Analogía |
| --- | --- | --- | --- |
| **M** | Modelo | `app/Models/Cita.php` | El archivero que conoce la base de datos |
| **V** | Vista | `resources/views/hampi/medicos.blade.php` | La hoja impresa que ve el usuario |
| **C** | Controlador | `app/Http/Controllers/HampiController.php` | El coordinador que recibe el pedido y decide qué hacer |

El controlador **no** debería tener toda la lógica compleja. Para eso están los **servicios** (`app/Services/`).

---

## 4. Mapa de carpetas

```text
hampi-web/
├── app/                         Código PHP propio de Hampi
│   ├── Http/
│   │   ├── Controllers/         Reciben las peticiones
│   │   │   ├── Admin/           Panel administrativo
│   │   │   ├── AuthController.php
│   │   │   ├── EmpleadoController.php
│   │   │   ├── EmpleadoCitaController.php
│   │   │   └── HampiController.php
│   │   ├── Middleware/          Porteros de seguridad
│   │   └── Requests/            Reglas de validación
│   ├── Models/                  Representan tablas de la base de datos
│   ├── Services/                Reglas de negocio reutilizables
│   └── Providers/               Arranque de Laravel
├── bootstrap/
│   └── app.php                  Configuración de rutas y middleware
├── config/                      Configuración de Laravel y de Hampi
├── database/
│   ├── migrations/              Planos de las tablas
│   ├── seeders/                 Datos de demostración
│   └── database.sqlite          Base local de pruebas (no se versiona)
├── public/
│   ├── index.php                Entrada única de la aplicación
│   ├── build/                   CSS y JS ya compilados
│   ├── fonts/                   Tipografías autoalojadas
│   ├── images/                  Imágenes del portal
│   └── storage/                 Enlace a las fotos de perfil
├── resources/
│   ├── css/app.css              Tailwind + sistema visual
│   ├── js/app.js                Interacciones del navegador
│   └── views/                   Plantillas Blade
├── routes/
│   └── web.php                  Todas las rutas del portal
├── storage/
│   ├── app/public/perfiles/     Fotos de perfil subidas
│   ├── framework/               Caché y sesiones
│   └── logs/                    Registros de errores
├── tests/                       Pruebas automáticas
├── .env                         Configuración local (NO se versiona)
├── .env.example                 Plantilla de configuración
├── composer.json                Dependencias PHP
├── package.json                 Herramientas de frontend
├── vite.config.js               Configuración de Vite
└── README.md                    Guía de instalación y uso
```

### 4.1 Carpetas que no se suben al repositorio

- `vendor/`: librerías PHP instaladas por Composer. Se reconstruye con `composer install`.
- `node_modules/`: herramientas de Node. Se reconstruye con `npm install`.
- `public/build/`: CSS/JS compilados. Se reconstruye con `npm run build`.
- `.env`: contiene contraseñas y tokens. **Nunca se sube.**
- `storage/logs/`, `storage/framework/`, `database/database.sqlite`: archivos de ejecución.
- `public/storage`: enlace que se crea con `php artisan storage:link`.

### 4.2 Archivos de configuración

| Archivo | Qué configura |
| --- | --- |
| `.env` | Secretos y valores del entorno (base de datos, token RENIEC, URL). |
| `.env.example` | Plantilla pública de `.env`. |
| `config/app.php` | Nombre, zona horaria, idioma y entorno. |
| `config/auth.php` | Cómo se autentican los usuarios. |
| `config/database.php` | Conexiones MySQL y SQLite de tests. |
| `config/filesystems.php` | Dónde se guardan las fotos y qué URL usan (`/storage`). |
| `config/navegacion.php` | Enlaces del menú público. |
| `config/services.php` | Token y URL de RENIEC. |
| `config/session.php` | Cookies y sesiones. |
| `composer.json` | Dependencias PHP y autoload de `App\`. |
| `package.json` | Dependencias de Vite/Tailwind y scripts de build. |
| `vite.config.js` | Entradas de CSS/JS y plugin de Tailwind. |
| `phpunit.xml` | Configuración de la suite de tests. |

**`composer.json` no es lo mismo que `package.json`:**

- `composer.json` instala **PHP** (Laravel, PHPUnit, etc.).
- `package.json` instala **herramientas de frontend** (Vite, Tailwind).

---

## 5. El viaje de una petición

Tomemos como ejemplo la página pública **`/medicos-hoy`**.

```text
Navegador
   │  GET /medicos-hoy
   ▼
public/index.php                 ← entrada única de Laravel
   │
   ▼
bootstrap/app.php                ← carga configuración y middleware
   │
   ▼
routes/web.php                   ← busca la ruta /medicos-hoy
   │
   ▼
HampiController@verMedicos       ← controlador
   │
   ▼
AgendaMedica::delDia()           ← servicio de negocio
   │
   ├── consulta tabla horarios
   ├── consulta tabla users
   └── consulta tabla turno_medicos
   │
   ▼
resources/views/hampi/medicos.blade.php  ← plantilla
   │
   ▼
HTML final + CSS + JS
   │
   ▼
Navegador del usuario
```

### 5.1 Explicación paso a paso

1. **El navegador pide `/medicos-hoy`.** Es una petición GET.
2. **`public/index.php`** es el único archivo PHP que el servidor web ejecuta directamente. Su trabajo es arrancar Laravel.
3. **`bootstrap/app.php`** carga la configuración, los middleware y las rutas.
4. **`routes/web.php`** encuentra la línea:
   ```php
   Route::get('/medicos-hoy', [HampiController::class, 'verMedicos'])->name('medicos.index');
   ```
   Eso significa: “si llega un GET a `/medicos-hoy`, llama al método `verMedicos` del `HampiController`”.
5. **El controlador** pide los datos al servicio `AgendaMedica`.
6. **El servicio** consulta la base de datos y arma una lista de médicos con turno, especialidad, consultorio, estado y foto.
7. **El controlador** entrega esa lista a la vista Blade.
8. **Blade** genera HTML. Laravel lo devuelve al navegador con estado 200.
9. El navegador carga el CSS y el JavaScript desde `public/build`.

> **En simple:** la ruta es la dirección, el controlador es el coordinador, el servicio es el especialista, el modelo es el archivero y la vista es la hoja que se imprime.

### 5.2 Los archivos de arranque

**`public/index.php`** es la puerta de entrada. Su contenido real es corto:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
```

Línea por línea:

| Línea | Qué hace |
| --- | --- |
| `require vendor/autoload.php` | Carga las librerías instaladas por Composer. |
| `require bootstrap/app.php` | Crea la aplicación Laravel. |
| `Request::capture()` | Toma la petición HTTP que llegó. |
| `handleRequest()` | Busca la ruta, ejecuta el controlador y devuelve la respuesta. |

**`bootstrap/app.php`** configura la aplicación:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
        ]);

        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('empleado', 'empleado/*')
            ? route('empleado.login')
            : route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

Puntos clave:

- `web: routes/web.php` → le dice a Laravel dónde están las rutas.
- `health: '/up'` → crea una ruta de salud para saber si la app responde.
- `trustProxies(at: '*')` → confía en los proxies de desarrollo/Codespaces. En un servidor real debe limitarse a los proxies conocidos.
- `SecurityHeaders` → agrega las cabeceras de seguridad a todas las respuestas.
- `'role' => EnsureRole::class` → permite usar `role:doctor` en las rutas.
- `redirectGuestsTo(...)` → si alguien sin sesión entra a `/empleado`, lo manda al login del personal; si entra a `/admin`, al login administrativo.

> El `public/index.php` real también revisa si la aplicación está en modo mantenimiento. Se omitió esa comprobación del ejemplo para que se entienda el flujo principal.

---

## 6. Rutas

Todas las rutas están en **`routes/web.php`**. Una ruta es una regla:

```php
Route::get('/medicos-hoy', [HampiController::class, 'verMedicos'])->name('medicos.index');
```

Se lee así:

- `Route::get` → responde a peticiones GET.
- `/medicos-hoy` → dirección que escribe el usuario.
- `[HampiController::class, 'verMedicos']` → método que se ejecuta.
- `->name('medicos.index')` → nombre interno para generar enlaces sin escribir la URL a mano.

### 6.1 Rutas públicas

| URL | Nombre interno | Método | Qué hace |
| --- | --- | --- | --- |
| `/` | `inicio` | `HampiController@index` | Portada y formulario DNI |
| `/api/consultar-dni/{dni}` | — | `HampiController@consultarDniReniec` | Consulta RENIEC (opcional) |
| `POST /ingreso-dni` | `ciudadano.login` | `HampiController@accesoPorDni` | Abre sesión ciudadana |
| `/mi-panel` | `ciudadano.panel` | `HampiController@panelCiudadano` | Panel de citas del ciudadano |
| `/cerrar-sesion-ciudadano` | `ciudadano.logout` | `HampiController@logoutCiudadano` | Cierra sesión ciudadana |
| `/medicos-hoy` | `medicos.index` | `HampiController@verMedicos` | Rol de médicos del día |
| `/pizarrin-vacunas` | `pizarrin.index` | `HampiController@verPizarrin` | Pizarrín de vacunas |
| `/campanas-preventivas` | `campanas.index` | `HampiController@verCampanasPreventivas` | Campañas preventivas |
| `/directorio-postas` | `postas.index` | `HampiController@verPostas` | Directorio de postas |

### 6.2 Rutas de acceso del personal y administración

| URL | Nombre | Método | Protección |
| --- | --- | --- | --- |
| `/personal/login` | `login` | `AuthController@showLoginForm` | Público |
| `POST /personal/login` | `login.post` | `AuthController@login` | Rate limit |
| `POST /personal/logout` | `logout` | `AuthController@logout` | Autenticado |
| `/equipo/login` | `empleado.login` | `EmpleadoController@showLogin` | Público |
| `POST /equipo/login` | `empleado.login.post` | `EmpleadoController@login` | Rate limit |
| `POST /equipo/logout` | `empleado.logout` | `EmpleadoController@logout` | Autenticado |

### 6.3 Rutas del personal de salud

Se agrupan con prefijo `/empleado` y nombre `empleado.`:

| URL | Nombre | Protección |
| --- | --- | --- |
| `/empleado/panel` | `empleado.panel` | `auth` + rol de personal |
| `POST /empleado/perfil` | `empleado.perfil.update` | `auth` + rol de personal |
| `/empleado/citas` | `empleado.citas.index` | `auth` + `role:doctor` |
| `/empleado/citas/crear` | `empleado.citas.create` | `auth` + `role:doctor` |
| `POST /empleado/citas` | `empleado.citas.store` | `auth` + `role:doctor` |
| `/empleado/citas/disponibilidad` | `empleado.citas.disponibilidad` | `auth` + `role:doctor` |
| `POST /empleado/citas/{cita}/estado` | `empleado.citas.estado` | `auth` + `role:doctor` |

### 6.4 Rutas de administración

Se agrupan con prefijo `/admin`, nombre `admin.` y middleware `auth` + `role:admin,admision`:

| URL | Nombre | Acción |
| --- | --- | --- |
| `/admin/dashboard` | `admin.dashboard` | Panel general |
| `POST /admin/turnos/guardar` | `admin.turno.store` | Registrar médico visitante |
| `POST /admin/turnos/{id}/estado` | `admin.turno.update` | Cambiar estado |
| `DELETE /admin/turnos/{id}` | `admin.turno.destroy` | Retirar del rol |
| `POST /admin/pizarrin/guardar` | `admin.vacuna.store` | Publicar aviso de vacuna |
| `DELETE /admin/pizarrin/{id}` | `admin.vacuna.destroy` | Retirar aviso |
| `/admin/equipo` | `admin.equipo.index` | Listar equipo |
| `POST /admin/equipo` | `admin.equipo.store` | Crear trabajador |
| `POST /admin/equipo/{empleado}` | `admin.equipo.update` | Editar trabajador |
| `DELETE /admin/equipo/{empleado}` | `admin.equipo.destroy` | Eliminar trabajador |
| `/admin/horarios` | `admin.horarios.index` | Ver horarios |
| `POST /admin/horarios/generar` | `admin.horarios.generar` | Generar 150 h |
| `DELETE /admin/horarios/{empleado}/{anio}/{mes}` | `admin.horarios.destroy` | Eliminar horario |
| `/admin/campanas` | `admin.campanas.index` | Listar campañas |
| `POST /admin/campanas` | `admin.campanas.store` | Crear campaña |
| `POST /admin/campanas/{campana}/estado` | `admin.campanas.estado` | Publicar/ocultar |
| `DELETE /admin/campanas/{campana}` | `admin.campanas.destroy` | Eliminar campaña |

### 6.5 Ejemplo de una ruta con parámetro

```php
Route::post('/empleado/citas/{cita}/estado', [EmpleadoCitaController::class, 'actualizarEstado'])
    ->name('empleado.citas.estado');
```

`{cita}` es un **parámetro**. Si la URL es `/empleado/citas/15/estado`, Laravel busca la cita con ID 15 y la entrega al método. Eso se llama **route model binding**: Laravel convierte el número en un objeto `Cita`.

---

## 7. Controladores

Un controlador es una clase con métodos. Cada método atiende una acción del portal. No guarda datos por sí mismo: coordina modelos, servicios y vistas.

### 7.1 `app/Http/Controllers/Controller.php`

Es una clase vacía que sirve de base para los demás controladores. Laravel la usa para compartir comportamientos comunes.

### 7.2 `HampiController`

Es el controlador del **portal público y del panel ciudadano**.

#### `index(AgendaMedica $agenda)`

Muestra la portada.

```php
Session::forget(['ciudadano_dni', 'ciudadano_id']);

$medicosHoy = $agenda->delDia();

$stats = [
    'turnos' => $agenda->total($medicosHoy),
    'vacunas' => VacunaAviso::where('stock_estado', 'Disponible')->count(),
    'postas' => Posta::where('estado_activo', true)->count(),
];
```

Explicación:

1. Borra cualquier sesión ciudadana anterior. Así, al volver a la portada, se empieza limpio.
2. Pide a `AgendaMedica` los médicos de hoy.
3. Cuenta médicos, vacunas disponibles y postas activas.
4. Busca la próxima campaña activa.
5. Devuelve la vista `hampi.login`.

La portada muestra tres números reales: no son datos inventados en la vista, salen de la base de datos.

#### `accesoPorDni(Request $request)`

Es el “login” del ciudadano. No pide contraseña.

```php
$dni = $request->input('dni', '72345678');
$nombres = trim(strip_tags((string) $request->input('nombres_reniec', '')));
$apellidos = trim(strip_tags((string) $request->input('apellidos_reniec', '')));

$ciudadano = Ciudadano::firstOrNew(['dni' => $dni]);
```

Qué hace:

1. Toma el DNI y los nombres que llegaron del formulario.
2. Busca un ciudadano con ese DNI; si no existe, crea uno nuevo **en memoria** con `firstOrNew`.
3. Si la ficha ya existía, no borra los nombres que había registrado un médico.
4. Guarda la ficha.
5. Guarda el DNI en la sesión: `Session::put('ciudadano_dni', $ciudadano->dni)`.
6. Redirige a `/mi-panel`.

> **Importante para la exposición:** este acceso es de **demostración**. En un sistema real de salud no se puede dejar entrar solo con el DNI; haría falta un segundo factor (código por SMS, correo o contraseña). El README lo advierte.

#### `panelCiudadano()`

1. Lee el DNI de la sesión.
2. Si no hay sesión, redirige a la portada.
3. Busca al ciudadano.
4. Trae sus citas con `Cita::with(['posta', 'medico'])`, ordenadas por fecha.
5. Devuelve `hampi.panel_paciente`.

#### `verMedicos(AgendaMedica $agenda)`

1. `delDia()` devuelve la agenda combinada.
2. `manana()` filtra los turnos de la mañana.
3. `tarde()` filtra los de la tarde.
4. Pasa ambos grupos a la vista `hampi.medicos`.

#### `verPizarrin()`

Trae todos los avisos de vacunas con su posta, ordenados por urgencia:

```php
$vacunas = VacunaAviso::with('posta')->orderBy('es_urgente', 'desc')->get();
```

#### `verPostas()`

Trae solo las postas activas:

```php
$postas = Posta::where('estado_activo', true)->get();
```

#### `verCampanasPreventivas()`

Trae las campañas activas usando el scope del modelo:

```php
$campanas = CampanaPreventiva::activas()->orderBy('fecha_inicio')->get();
```

#### `logoutCiudadano()`

Borra `ciudadano_dni` y `ciudadano_id` de la sesión y vuelve a la portada.

#### `consultarDniReniec($dni)`

Es una **API interna** que consulta RENIEC para autocompletar nombres.

1. Valida que el DNI tenga exactamente 8 dígitos con una expresión regular.
2. Lee el token de `config('services.reniec.token')`.
3. Hace una petición HTTP a `apis.net.pe` con timeout de 5 segundos.
4. Si responde bien, devuelve un JSON:
   ```json
   {
     "success": true,
     "nombres": "ROSA ELENA",
     "apellidos": "QUISPE MAMANI",
     "origen": "Padrón RENIEC Oficial"
   }
   ```
5. Si falla, devuelve un mensaje para que el usuario escriba sus nombres a mano.

La vista usa JavaScript para llamar a esta ruta cuando el usuario termina de escribir los 8 dígitos.

### 7.3 `AuthController`

Controla el **acceso de administración** con correo y contraseña.

#### `login(Request $request)`

1. Crea una clave de límite con el correo y la IP:
   ```php
   $claveLimite = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());
   ```
2. Si hay más de 5 intentos fallidos, responde con error 429 y escribe una advertencia en el log.
3. Valida que el correo sea válido y que la contraseña exista.
4. Intenta autenticar con `Auth::attempt($credenciales)`.
5. Si la cuenta no es administrativa o está inactiva, cierra la sesión y muestra un error.
6. Si todo está bien, regenera la sesión y redirige a `/admin/dashboard`.

#### `logout(Request $request)`

Cierra la sesión, invalida la sesión anterior y regenera el token CSRF.

### 7.4 `EmpleadoController`

Controla el **acceso del personal de salud** con DNI + contraseña.

#### `showLogin()`

Si la persona ya inició sesión y es personal, la manda directo a su panel. Si no, muestra el formulario.

#### `login(Request $request)`

1. Crea la clave de límite `empleado|DNI|IP`.
2. Bloquea después de 5 intentos.
3. Valida DNI de 8 dígitos y contraseña.
4. Busca al usuario por DNI.
5. Comprueba tres cosas:
   - que la cuenta esté activa,
   - que sea personal de salud,
   - que la contraseña coincida con el hash guardado.
6. Si todo está bien, `Auth::login($user)`, regenera la sesión y redirige al panel.
7. Si falla, suma un intento y muestra un error genérico.

#### `panel(Request $request)`

1. Obtiene el usuario autenticado.
2. Normaliza año y mes.
3. Busca sus horarios del mes.
4. Suma las horas.
5. Devuelve `empleado.panel` con el horario, las horas y el perfil.

#### `updatePerfil(Request $request)`

1. Valida correo, teléfono, especialidad, biografía, títulos, experiencia y foto.
2. La foto debe ser JPG, PNG o WebP y pesar como máximo 2 MB.
3. Si hay foto nueva, borra la anterior y guarda la nueva en `storage/app/public/perfiles`.
4. Actualiza el usuario.
5. Vuelve al panel con un mensaje de éxito.

> **Dato técnico:** la foto se guarda en el **disco público** de Laravel y se muestra con una URL relativa `/storage/perfiles/...`. Para que esa URL funcione, `public/storage` debe ser un enlace a `storage/app/public`.

### 7.5 `EmpleadoCitaController`

Controla la **agenda de citas del médico**.

#### `index(Request $request)`

Trae solo las citas del médico autenticado y las separa en:

- **Hoy**,
- **Próximas**,
- **Historial** (atendidas, canceladas o pasadas).

#### `create(Request $request, AgendaCitas $agenda)`

Muestra el formulario para agendar.

- Busca al paciente por DNI si se envió uno.
- Si el DNI no tiene 8 dígitos, muestra un error.
- Calcula la fecha inicial: la indicada o el primer día próximo con bloques libres.
- Pide a `AgendaCitas` los bloques disponibles.
- Carga las postas activas.
- Elige la posta del médico como predeterminada.

#### `store(Request $request, AgendaCitas $agenda)`

Guarda una cita nueva.

1. Valida DNI, posta, fecha, hora, nombres y teléfono.
2. Busca al ciudadano; si no existe, exige nombres y apellidos.
3. Comprueba que la hora sea válida:
   - que esté dentro del turno,
   - que sea múltiplo de 30 minutos,
   - que no esté en el pasado.
4. Comprueba que el médico no tenga otra cita en ese bloque.
5. Comprueba que el paciente no tenga otra cita a la misma hora.
6. Dentro de una **transacción**, crea al ciudadano si hace falta y crea la cita.
7. Redirige a “Mis citas” con un mensaje.

#### `actualizarEstado(Request $request, Cita $cita)`

1. Comprueba que la cita pertenezca al médico autenticado.
2. Solo acepta `Atendida` o `Cancelada`.
3. No permite reabrir una cita ya cerrada.
4. No permite marcar como atendida una cita que todavía es futura.
5. Guarda el nuevo estado.

#### `disponibilidad(Request $request, AgendaCitas $agenda)`

Devuelve un JSON para el selector dinámico:

```json
{
  "fecha": "2026-10-26",
  "turno": "Mañana",
  "horario": "07:00 a 13:00",
  "slots": [
    { "hora": "07:00", "etiqueta": "07:00" },
    { "hora": "07:30", "etiqueta": "07:30" }
  ],
  "mensaje": null
}
```

La clave se llama `slots` porque así la espera el JavaScript. La variable interna sí se llama `$bloques`.

#### `fechaDelFormulario(...)`

Es un método privado que decide qué fecha abrir por defecto. Si hoy ya no quedan bloques, busca el siguiente día con disponibilidad.

### 7.6 Controladores de administración

#### `Admin/DashboardController@index`

Cuenta:

- vacunas,
- médicos del día,
- médicos atendiendo,
- personal de salud,
- horarios del mes.

Y muestra el panel.

#### `Admin/EquipoController`

- `index`: lista al personal, con filtros por médicos o enfermería.
- `store`: crea una cuenta con nombre, DNI, correo, rol, especialidad, teléfono, establecimiento y contraseña.
- `update`: actualiza los datos, el rol, el estado activo o la contraseña.
- `destroy`: elimina la cuenta y su foto.

Solo permite crear o editar roles `doctor`, `enfermero` o `enfermera`; no permite crear administradores desde allí.

#### `Admin/HorarioController`

- `index`: muestra el calendario mensual del trabajador elegido.
- `generar`: llama a `HorarioGenerator` para crear 150 horas.
- `destroy`: borra el horario del mes.

Antes de regenerar o borrar, comprueba que no existan **citas futuras activas** en ese mes. Si existen, bloquea la operación para no dejar citas sin turno.

#### `Admin/CampanaController`

- `index`: lista campañas activas e inactivas.
- `store`: crea una campaña.
- `toggleEstado`: publica u oculta una campaña.
- `destroy`: la elimina.

#### `Admin/TurnoController`

Gestiona el **rol manual** de médicos visitantes:

- `store`: agrega un médico al rol de hoy.
- `updateEstado`: cambia su estado (`Atendiendo`, `Turno Completo`, `Reprogramado`).
- `destroy`: lo retira.

> En esta demo, el `posta_id` se fija en `1` porque hay una sola posta principal. En un sistema real se elegiría la posta desde el formulario.

#### `Admin/VacunaController`

- `store`: publica un aviso en el pizarrín.
- `destroy`: retira el aviso.

Limpia el HTML de los textos con `strip_tags` antes de guardar.

---

## 8. Modelos y base de datos

Un **modelo** es una clase PHP que representa una tabla. En lugar de escribir SQL a mano, se usan métodos como:

```php
$citas = Cita::where('estado', 'Confirmada')->get();
```

Laravel traduce eso a SQL:

```sql
SELECT * FROM citas WHERE estado = 'Confirmada';
```

### 8.1 `User` (`users`)

Representa a una persona con cuenta: administración o personal de salud.

Campos importantes:

| Campo | Tipo | Significado |
| --- | --- | --- |
| `name` | texto | Nombre completo |
| `dni` | texto de 8 | Documento de identidad |
| `email` | texto único | Correo de acceso |
| `password` | texto | Contraseña cifrada con bcrypt |
| `role` | texto | `admin`, `admision`, `doctor`, `enfermero`, `enfermera` |
| `establecimiento` | texto | Posta donde trabaja |
| `telefono` | texto | Teléfono |
| `especialidad` | texto | Especialidad profesional |
| `biografia`, `titulos`, `experiencia` | texto largo | Perfil profesional |
| `foto_perfil` | texto | Ruta de la foto |
| `activo` | sí/no | Si puede entrar |

Métodos:

- `esAdmin()`: devuelve `true` si el rol es `admin` o `admision`.
- `esPersonal()`: devuelve `true` si el rol es `doctor`, `enfermero` o `enfermera`.
- `getRolLabelAttribute()`: convierte el rol interno en una etiqueta legible.
- `getFotoUrlAttribute()`: devuelve la URL de la foto o `null` si no existe.
- `iniciales()`: devuelve las iniciales para el avatar de respaldo.
- `horarios()` y `citas()`: relaciones.

El modelo **oculta** `password` y `remember_token` cuando se convierte a JSON. Eso evita filtrar contraseñas por accidente.

### 8.2 `Ciudadano` (`ciudadanos`)

Representa a un vecino sin cuenta.

| Campo | Significado |
| --- | --- |
| `dni` | Documento único de 8 dígitos |
| `nombres` | Nombres |
| `apellidos` | Apellidos |
| `telefono` | Teléfono opcional |

No tiene contraseña. Su identificación es el DNI.

### 8.3 `Cita` (`citas`)

Representa una cita médica.

| Campo | Significado |
| --- | --- |
| `ciudadano_id` | A qué ciudadano pertenece |
| `user_id` | Qué médico la atiende (puede ser null en citas antiguas) |
| `posta_id` | En qué establecimiento |
| `especialidad` | Especialidad al momento de agendar |
| `fecha_hora` | Fecha y hora |
| `estado` | `Confirmada`, `Pendiente`, `Atendida`, `Cancelada` |

Relaciones:

```php
$cita->ciudadano; // devuelve un Ciudadano
$cita->medico;    // devuelve un User o null
$cita->posta;     // devuelve una Posta
```

Solo `Confirmada` y `Pendiente` ocupan un bloque de agenda.

### 8.4 `Horario` (`horarios`)

Representa el turno de un trabajador en una fecha.

| Campo | Significado |
| --- | --- |
| `user_id` | Trabajador |
| `fecha` | Día |
| `turno` | `manana`, `tarde`, `completo`, `sabado` |
| `hora_inicio`, `hora_fin` | Horas del turno |
| `horas` | Horas que suma ese día |

La constante `TURNOS` define los cuatro turnos:

```php
'manana'   => 07:00 a 13:00, 6.0 h
'tarde'    => 13:00 a 19:30, 6.5 h
'completo' => 07:00 a 19:00, 12.0 h
'sabado'   => 07:00 a 15:00, 8.0 h
```

Hay una restricción de base de datos: **un trabajador no puede tener dos horarios el mismo día** (`unique(['user_id', 'fecha'])`).

### 8.5 `Posta` (`postas`)

Representa un establecimiento de salud.

| Campo | Significado |
| --- | --- |
| `nombre` | Nombre de la posta |
| `categoria` | Categoría (por ejemplo, I-3) |
| `direccion` | Dirección |
| `telefono` | Teléfono |
| `horario_atencion` | Horario de atención |
| `estado_activo` | Si aparece en el directorio |

### 8.6 `TurnoMedico` (`turno_medicos`)

Es el **rol manual** de médicos del día. Se usa para médicos visitantes o ajustes que no tienen un horario mensual.

| Campo | Significado |
| --- | --- |
| `posta_id` | Posta |
| `medico_nombre` | Nombre del médico |
| `especialidad` | Especialidad |
| `turno` | `Mañana` o `Tarde` |
| `consultorio` | Consultorio |
| `estado` | `Atendiendo`, `Turno Completo`, `Reprogramado` |

### 8.7 `VacunaAviso` (`vacuna_avisos`)

Es un aviso del pizarrín de vacunas.

| Campo | Significado |
| --- | --- |
| `posta_id` | Posta que publica |
| `titulo` | Nombre de la vacuna o campaña |
| `grupo_objetivo` | A quién está dirigida |
| `stock_estado` | `Disponible`, `Por agotarse`, `Agotado` |
| `requisitos` | Qué debe llevar la persona |
| `observacion` | Información adicional |
| `es_urgente` | Si se destaca como urgente |

### 8.8 `CampanaPreventiva` (`campanas_preventivas`)

Representa una campaña de prevención.

| Campo | Significado |
| --- | --- |
| `titulo` | Nombre |
| `grupo_objetivo` | Público objetivo |
| `descripcion` | Explicación |
| `lugar` | Dónde se realiza |
| `fecha_inicio`, `fecha_fin` | Fechas |
| `activa` | Si se publica en el portal |

El modelo calcula dos cosas que **no** se guardan en la base de datos:

- `periodo`: “05/10/2026 al 12/10/2026” o “Fechas por confirmar”.
- `estado`: “Próxima”, “En curso” o “Finalizada”, comparando las fechas con hoy.

### 8.9 Diagrama de relaciones

```text
users (personal)
  │
  ├──< horarios
  └──< citas

ciudadanos
  └──< citas

postas
  ├──< citas
  ├──< vacuna_avisos
  └──< turno_medicos

citas >── ciudadano
citas >── medico (users)
citas >── posta
```

`<` significa “tiene muchos”; `>` significa “pertenece a”.

### 8.10 Migraciones

Una **migración** es el plano que crea o modifica una tabla. Están en `database/migrations/`.

| Migración | Qué crea |
| --- | --- |
| `0001_01_01_000000_create_users_table` | `users`, `password_reset_tokens`, `sessions` |
| `0001_01_01_000001_create_cache_table` | `cache`, `cache_locks` |
| `0001_01_01_000002_create_jobs_table` | `jobs`, `job_batches`, `failed_jobs` (infraestructura de Laravel) |
| `2026_10_11_034850_create_postas_table` | `postas` |
| `2026_10_11_034851_create_ciudadanos_table` | `ciudadanos` |
| `2026_10_11_034852_create_vacuna_avisos_table` | `vacuna_avisos` |
| `2026_10_11_034853_create_turno_medicos_table` | `turno_medicos` |
| `2026_10_11_034854_create_citas_table` | `citas` |
| `2026_10_19_120000_add_staff_fields_to_users_table` | DNI, perfil, foto y estado activo |
| `2026_10_19_120100_create_horarios_table` | `horarios` |
| `2026_10_19_120200_create_campanas_preventivas_table` | `campanas_preventivas` |
| `2026_10_20_000000_add_user_id_to_citas_table` | Relación cita → médico |

Ventaja: cualquier persona puede reconstruir la base con un solo comando:

```bash
php artisan migrate
```

### 8.11 Seeders

Un **seeder** llena la base con datos de ejemplo. Está en `database/seeders/DatabaseSeeder.php`.

Crea:

- 1 administradora,
- 4 trabajadores de salud,
- 1 posta,
- 3 avisos de vacunas,
- 2 turnos manuales,
- 1 ciudadana de prueba,
- 1 cita de demostración,
- 4 campañas,
- horarios de 150 h para el mes actual.

Se ejecuta con:

```bash
php artisan db:seed
```

O junto con las migraciones:

```bash
php artisan migrate --seed
```

---

## 9. Servicios: la lógica del negocio

Los servicios son clases que contienen reglas que no pertenecen ni a un controlador ni a un modelo. Se reutilizan desde varios lugares y son más fáciles de probar.

### 9.1 `MedicoDelDia`

Es un **DTO** (*Data Transfer Object*): una caja con datos ya armados para la vista.

Guarda:

```php
public string $medico_nombre;
public string $especialidad;
public string $turno;
public ?string $hora_inicio;
public ?string $hora_fin;
public string $consultorio;
public string $estado;
public bool $en_manana;
public bool $en_tarde;
public ?string $foto_url;
```

No es una tabla. Es solo una forma cómoda de pasar la información a la vista.

### 9.2 `AgendaMedica`

Es el servicio que arma el **rol del día**. Combina dos fuentes:

1. **Horarios mensuales** (`horarios`) de médicos activos.
2. **Rol manual** (`turno_medicos`) de médicos visitantes o ajustes.

Reglas:

- Solo incluye médicos con rol `doctor` y `activo`.
- Si un médico tiene horario y además un registro manual con el mismo nombre, aparece **una sola vez**.
- El horario manda para las horas y el turno.
- El registro manual aporta el consultorio.
- Si no hay registro manual, el consultorio es “Consultorio de la Microred”.
- La foto se toma de la cuenta del médico, si existe.

Métodos:

- `delDia($fecha)`: arma la colección.
- `manana($medicos)`: filtra los que trabajan en la mañana.
- `tarde($medicos)`: filtra los de la tarde.
- `total($medicos)`: cuenta.
- `atendiendo($medicos)`: cuenta los que están atendiendo ahora.
- `normalizarNombre()`: quita “Dr.”, “Dra.”, tildes y mayúsculas para comparar nombres.
- `etiquetaTurno()`: convierte `manana` en “Mañana”.
- `estadoHorario()`: calcula si el turno ya terminó, está en curso o aún no empieza.

### 9.3 `AgendaCitas`

Es el servicio que calcula los **bloques de 30 minutos** de un médico.

Constantes:

```php
DURACION_MINUTOS = 30;
ESTADOS_ACTIVOS = ['Confirmada', 'Pendiente'];
```

#### `horarioDelDia($medico, $fecha)`

Busca el turno del médico para una fecha. Si no tiene horario, devuelve `null`.

#### `slots($medico, $fecha)`

1. Busca el horario del día.
2. Recorre desde `hora_inicio` hasta `hora_fin`, sumando 30 minutos.
3. Descarta los bloques que ya pasaron.
4. Descarta los bloques ocupados por citas activas.
5. Devuelve una lista como:
   ```php
   [
       ['hora' => '07:00', 'etiqueta' => '07:00'],
       ['hora' => '07:30', 'etiqueta' => '07:30'],
   ]
   ```

#### `slotValido($medico, $fecha, $hora)`

Comprueba:

- que la hora tenga formato `HH:MM`,
- que sea múltiplo de 30,
- que esté dentro del turno,
- que no esté en el pasado.

#### `fechaHora($fecha, $hora)`

Combina una fecha y una hora en la zona horaria de la aplicación.

### 9.4 `HorarioGenerator`

Es el servicio que genera **exactamente 150 horas mensuales** para un trabajador.

Reglas:

- Lunes a viernes: mañana (6 h), tarde (6.5 h) o todo el día (12 h).
- Sábados: solo 07:00 a 15:00 (8 h).
- Domingos: sin atención.

#### ¿Cómo logra exactamente 150 horas?

Ejemplo de combinación:

- 4 días completos: 4 × 12 = 48 h
- 12 mañanas: 12 × 6 = 72 h
- 4 tardes: 4 × 6.5 = 26 h
- 2 sábados: 2 × 8 = 16 h
- **Total: 162 h** → demasiado.

El algoritmo prueba combinaciones al azar hasta encontrar una que sume exactamente 150:

```text
150 = 12 × días completos + 6 × mañanas + 6.5 × tardes + 8 × sábados
```

Para resolverlo, primero elige cuántos días completos y sábados usar. Luego resuelve la parte de mañanas y tardes:

```text
6 × mañanas + 6.5 × tardes = horas restantes
```

Finalmente asigna esos turnos a días reales del mes. Hace hasta 500 intentos; si no encuentra, lanza un error.

#### `generar($user, $anio, $mes)`

1. Calcula el primer y último día del mes.
2. Separa días de semana y sábados; ignora domingos.
3. Busca una combinación válida.
4. Abre una **transacción**:
   - borra el horario anterior del mes,
   - inserta las filas nuevas.
5. Devuelve 150.0.

La transacción garantiza que, si algo falla a mitad, no queden horarios a medias.

---

## 10. Middleware y seguridad

Un **middleware** es un portero que revisa la petición antes de que llegue al controlador.

### 10.1 `EnsureRole`

Se usa así:

```php
Route::middleware(['auth', 'role:doctor'])->group(function () {
    // rutas solo para médicos
});
```

Hace lo siguiente:

1. Si no hay usuario autenticado, redirige al login correspondiente.
2. Si la cuenta está inactiva, cierra la sesión y redirige con un mensaje.
3. Si el rol no está permitido:
   - administración → panel de administración,
   - personal → panel del empleado,
   - otro → error 403.
4. Si todo está bien, deja pasar la petición.

### 10.2 `SecurityHeaders`

Agrega cabeceras de seguridad a todas las respuestas:

- `X-Frame-Options: SAMEORIGIN` → evita que otro sitio incruste el portal.
- `X-Content-Type-Options: nosniff` → evita que el navegador adivine tipos de archivo.
- `Referrer-Policy: strict-origin-when-cross-origin` → limita la información que se envía a otros sitios.

### 10.3 Otras protecciones de Laravel

| Protección | Qué hace | Dónde se ve |
| --- | --- | --- |
| **CSRF** | Evita que otro sitio envíe formularios en tu nombre | `@csrf` en cada formulario |
| **Sesiones** | Guardan quién está autenticado | `SESSION_DRIVER=database` |
| **Hash bcrypt** | Guarda contraseñas cifradas, no en texto plano | `config/auth.php`, `User` |
| **Rate limiting** | Bloquea después de 5 intentos de login | `RateLimiter` |
| **Validación** | Rechaza datos inválidos antes de guardar | `$request->validate()` y Form Requests |
| **Middleware de rol** | Impide que un rol entre a otro panel | `EnsureRole` |
| **Escape de Blade** | Convierte `<` en `&lt;` para evitar XSS | `{{ $variable }}` |

### 10.4 CSRF explicado en simple

Imagina que tu sesión es una pulsera. Otro sitio podría intentar enviar un formulario usando tu pulsera. Para evitarlo, Laravel incluye en cada formulario un **token secreto**:

```blade
<form method="POST" action="/ingreso-dni">
    @csrf
    ...
</form>
```

El token debe coincidir con el de la sesión. Si no coincide, Laravel responde **419 Page Expired**.

### 10.5 Rate limiting explicado en simple

Si alguien intenta adivinar una contraseña, el sistema cuenta los intentos. Después de 5 fallos en un minuto, responde:

```text
429 Too Many Requests
```

Eso hace que probar miles de contraseñas sea mucho más lento.

---

## 11. Form Requests y validación

Un **Form Request** es una clase que agrupa las reglas de validación de un formulario. En Hampi hay tres, en `app/Http/Requests/Admin/`:

### 11.1 `StoreTurnoRequest`

Valida un médico visitante:

```php
'medico_nombre' => ['required', 'string', 'max:100'],
'especialidad' => ['required', 'string', 'max:100'],
'turno' => ['required', 'in:Mañana,Tarde'],
'consultorio' => ['required', 'string', 'max:50'],
```

- `required`: obligatorio.
- `string`: debe ser texto.
- `max:100`: no más de 100 caracteres.
- `in:...`: solo acepta esos valores.

`authorize()` comprueba que el usuario sea administrador.

### 11.2 `UpdateTurnoEstadoRequest`

Solo acepta:

```php
'estado' => ['required', 'in:Atendiendo,Turno Completo,Reprogramado'],
```

### 11.3 `StoreVacunaRequest`

Valida:

```php
'titulo' => ['required', 'string', 'max:100'],
'grupo_objetivo' => ['required', 'string', 'max:150'],
'stock_estado' => ['required', 'in:Disponible,Por agotarse,Agotado'],
'requisitos' => ['required', 'string'],
'es_urgente' => ['sometimes', 'boolean'],
```

Si falla, Laravel vuelve atrás con los errores y los mensajes se muestran en la vista.

### 11.4 Ejemplo de error de validación

Si alguien envía un DNI de 7 dígitos en el login del personal, el sistema no lo deja pasar:

```text
El campo DNI debe tener 8 dígitos.
```

La regla es `digits:8`.

---

## 12. Vistas Blade

Blade es el motor de plantillas de Laravel. Permite mezclar HTML con instrucciones PHP de forma legible.

### 12.1 Estructura de una vista

```blade
@extends('hampi.layout')

@section('title', 'Médicos de turno hoy · Hampi')

@section('content')
    <x-page-board>
        <x-slot:hero>
            <x-page-hero
                eyebrow="Rol de atención"
                title="Médicos de turno hoy"
                description="Especialidades y estado de atención."
                icon="stethoscope"
            />
        </x-slot:hero>

        @foreach ($turnos as $turno)
            <p>{{ $turno->medico_nombre }}</p>
        @endforeach
    </x-page-board>
@endsection
```

- `@extends` usa una plantilla base.
- `@section` llena una sección de esa base.
- `{{ ... }}` imprime una variable **escapada** (segura).
- `@foreach` repite HTML por cada elemento.
- `<x-...>` usa un componente reutilizable.

### 12.2 `layout.blade.php`

Es el marco de todas las páginas:

- `<head>` con título, descripción, favicon y assets.
- Header con logo, navegación y accesos.
- Menú móvil.
- Mensajes flash (`session('mensaje')`).
- `<main>` con el contenido de cada página.
- Footer.
- Barra inferior móvil con cinco accesos.
- `@stack('scripts')` para JavaScript específico de una vista.

### 12.3 Componentes

| Componente | Qué hace |
| --- | --- |
| `x-site-logo` | Logo H, nombre Hampi y badge Socabaya |
| `x-page-hero` | Cabecera con título, descripción, acciones e icono |
| `x-page-board` | Tablero común de páginas internas |
| `x-auth-shell` | Marco de las pantallas de acceso |
| `x-empty-state` | Bloque para listas vacías |
| `x-badge` | Etiqueta de estado |
| `x-icon` | Iconos SVG reutilizables |

### 12.4 `x-page-board`

Es el componente que unificó el diseño. Recibe:

- el **hero** en un slot,
- el **contenido** en el slot por defecto,
- opcionalmente el **nav** de administración.

Renderiza el tablero de corcho, el hero en una hoja blanca y el contenido en otra hoja.

### 12.5 Vistas públicas

| Vista | Qué muestra |
| --- | --- |
| `hampi/login.blade.php` | Portada, formulario DNI, servicios, FAQ |
| `hampi/medicos.blade.php` | Rol de médicos por turno, con foto o iniciales |
| `hampi/pizarrin.blade.php` | Avisos de vacunas con filtros |
| `hampi/campanas.blade.php` | Campaña destacada y siguientes campañas |
| `hampi/postas.blade.php` | Fichas de establecimientos |
| `hampi/panel_paciente.blade.php` | Próxima cita, historial y accesos |

### 12.6 Vistas del personal

| Vista | Qué muestra |
| --- | --- |
| `empleado/login.blade.php` | Acceso con DNI |
| `empleado/panel.blade.php` | Horario mensual, calendario, perfil y foto |
| `empleado/citas/index.blade.php` | Citas de hoy, próximas e historial |
| `empleado/citas/create.blade.php` | Buscar paciente y elegir bloque |

### 12.7 Vistas de administración

| Vista | Qué muestra |
| --- | --- |
| `hampi/admin_dashboard.blade.php` | Resumen, rol y pizarrín |
| `hampi/admin/equipo.blade.php` | Alta, edición y listado del equipo |
| `hampi/admin/horarios.blade.php` | Calendario y generación de 150 h |
| `hampi/admin/campanas.blade.php` | Publicación y edición de campañas |

### 12.8 Accesibilidad en las vistas

- Enlace “Saltar al contenido”.
- `aria-label` en botones y navegación.
- `aria-current="page"` en la sección activa.
- `aria-live` para mensajes dinámicos.
- Foco visible.
- Controles de al menos 44 px.
- No se comunica un estado solo con color: siempre hay texto.
- `prefers-reduced-motion` desactiva animaciones.

---

## 13. CSS, diseño y accesibilidad

### 13.1 Tailwind CSS

Tailwind permite escribir el diseño con clases directamente en el HTML:

```html
<button class="rounded bg-brand-700 px-4 py-2 text-white">
    Entrar
</button>
```

En lugar de crear un archivo CSS enorme, se usan clases pequeñas. Tailwind lee las vistas y genera solo el CSS necesario.

### 13.2 Variables de diseño

En `resources/css/app.css` están los colores y las fuentes:

```css
--color-paper-50: #fbfaf6;
--color-paper-100: #f4f2ea;
--color-brand-700: #17522f;
--color-ink-950: #0b0f0c;
--color-signal-600: #c9471b;
--color-stamp-600: #b3261e;
```

- **paper** = papel.
- **brand** = verde de la posta.
- **ink** = tinta.
- **signal** = naranja de señal.
- **stamp** = rojo de sello.

### 13.3 El fondo y las hojas

- `bg-ledger`: fondo global de papel con retícula.
- `bg-ledger-carteles`: tablero de corcho de las páginas internas.
- `.sheet`: hoja blanca con borde y sombra.
- `.sheet-doctor`: hoja blanca de las páginas internas.
- `x-page-board`: agrupa hero y contenido en dos hojas.

### 13.4 Tipografías

- **Archivo** para títulos.
- **Instrument Sans** para texto.
- **Martian Mono** para datos, fechas, DNI y estados.

Se sirven desde `public/fonts/hampi/`; no se piden a Google Fonts. Eso mejora privacidad y velocidad.

### 13.5 Responsive

- En móvil se usa menú hamburguesa y barra inferior.
- Las tablas permiten desplazamiento horizontal.
- Los formularios se apilan.
- Los botones miden al menos 44 px.
- Los campos usan 16 px para evitar zoom en iOS.

### 13.6 Movimiento

- `data-reveal`: los bloques aparecen suavemente al entrar en pantalla.
- Si el usuario prefiere menos movimiento, se desactiva.
- No hay animaciones infinitas.

---

## 14. JavaScript del navegador

El archivo `resources/js/app.js` se ejecuta en el navegador. No contiene la lógica del negocio; solo mejora la experiencia.

### 14.1 Menú móvil

```js
toggle.addEventListener('click', () => {
    setOpen(menu.hidden);
});
```

Abre y cierra el menú. También:
- actualiza `aria-expanded`,
- cambia el icono,
- cierra con `Escape`,
- mueve el foco al primer enlace.

### 14.2 Sombra del header al hacer scroll

```js
header.classList.toggle('is-scrolled', window.scrollY > 8);
```

Agrega una sombra cuando la página se desplaza.

### 14.3 Animación de entrada

Usa `IntersectionObserver` para mostrar bloques cuando entran en pantalla. Si el navegador no lo soporta o el usuario prefiere menos movimiento, los muestra de inmediato.

### 14.4 Mostrar/ocultar contraseña

```js
input.type = isPassword ? 'text' : 'password';
```

Cambia el tipo del campo y actualiza el texto “Mostrar/Ocultar”.

### 14.5 Filtros del pizarrín

Los botones “Todas”, “Disponibles”, “Por agotarse” y “Agotadas” ocultan o muestran tarjetas según `data-stock`.

### 14.6 Disponibilidad de citas

Cuando el médico cambia la fecha, el navegador pide:

```text
GET /empleado/citas/disponibilidad?fecha=2026-10-26
```

Y recibe un JSON con los bloques libres. Luego llena el `<select>` de horas.

### 14.7 Consulta RENIEC

En la portada, cuando el DNI tiene 8 dígitos, el JavaScript llama a:

```text
GET /api/consultar-dni/72345678
```

Muestra un spinner, rellena nombres y apellidos, y si falla permite escribirlos a mano.

---

## 15. Flujos completos, paso a paso

### 15.1 Ciudadano entra con su DNI

1. Abre `/`.
2. Escribe `72345678`.
3. El JavaScript consulta RENIEC (si hay token).
4. Envía el formulario a `POST /ingreso-dni` con el token CSRF.
5. `HampiController@accesoPorDni` busca o crea el ciudadano.
6. Guarda el DNI en la sesión.
7. Redirige a `/mi-panel`.
8. El panel muestra sus citas.

**Ejemplo de sesión:**

```text
Cookie: laravel_session=abc123...
Servidor: ciudadano_dni = 72345678
```

### 15.2 Ver médicos del día

1. El usuario entra a `/medicos-hoy`.
2. La ruta llama a `HampiController@verMedicos`.
3. `AgendaMedica` busca horarios de hoy y el rol manual.
4. Deduplica por nombre.
5. Calcula estado: Programado, Atendiendo o Finalizado.
6. La vista muestra foto si existe; si no, iniciales.
7. La foto se sirve desde `/storage/perfiles/...`.

### 15.3 Doctor inicia sesión

1. Entra a `/equipo/login`.
2. Escribe DNI `45678912` y contraseña `socabaya2024`.
3. El controlador busca al usuario.
4. Verifica `activo`, rol de personal y `Hash::check`.
5. Crea la sesión.
6. Redirige a `/empleado/panel`.

Si falla 5 veces, recibe 429.

### 15.4 Doctor agenda una cita

1. Entra a `/empleado/citas/crear`.
2. Busca al paciente por DNI.
3. Si no existe, escribe nombres y apellidos.
4. Elige fecha.
5. El JavaScript pide los bloques disponibles.
6. Elige un bloque de 30 minutos.
7. Envía el formulario.
8. El controlador valida:
   - DNI,
   - posta,
   - fecha,
   - hora,
   - que el bloque esté dentro del turno,
   - que no esté ocupado.
9. Si todo está bien, crea la cita en una transacción.
10. La cita aparece en el panel del ciudadano.

### 15.5 Administración genera 150 horas

1. Entra a `/admin/horarios`.
2. Elige trabajador, año y mes.
3. Pulsa “Generar”.
4. El controlador comprueba que no haya citas futuras activas en ese mes.
5. `HorarioGenerator` calcula una combinación de turnos.
6. Borra el horario anterior del mes.
7. Inserta las filas nuevas.
8. El calendario muestra los turnos y el total de horas.

### 15.6 Administración publica una vacuna

1. Entra a `/admin/dashboard`.
2. Completa el formulario del pizarrín.
3. `StoreVacunaRequest` valida.
4. `VacunaController@store` limpia el HTML y guarda.
5. El aviso aparece en `/pizarrin-vacunas`.

### 15.7 Doctor sube su foto

1. Entra a `/empleado/panel`.
2. En “Editar mi perfil” elige una imagen JPG, PNG o WebP de menos de 2 MB.
3. El formulario tiene `enctype="multipart/form-data"`.
4. `updatePerfil` valida la imagen.
5. Guarda el archivo en `storage/app/public/perfiles`.
6. Actualiza `foto_perfil` en la base de datos.
7. `getFotoUrlAttribute` devuelve `/storage/perfiles/...`.
8. La foto aparece en el panel, en el equipo y en el rol de médicos.

El código que guarda la foto es:

```php
if ($request->hasFile('foto_perfil')) {
    if ($user->foto_perfil) {
        Storage::disk('public')->delete($user->foto_perfil);
    }

    $datos['foto_perfil'] = $request->file('foto_perfil')->store('perfiles', 'public');
}
```

El disco `public` está configurado así en `config/filesystems.php`:

```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => '/storage',
    'visibility' => 'public',
],
```

La URL es **relativa** (`/storage`) para que funcione igual en local, Codespaces o un dominio propio. El navegador la resuelve contra el host actual.

La vista muestra la foto así:

```blade
@if ($user->foto_url)
    <img src="{{ $user->foto_url }}" alt="Foto de {{ $user->name }}">
@else
    {{ $user->iniciales() }}
@endif
```

`foto_url` es un **accessor**: Laravel lo calcula al pedir `$user->foto_url`. Revisa que el archivo exista y devuelve `null` si no, para que la vista use las iniciales.

---

## 16. Tests y verificaciones

### 16.1 Qué es un test

Un test es un programa que comprueba que otra parte del programa hace lo que debe. Si algo se rompe, el test falla y avisa.

Se ejecutan con:

```bash
php artisan test
```

En este proyecto hay **37 tests** y más de **240 comprobaciones** (`assertions`).

### 16.2 Tests por área

| Archivo | Qué comprueba |
| --- | --- |
| `AgendaMedicaTest` | Agenda pública, foto del médico, médico inactivo, rol manual, CRUD manual, turno inválido |
| `CitaMedicaTest` | Buscar paciente, agendar, paciente nuevo, bloques inválidos, disponibilidad, cerrar cita, aislamiento entre médicos, panel ciudadano |
| `EmpleadoYEquipoTest` | Login por DNI, bloqueo de admin, cuenta inactiva, crear trabajador, editar perfil y subir foto, campañas |
| `HorarioGeneratorTest` | 150 h exactas, reglas de domingo/sábado, varios años, regeneración |
| `LoginRenderTest` | Las pantallas de login renderizan la plantilla compartida |
| `SecurityTest` | Inyección SQL en DNI, rate limiting, bcrypt |
| `VacunaAvisoTest` | Publicar/retirar avisos y estado de stock inválido |

### 16.3 Ejemplo de un test

Un test de Hampi se ve así:

```php
public function test_el_admin_publica_un_aviso(): void
{
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('admin.vacuna.store'), [
        'titulo' => 'Vacuna de prueba',
        'grupo_objetivo' => 'Adultos',
        'stock_estado' => 'Disponible',
        'requisitos' => 'DNI',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('vacuna_avisos', [
        'titulo' => 'Vacuna de prueba',
    ]);
}
```

Se lee así:

1. Crea un administrador falso.
2. Actúa como si ese administrador hubiera enviado el formulario.
3. Comprueba que la respuesta sea una redirección.
4. Comprueba que el aviso quedó guardado en la base de datos.

### 16.4 Cómo se ejecutan sin MySQL

`phpunit.xml` configura:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

Cada test usa una base SQLite **en memoria**. Se crea al empezar y se borra al terminar. Por eso los tests son rápidos y no ensucian la base real.

### 16.5 Smoke tests manuales

Además de los tests, se probaron las rutas principales con el servidor encendido:

```text
200 /
200 /medicos-hoy
200 /pizarrin-vacunas
200 /campanas-preventivas
200 /directorio-postas
200 /personal/login
200 /equipo/login
200 /admin/dashboard
200 /admin/equipo
200 /admin/horarios
200 /admin/campanas
200 /empleado/panel
200 /empleado/citas
200 /empleado/citas/crear
200 /mi-panel
200 /storage/perfiles/...
```

También se comprobó:

- login de administración con `enfermeria@hampi.pe`,
- login de personal con DNI `45678912`,
- login ciudadano con DNI `72345678`,
- foto de perfil servida con estado 200,
- compilación de assets con `npm run build`.

### 16.6 Comandos útiles

```bash
# Todos los tests
php artisan test

# Solo tests de funcionalidad
php artisan test --testsuite=Feature

# Un archivo concreto
php artisan test tests/Feature/CitaMedicaTest.php

# Limpiar caché de vistas
php artisan view:clear

# Compilar vistas para detectar errores de sintaxis
php artisan view:cache

# Ver todas las rutas registradas
php artisan route:list

# Ver el estado de las migraciones
php artisan migrate:status

# Abrir una consola interactiva para consultar datos
php artisan tinker

# Desarrollo con recarga automática de CSS/JS
npm run dev
```

`php artisan route:list` es especialmente útil para la exposición: muestra una tabla con todas las URLs, sus nombres, métodos y middleware.

---

## 17. Cómo levantar el proyecto

### 17.1 Requisitos

- PHP 8.2+ con `pdo_mysql`, `intl`, `fileinfo`.
- Composer.
- MySQL 8 o MariaDB.
- Node.js 22.12+ y npm (solo para construir assets).

### 17.2 Instalación local

```bash
git clone https://github.com/pepito11442/hampi-web.git
cd hampi-web
composer install
cp .env.example .env
php artisan key:generate
```

Crear la base:

```bash
mysql -u root -e "CREATE DATABASE hampi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Configurar `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hampi
DB_USERNAME=root
DB_PASSWORD=
```

Variables principales del `.env`:

| Variable | Para qué sirve | Valor típico |
| --- | --- | --- |
| `APP_NAME` | Nombre de la app | `Hampi` |
| `APP_ENV` | Entorno | `local` o `production` |
| `APP_DEBUG` | Muestra errores detallados | `true` en local, `false` en producción |
| `APP_URL` | URL base | `http://127.0.0.1:8000` |
| `APP_TIMEZONE` | Zona horaria | `America/Lima` |
| `DB_CONNECTION` | Motor de base de datos | `mysql` |
| `DB_DATABASE` | Nombre de la base | `hampi` |
| `SESSION_DRIVER` | Dónde se guardan las sesiones | `database` |
| `FILESYSTEM_DISK` | Disco por defecto | `local` |
| `RENIEC_API_TOKEN` | Token opcional de RENIEC | vacío o token real |

Migrar y cargar datos:

```bash
php artisan migrate --seed
```

Crear el enlace de almacenamiento:

```bash
php artisan storage:link
```

En Windows, si el enlace no funciona:

```powershell
cmd /c rmdir public\storage
cmd /c mklink /J public\storage storage\app\public
```

Compilar assets:

```bash
npm install
npm run build
```

Levantar:

```bash
php artisan serve
```

Abrir `http://127.0.0.1:8000`.

### 17.3 Codespaces

Los pasos están en el README. Lo importante:

- compilar `pdo_mysql` e `intl` si faltan,
- levantar MySQL,
- configurar `.env`,
- `php artisan migrate --seed`,
- `php artisan storage:link`,
- `npm ci && npm run build`,
- `php artisan serve --host=0.0.0.0 --port=8000`,
- abrir el puerto 8000.

### 17.4 Producción

Para un despliegue real:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Y configurar HTTPS, `APP_DEBUG=false`, base de datos con usuario limitado, respaldos y monitoreo.

---

## 18. Guion para la exposición

### 18.1 Credenciales de demostración

| Perfil | Dónde entra | Usuario | Contraseña |
| --- | --- | --- | --- |
| Administración | `/personal/login` | `enfermeria@hampi.pe` | `socabaya2024` |
| Médico | `/equipo/login` | DNI `45678912` | `socabaya2024` |
| Médica | `/equipo/login` | DNI `41234567` | `socabaya2024` |
| Enfermería | `/equipo/login` | DNI `47891234` | `socabaya2024` |
| Enfermería | `/equipo/login` | DNI `46789123` | `socabaya2024` |
| Ciudadanía | `/` | DNI `72345678` | sin contraseña |

### 18.2 Recorrido sugerido

Una forma de presentar el proyecto en 10–15 minutos:

### Minuto 0–1: presentación

> “Hampi es el portal de la Microred Socabaya. Permite que un vecino consulte médicos, vacunas, campañas y sus citas con su DNI, y que el personal de salud gestione horarios. Está hecho con Laravel, MySQL y una interfaz mobile-first.”

### Minuto 1–3: portada

- Mostrar los tres indicadores: médicos, vacunas, próxima campaña.
- Escribir un DNI de prueba: `72345678`.
- Entrar al panel ciudadano y mostrar la cita de demostración.

### Minuto 3–5: información pública

- Abrir “Médicos”.
- Señalar la foto de la Dra. María Paredes y las iniciales del Dr. Eduardo.
- Abrir “Vacunas”, filtrar por estado.
- Abrir “Campañas” y “Postas”.

### Minuto 5–7: personal de salud

- Entrar a `/equipo/login` con `45678912` / `socabaya2024`.
- Mostrar el calendario mensual y las 150 horas.
- Mostrar la foto de perfil y explicar que se guarda en `storage`.
- Ir a “Mis citas” y agendar una cita.

### Minuto 7–9: administración

- Entrar a `/personal/login` con `enfermeria@hampi.pe` / `socabaya2024`.
- Mostrar el panel.
- Generar un horario de 150 h.
- Publicar una vacuna.
- Mostrar que no se puede regenerar un horario con citas futuras.

### Minuto 9–11: por dentro

- Mostrar `routes/web.php`: “esta es la lista de direcciones”.
- Mostrar un controlador: “recibe la petición”.
- Mostrar un modelo: “habla con la base de datos”.
- Mostrar una vista Blade: “genera el HTML”.
- Mostrar un servicio: “aquí está la regla de negocio”.

### Minuto 11–13: seguridad y pruebas

- Explicar login con DNI + contraseña para personal y correo para admin.
- Mostrar CSRF, rate limiting y roles.
- Ejecutar `php artisan test` y mostrar **37 tests pasando**.

### Minuto 13–15: cierre

> “La demo es honesta: los datos son simulados y el acceso ciudadano es solo por DNI, pensado para la exposición. Para producción haría falta un segundo factor de autenticación, actualizar Laravel y restringir RENIEC.”

---

## 19. Preguntas frecuentes de la exposición

### ¿Por qué usaron Laravel y no PHP puro?

Porque Laravel ya resuelve rutas, sesiones, seguridad, base de datos, validaciones y pruebas. Así el equipo se concentra en las reglas de salud y no en reinventar la infraestructura.

### ¿Por qué hay Node si es PHP?

Node solo se usa para **construir** el CSS y el JavaScript con Vite/Tailwind. La página se ejecuta con PHP. Si `public/build` ya está compilado, Node no participa en la atención de las peticiones.

### ¿Dónde está la base de datos?

En MySQL. El archivo `.env` tiene la conexión. Los tests usan SQLite en memoria para no depender de MySQL.

### ¿Cómo se guarda la contraseña?

Con **bcrypt**, un algoritmo de hash. La contraseña original no se guarda; solo un valor cifrado que no se puede revertir fácilmente.

### ¿Qué pasa si alguien intenta entrar muchas veces?

Después de 5 intentos fallidos, el sistema responde 429 y escribe una advertencia en el log.

### ¿Cómo se evita que un médico vea las citas de otro?

Cada consulta filtra por `user_id` del médico autenticado. Además, al cerrar una cita se comprueba que pertenezca al médico.

### ¿Cómo se calculan las 150 horas?

El generador prueba combinaciones de turnos de 6, 6.5, 8 y 12 horas hasta sumar exactamente 150, respetando domingos y sábados.

### ¿Qué pasa si se regenera un horario con citas futuras?

Se bloquea. Primero hay que cancelar o reprogramar esas citas.

### ¿Por qué las fotos no se guardan en la carpeta pública directamente?

Por seguridad y orden: se guardan en `storage`, y solo se expone un enlace controlado (`public/storage`). Así no se mezclan archivos subidos con el código.

### ¿Qué es el DNI en la sesión?

Es la forma en que el portal recuerda qué ciudadano está consultando. No es una contraseña; es una identificación de demostración.

---

## 20. Glosario para no programadores

| Término | Significado simple |
| --- | --- |
| **API** | Puerta por la que dos programas se comunican. |
| **Asset** | Archivo estático: CSS, JS, imagen o fuente. |
| **Backend** | Parte que corre en el servidor (PHP, base de datos). |
| **Base de datos** | Conjunto de tablas ordenadas. |
| **bcrypt** | Algoritmo que cifra contraseñas. |
| **Blade** | Motor de plantillas de Laravel. |
| **Composer** | Instalador de librerías PHP. |
| **Controlador** | Clase que recibe una petición y decide qué hacer. |
| **Cookie** | Pequeño dato que el navegador guarda para recordar la sesión. |
| **CSRF** | Token que evita formularios falsos. |
| **CSS** | Lenguaje que da estilo y diseño. |
| **DTO** | Objeto simple que transporta datos entre partes. |
| **Eloquent** | Herramienta de Laravel para hablar con la base de datos. |
| **Frontend** | Parte que ve el usuario en el navegador. |
| **GET** | Petición para pedir información. |
| **Hash** | Resultado cifrado de una contraseña. |
| **HTML** | Lenguaje de estructura de las páginas. |
| **HTTP** | Protocolo con el que el navegador y el servidor hablan. |
| **JSON** | Formato de texto para intercambiar datos. |
| **Laravel** | Framework de PHP. |
| **Middleware** | Portero que revisa una petición antes del controlador. |
| **Migración** | Archivo que crea o modifica una tabla. |
| **Modelo** | Clase que representa una tabla. |
| **MVC** | Modelo-Vista-Controlador. |
| **Node.js** | Programa que ejecuta herramientas de frontend. |
| **npm** | Instalador de herramientas de Node. |
| **ORM** | Traductor entre objetos PHP y tablas SQL. |
| **PHP** | Lenguaje del servidor. |
| **POST** | Petición para enviar datos. |
| **Rate limit** | Límite de intentos. |
| **Request** | Petición que llega al servidor. |
| **Response** | Respuesta que devuelve el servidor. |
| **Ruta** | Dirección que activa un controlador. |
| **Seeder** | Archivo que llena la base con datos de ejemplo. |
| **Servicio** | Clase con reglas del negocio. |
| **Sesión** | Memoria temporal del servidor sobre quién eres. |
| **SQL** | Lenguaje de consultas a bases de datos. |
| **Tailwind** | Herramienta de estilos por clases. |
| **Test** | Prueba automática. |
| **Token** | Cadena secreta de seguridad. |
| **Transacción** | Grupo de cambios que se guardan todos o ninguno. |
| **Vista** | Plantilla que genera HTML. |
| **Vite** | Herramienta que empaqueta CSS y JS. |
| **XSS** | Ataque que inyecta código en una página. |

---

## 21. Índice de archivos

### Raíz

| Archivo | Para qué sirve |
| --- | --- |
| `.env` | Configuración local y secretos (no se sube). |
| `.env.example` | Plantilla de configuración. |
| `.gitignore` | Lista de archivos que no se suben. |
| `artisan` | Comando de Laravel. |
| `composer.json` | Dependencias PHP. |
| `composer.lock` | Versiones exactas de las dependencias. |
| `package.json` | Herramientas de frontend. |
| `package-lock.json` | Versiones exactas de Node. |
| `vite.config.js` | Configuración de Vite. |
| `phpunit.xml` | Configuración de los tests. |
| `docker-compose.yml` | MySQL para desarrollo. |
| `README.md` | Guía de instalación y uso. |
| `DESIGN.md` | Sistema visual. |
| `PRODUCT.md` | Verdad de producto. |
| `CHANGELOG.md` | Historial de cambios. |
| `DOCUMENTACION.md` | Este documento. |

### `app/Http/Controllers`

| Archivo | Responsabilidad |
| --- | --- |
| `Controller.php` | Base de los controladores. |
| `AuthController.php` | Login de administración. |
| `EmpleadoController.php` | Login y perfil del personal. |
| `EmpleadoCitaController.php` | Agenda de citas del médico. |
| `HampiController.php` | Portal público y panel ciudadano. |
| `Admin/DashboardController.php` | Panel general. |
| `Admin/EquipoController.php` | Cuentas del personal. |
| `Admin/HorarioController.php` | Horarios mensuales. |
| `Admin/CampanaController.php` | Campañas preventivas. |
| `Admin/TurnoController.php` | Rol manual de médicos. |
| `Admin/VacunaController.php` | Pizarrín de vacunas. |

### `app/Http/Middleware`

| Archivo | Responsabilidad |
| --- | --- |
| `EnsureRole.php` | Comprueba autenticación, estado y rol. |
| `SecurityHeaders.php` | Agrega cabeceras de seguridad. |

### `app/Http/Requests/Admin`

| Archivo | Valida |
| --- | --- |
| `StoreTurnoRequest.php` | Alta de médico visitante. |
| `UpdateTurnoEstadoRequest.php` | Cambio de estado del rol. |
| `StoreVacunaRequest.php` | Aviso de vacuna. |

### `app/Models`

| Archivo | Tabla |
| --- | --- |
| `User.php` | `users` |
| `Ciudadano.php` | `ciudadanos` |
| `Cita.php` | `citas` |
| `Horario.php` | `horarios` |
| `Posta.php` | `postas` |
| `TurnoMedico.php` | `turno_medicos` |
| `VacunaAviso.php` | `vacuna_avisos` |
| `CampanaPreventiva.php` | `campanas_preventivas` |

### `app/Services`

| Archivo | Responsabilidad |
| --- | --- |
| `AgendaMedica.php` | Combina horarios y rol manual. |
| `AgendaCitas.php` | Bloques de 30 minutos. |
| `HorarioGenerator.php` | Genera 150 horas. |
| `MedicoDelDia.php` | DTO de la agenda. |

### `app/Providers`

| Archivo | Responsabilidad |
| --- | --- |
| `AppServiceProvider.php` | Arranque de servicios (vacío por ahora). |

### `resources/views`

| Archivo | Responsabilidad |
| --- | --- |
| `hampi/layout.blade.php` | Marco general. |
| `hampi/login.blade.php` | Portada. |
| `hampi/medicos.blade.php` | Médicos del día. |
| `hampi/pizarrin.blade.php` | Vacunas. |
| `hampi/campanas.blade.php` | Campañas. |
| `hampi/postas.blade.php` | Postas. |
| `hampi/panel_paciente.blade.php` | Panel ciudadano. |
| `hampi/auth_login.blade.php` | Login administrativo. |
| `hampi/admin_dashboard.blade.php` | Panel admin. |
| `hampi/admin/equipo.blade.php` | Equipo. |
| `hampi/admin/horarios.blade.php` | Horarios. |
| `hampi/admin/campanas.blade.php` | Campañas admin. |
| `hampi/partials/admin-nav.blade.php` | Tabs de administración. |
| `hampi/partials/calendario.blade.php` | Calendario mensual. |
| `empleado/login.blade.php` | Login del personal. |
| `empleado/panel.blade.php` | Panel del personal. |
| `empleado/citas/index.blade.php` | Citas del médico. |
| `empleado/citas/create.blade.php` | Agendar cita. |
| `components/*` | Componentes reutilizables. |

### `routes`

| Archivo | Responsabilidad |
| --- | --- |
| `web.php` | Todas las rutas web. |
| `console.php` | Comandos de consola. |

### `config`

| Archivo | Responsabilidad |
| --- | --- |
| `app.php` | Nombre, zona horaria, idioma. |
| `auth.php` | Autenticación. |
| `database.php` | Conexiones MySQL/SQLite. |
| `filesystems.php` | Discos de archivos y URL `/storage`. |
| `navegacion.php` | Enlaces del portal. |
| `services.php` | Token de RENIEC. |
| `session.php` | Sesiones y cookies. |
| `cache.php`, `queue.php`, `mail.php`, `logging.php` | Infraestructura de Laravel. |

### `database`

| Archivo | Responsabilidad |
| --- | --- |
| `migrations/*` | Planos de las tablas. |
| `seeders/DatabaseSeeder.php` | Datos de demostración. |
| `factories/UserFactory.php` | Datos falsos para tests. |

### `tests`

| Archivo | Responsabilidad |
| --- | --- |
| `Feature/AgendaMedicaTest.php` | Agenda pública. |
| `Feature/CitaMedicaTest.php` | Citas. |
| `Feature/EmpleadoYEquipoTest.php` | Personal y equipo. |
| `Feature/HorarioGeneratorTest.php` | 150 horas. |
| `Feature/LoginRenderTest.php` | Pantallas de login. |
| `Feature/SecurityTest.php` | Seguridad. |
| `Feature/VacunaAvisoTest.php` | Pizarrín. |

---

## 22. Límites de la demo y advertencias

Este proyecto está pensado para una **exposición académica**. Para usarlo con datos reales habría que resolver, como mínimo:

1. **Acceso ciudadano con un segundo factor.** Hoy cualquiera que sepa un DNI puede ver las citas de esa persona. En producción haría falta un código por SMS/correo o una contraseña.
2. **Actualizar Laravel.** Laravel 11 ya no recibe parches de seguridad desde marzo de 2026.
3. **Restringir RENIEC.** La ruta de consulta es pública; en producción debería tener autenticación, captcha o un límite más estricto.
4. **Revisar `trustProxies`.** Confiar en todos los proxies permite falsificar la IP y saltarse el rate limiting.
5. **Añadir cabeceras CSP/HSTS** y configurar cookies seguras.
6. **Respaldos y monitoreo** de la base y de las fotos.
7. **No usar las contraseñas demo** ni ejecutar el seeder en producción.
8. **Revisar la privacidad** de los datos de salud (Ley 29733 en Perú).

La demo es honesta: muestra datos simulados y lo dice en el portal, en el README y en este documento.

---

> **Cierre para la exposición:** Hampi no es solo una página bonita; es una aplicación completa con rutas, controladores, modelos, servicios, validaciones, seguridad, pruebas y una base de datos. La interfaz es la parte visible, pero detrás hay un flujo ordenado que convierte una petición del navegador en información útil para un vecino de Socabaya.
