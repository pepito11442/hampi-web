<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0d9488">

    <title>@yield('title', 'Hampi · Tu salud, más cerca | Socabaya, Arequipa')</title>
    <meta name="description" content="@yield('meta_description', 'Hampi conecta a los vecinos de Socabaya con los servicios de su microred de salud: médicos de turno, pizarrín de vacunas y directorio de postas.')">

    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <script>
        document.documentElement.classList.add('js');
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-mist-50 text-ink-800">
    <a href="#contenido" class="skip-link">Saltar al contenido principal</a>

    <header data-site-header class="site-header sticky top-0 z-40 border-b border-ink-100 bg-white/90 backdrop-blur-md">
        {{-- Franja institucional (escritorio) --}}
        <div class="hidden border-b border-ink-100 bg-ink-950 text-white lg:block">
            <div class="container-page flex h-9 items-center justify-between text-[11px] font-medium tracking-wide">
                <p class="flex items-center gap-2 text-white/85">
                    <x-icon name="map-pin" class="h-3.5 w-3.5 text-brand-300" />
                    Microred Socabaya · Arequipa, Perú
                </p>
                <div class="flex items-center gap-6">
                    <span class="flex items-center gap-1.5 text-white/75">
                        <x-icon name="clock" class="h-3.5 w-3.5 text-brand-300" />
                        Atención de lunes a sábado · 07:30 a 19:30
                    </span>
                    <a href="/personal/login" class="flex items-center gap-1.5 font-semibold text-white transition-colors hover:text-brand-200">
                        <x-icon name="user-cog" class="h-3.5 w-3.5" />
                        Personal de salud
                    </a>
                </div>
            </div>
        </div>

        {{-- Barra principal --}}
        <div class="container-page flex h-16 items-center justify-between gap-3">
            <a href="/" class="group flex items-center gap-3" aria-label="Hampi, volver al inicio">
                <span class="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-glow transition-transform duration-300 group-hover:scale-105">
                    <x-icon name="heart-pulse" class="h-6 w-6" stroke-width="2" />
                </span>
                <span class="leading-tight">
                    <span class="flex items-center gap-2">
                        <span class="text-lg font-bold tracking-tight text-ink-900">Hampi</span>
                        <span class="hidden rounded-full border border-brand-200/70 bg-brand-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-brand-700 sm:inline-block">
                            Socabaya
                        </span>
                    </span>
                    <span class="block text-[11px] font-medium text-ink-400">Salud comunitaria · Arequipa</span>
                </span>
            </a>

            <nav class="hidden items-center gap-1 md:flex" aria-label="Navegación principal">
                <a href="/" class="nav-link {{ request()->is('/') ? 'nav-link-active' : '' }}" @if(request()->is('/')) aria-current="page" @endif>Inicio</a>
                <a href="/medicos-hoy" class="nav-link {{ request()->is('medicos-hoy') ? 'nav-link-active' : '' }}" @if(request()->is('medicos-hoy')) aria-current="page" @endif>Médicos del día</a>
                <a href="/pizarrin-vacunas" class="nav-link {{ request()->is('pizarrin-vacunas') ? 'nav-link-active' : '' }}" @if(request()->is('pizarrin-vacunas')) aria-current="page" @endif>Pizarrín de vacunas</a>
                <a href="/directorio-postas" class="nav-link {{ request()->is('directorio-postas') ? 'nav-link-active' : '' }}" @if(request()->is('directorio-postas')) aria-current="page" @endif>Postas</a>
                @if(session('ciudadano_dni'))
                    <a href="/mi-panel" class="nav-link {{ request()->is('mi-panel') ? 'nav-link-active' : '' }}" @if(request()->is('mi-panel')) aria-current="page" @endif>Mi panel</a>
                @endif
            </nav>

            <div class="flex items-center gap-2">
                @if(session('ciudadano_dni'))
                    <a href="/mi-panel" class="btn btn-primary btn-sm">
                        <x-icon name="calendar-check" class="h-4 w-4" />
                        <span class="hidden sm:inline">Mis citas</span>
                        <span class="sm:hidden">Panel</span>
                    </a>
                    <a href="/cerrar-sesion-ciudadano" class="hidden items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition-colors hover:bg-rose-100 lg:inline-flex">
                        <x-icon name="log-out" class="h-4 w-4" />
                        Salir
                    </a>
                @else
                    <a href="/#acceso" class="btn btn-primary btn-sm">
                        <x-icon name="id-card" class="h-4 w-4" />
                        <span class="hidden sm:inline">Acceso con DNI</span>
                        <span class="sm:hidden">Acceso</span>
                    </a>
                @endif

                <button
                    type="button"
                    data-menu-toggle
                    aria-controls="mobile-menu"
                    aria-expanded="false"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-ink-200 text-ink-700 transition-colors hover:border-brand-300 hover:text-brand-700 md:hidden"
                >
                    <x-icon name="menu" class="h-5 w-5" data-menu-icon-open />
                    <x-icon name="x" class="hidden h-5 w-5" data-menu-icon-close />
                    <span class="sr-only">Abrir menú de navegación</span>
                </button>
            </div>
        </div>

        {{-- Menú móvil --}}
        <div id="mobile-menu" hidden class="border-t border-ink-100 bg-white md:hidden">
            <nav class="container-page flex flex-col gap-1 py-4" aria-label="Navegación móvil">
                <a href="/" class="nav-link {{ request()->is('/') ? 'nav-link-active' : '' }}">Inicio</a>
                <a href="/medicos-hoy" class="nav-link {{ request()->is('medicos-hoy') ? 'nav-link-active' : '' }}">Médicos del día</a>
                <a href="/pizarrin-vacunas" class="nav-link {{ request()->is('pizarrin-vacunas') ? 'nav-link-active' : '' }}">Pizarrín de vacunas</a>
                <a href="/directorio-postas" class="nav-link {{ request()->is('directorio-postas') ? 'nav-link-active' : '' }}">Directorio de postas</a>
                @if(session('ciudadano_dni'))
                    <a href="/mi-panel" class="nav-link {{ request()->is('mi-panel') ? 'nav-link-active' : '' }}">Mi panel</a>
                    <a href="/cerrar-sesion-ciudadano" class="nav-link text-rose-600 hover:bg-rose-50 hover:text-rose-700">Cerrar sesión ciudadana</a>
                @endif
                <a href="/personal/login" class="nav-link">Personal de salud</a>
            </nav>
        </div>
    </header>

    @if(session('mensaje'))
        <div class="container-page pt-6">
            <div role="status" class="flex items-start gap-3 rounded-2xl border border-brand-200 bg-brand-50 p-4 text-sm text-brand-900 shadow-sm">
                <x-icon name="check-circle" class="mt-0.5 h-5 w-5 shrink-0 text-brand-600" />
                <p>{{ session('mensaje') }}</p>
            </div>
        </div>
    @endif

    <main id="contenido" class="flex-grow {{ request()->is('/') ? '' : 'pb-24 md:pb-0' }}">
        @yield('content')
    </main>

    <footer class="mt-auto border-t border-ink-100 bg-white">
        <div class="container-page grid gap-10 py-14 md:grid-cols-12">
            <div class="md:col-span-4">
                <div class="flex items-center gap-3">
                    <span class="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-glow">
                        <x-icon name="heart-pulse" class="h-6 w-6" stroke-width="2" />
                    </span>
                    <span class="leading-tight">
                        <span class="block text-lg font-bold tracking-tight text-ink-900">Hampi</span>
                        <span class="block text-[11px] font-medium text-ink-400">Salud comunitaria · Socabaya</span>
                    </span>
                </div>
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-ink-500">
                    Portal ciudadano de la Microred Socabaya. Consulta médicos de turno, disponibilidad de vacunas y el directorio de establecimientos de tu comunidad.
                </p>
                <p class="mt-4 inline-flex items-center gap-2 rounded-full border border-ink-100 bg-ink-50 px-3 py-1.5 text-[11px] font-semibold text-ink-500">
                    <x-icon name="lock" class="h-3.5 w-3.5 text-brand-600" />
                    Datos protegidos por la Ley N.° 29733
                </p>
            </div>

            <div class="md:col-span-2">
                <h2 class="text-xs font-bold uppercase tracking-[0.16em] text-ink-400">Portal</h2>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="/" class="text-ink-600 transition-colors hover:text-brand-700">Inicio</a></li>
                    <li><a href="/medicos-hoy" class="text-ink-600 transition-colors hover:text-brand-700">Médicos del día</a></li>
                    <li><a href="/pizarrin-vacunas" class="text-ink-600 transition-colors hover:text-brand-700">Pizarrín de vacunas</a></li>
                    <li><a href="/directorio-postas" class="text-ink-600 transition-colors hover:text-brand-700">Directorio de postas</a></li>
                </ul>
            </div>

            <div class="md:col-span-3">
                <h2 class="text-xs font-bold uppercase tracking-[0.16em] text-ink-400">Accesos</h2>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="/#acceso" class="text-ink-600 transition-colors hover:text-brand-700">Acceso ciudadano con DNI</a></li>
                    <li><a href="/mi-panel" class="text-ink-600 transition-colors hover:text-brand-700">Mi panel de citas</a></li>
                    <li><a href="/personal/login" class="text-ink-600 transition-colors hover:text-brand-700">Personal de salud</a></li>
                </ul>
            </div>

            <div class="md:col-span-3">
                <h2 class="text-xs font-bold uppercase tracking-[0.16em] text-ink-400">Atención</h2>
                <ul class="mt-4 space-y-2.5 text-sm text-ink-600">
                    <li class="flex items-start gap-2">
                        <x-icon name="building-2" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                        Microred Socabaya, Arequipa
                    </li>
                    <li class="flex items-start gap-2">
                        <x-icon name="clock" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                        Lunes a sábado · 07:30 a 19:30
                    </li>
                    <li class="flex items-start gap-2">
                        <x-icon name="map-pin" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                        <a href="/directorio-postas" class="transition-colors hover:text-brand-700">Ver establecimientos y horarios</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-ink-100">
            <div class="container-page flex flex-col gap-3 py-6 text-xs text-ink-400 sm:flex-row sm:items-center sm:justify-between">
                <p>© {{ date('Y') }} Hampi · Microred Socabaya (Arequipa). Proyecto académico Universidad Continental.</p>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                    <span>Ley N.° 29733 (Protección de Datos)</span>
                    <span aria-hidden="true">•</span>
                    <span>GERESA Arequipa</span>
                </div>
            </div>
        </div>
    </footer>

    {{-- Navegación inferior para móviles (páginas internas) --}}
    @unless(request()->is('/'))
        <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-ink-100 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur-md md:hidden" aria-label="Accesos rápidos">
            <div class="grid grid-cols-4 py-1.5">
                @if(session('ciudadano_dni'))
                    <a href="/mi-panel" class="flex flex-col items-center gap-0.5 rounded-xl py-1.5 text-[11px] font-semibold transition-colors {{ request()->is('mi-panel') ? 'text-brand-700' : 'text-ink-500 hover:text-brand-700' }}">
                        <x-icon name="calendar-check" class="h-5 w-5" />
                        Mis citas
                    </a>
                @else
                    <a href="/" class="flex flex-col items-center gap-0.5 rounded-xl py-1.5 text-[11px] font-semibold transition-colors {{ request()->is('/') ? 'text-brand-700' : 'text-ink-500 hover:text-brand-700' }}">
                        <x-icon name="home" class="h-5 w-5" />
                        Inicio
                    </a>
                @endif
                <a href="/medicos-hoy" class="flex flex-col items-center gap-0.5 rounded-xl py-1.5 text-[11px] font-semibold transition-colors {{ request()->is('medicos-hoy') ? 'text-brand-700' : 'text-ink-500 hover:text-brand-700' }}">
                    <x-icon name="stethoscope" class="h-5 w-5" />
                    Médicos
                </a>
                <a href="/pizarrin-vacunas" class="flex flex-col items-center gap-0.5 rounded-xl py-1.5 text-[11px] font-semibold transition-colors {{ request()->is('pizarrin-vacunas') ? 'text-brand-700' : 'text-ink-500 hover:text-brand-700' }}">
                    <x-icon name="clipboard-list" class="h-5 w-5" />
                    Pizarrín
                </a>
                <a href="/directorio-postas" class="flex flex-col items-center gap-0.5 rounded-xl py-1.5 text-[11px] font-semibold transition-colors {{ request()->is('directorio-postas') ? 'text-brand-700' : 'text-ink-500 hover:text-brand-700' }}">
                    <x-icon name="map-pin" class="h-5 w-5" />
                    Postas
                </a>
            </div>
        </nav>
    @endunless

    @stack('scripts')
</body>
</html>
