<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#e0f2fe">

    <title>@yield('title', 'Hampi · Tu salud, más cerca | Socabaya, Arequipa')</title>
    <meta name="description" content="@yield('meta_description', 'Hampi conecta a los vecinos de Socabaya con los servicios de su microred de salud: médicos de turno, campañas y directorio de postas.')">

    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <script>
        document.documentElement.classList.add('js');
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-mist-50 text-ink-800">
    <a href="#contenido" class="skip-link">Saltar al contenido principal</a>

    {{-- Fondo decorativo dinámico (celeste) --}}
    <div class="pointer-events-none fixed inset-0 z-0 overflow-hidden" aria-hidden="true">
        <div class="absolute inset-0 bg-aurora"></div>
        <div class="absolute inset-0 bg-dot-grid opacity-30 mask-fade-b"></div>
        <div class="absolute -left-24 top-24 h-80 w-80 rounded-full bg-brand-200/40 blur-3xl animate-float-soft"></div>
    </div>

    <header data-site-header class="site-header sticky top-0 z-40 border-b border-ink-100/80 bg-white/85 backdrop-blur-md">
        {{-- Franja institucional (escritorio) --}}
        <div class="hidden border-b border-ink-100 bg-ink-950 text-white lg:block">
            <div class="container-page flex h-9 items-center justify-between text-[11px] font-medium tracking-wide">
                <p class="flex items-center gap-2 text-white/85">
                    <x-icon name="map-pin" class="h-3.5 w-3.5 text-brand-300" />
                    Posta Socabaya · Arequipa, Perú
                </p>
                <div class="flex items-center gap-6">
                    <span class="flex items-center gap-1.5 text-white/75">
                        <x-icon name="clock" class="h-3.5 w-3.5 text-brand-300" />
                        Lun a vie 07:00–19:30 · Sáb 07:00–15:00
                    </span>
                    @auth
                        @if(auth()->user()->esAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-1.5 font-semibold text-white transition-colors hover:text-brand-200">
                                <x-icon name="settings" class="h-3.5 w-3.5" />
                                Panel administrativo
                            </a>
                        @else
                            <a href="{{ route('empleado.panel') }}" class="flex items-center gap-1.5 font-semibold text-white transition-colors hover:text-brand-200">
                                <x-icon name="user-check" class="h-3.5 w-3.5" />
                                Mi panel de trabajo
                            </a>
                        @endif
                    @else
                        <a href="{{ route('empleado.login') }}" class="flex items-center gap-1.5 font-semibold text-white transition-colors hover:text-brand-200">
                            <x-icon name="user-cog" class="h-3.5 w-3.5" />
                            Personal de salud
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        {{-- Barra principal --}}
        <div class="container-page flex h-16 items-center justify-between gap-3">
            <x-site-logo :badge="true" />

            <nav class="hidden items-center gap-1 lg:flex" aria-label="Navegación principal">
                @foreach (config('navegacion') as $enlace)
                    <a href="{{ $enlace['href'] }}"
                       class="nav-link {{ request()->is($enlace['patron']) ? 'nav-link-active' : '' }}"
                       @if (request()->is($enlace['patron'])) aria-current="page" @endif>
                        {{ $enlace['etiqueta'] }}
                    </a>
                @endforeach
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
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-ink-200 text-ink-700 transition-colors hover:border-brand-300 hover:text-brand-700 lg:hidden"
                >
                    <x-icon name="menu" class="h-5 w-5" data-menu-icon-open />
                    <x-icon name="x" class="hidden h-5 w-5" data-menu-icon-close />
                    <span class="sr-only">Abrir menú de navegación</span>
                </button>
            </div>
        </div>

        {{-- Menú móvil --}}
        <div id="mobile-menu" hidden class="border-t border-ink-100 bg-white lg:hidden">
            <nav class="container-page flex flex-col gap-1 py-4" aria-label="Navegación móvil">
                @foreach (config('navegacion') as $enlace)
                    <a href="{{ $enlace['href'] }}" class="nav-link {{ request()->is($enlace['patron']) ? 'nav-link-active' : '' }}">
                        {{ $enlace['etiqueta'] }}
                    </a>
                @endforeach
                @if(session('ciudadano_dni'))
                    <a href="/mi-panel" class="nav-link {{ request()->is('mi-panel') ? 'nav-link-active' : '' }}">Mi panel de citas</a>
                    <a href="/cerrar-sesion-ciudadano" class="nav-link text-rose-600 hover:bg-rose-50 hover:text-rose-700">Cerrar sesión ciudadana</a>
                @endif

                @auth
                    @if(auth()->user()->esAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="nav-link">Panel administrativo</a>
                    @else
                        <a href="{{ route('empleado.panel') }}" class="nav-link">Mi panel de trabajo</a>
                    @endif
                    <form action="{{ auth()->user()->esAdmin() ? route('logout') : route('empleado.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nav-link w-full text-left text-rose-600 hover:bg-rose-50 hover:text-rose-700">Cerrar sesión del personal</button>
                    </form>
                @else
                    <a href="{{ route('empleado.login') }}" class="nav-link">Acceso del personal (DNI)</a>
                @endauth
            </nav>
        </div>
    </header>

    @if(session('mensaje'))
        <div class="relative z-10 container-page pt-6">
            <div role="status" class="flex items-start gap-3 rounded-2xl border border-brand-200 bg-brand-50 p-4 text-sm text-brand-900 shadow-sm">
                <x-icon name="check-circle" class="mt-0.5 h-5 w-5 shrink-0 text-brand-600" />
                <p>{{ session('mensaje') }}</p>
            </div>
        </div>
    @endif

    @php
        $esPanel = request()->is('admin/*') || request()->is('personal/*') || request()->is('empleado/*');
    @endphp

    <main id="contenido" class="relative z-10 flex-grow {{ $esPanel ? '' : 'pb-24 lg:pb-0' }}">
        @yield('content')
    </main>

    <footer class="relative z-10 mt-auto border-t border-ink-100/80 bg-white/90 {{ $esPanel ? '' : 'pb-20 lg:pb-0' }}">
        <div class="container-page grid gap-10 py-14 md:grid-cols-12">
            <div class="md:col-span-4">
                <x-site-logo subtitulo="Salud comunitaria · Socabaya" />
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-ink-500">
                    Portal ciudadano de la Posta Socabaya. Consulta médicos de turno, disponibilidad de vacunas y campañas preventivas de tu comunidad.
                </p>
            </div>

            <div class="md:col-span-2">
                <h2 class="text-xs font-bold uppercase tracking-[0.16em] text-ink-400">Portal</h2>
                <ul class="mt-4 space-y-2.5 text-sm">
                    @foreach (config('navegacion') as $enlace)
                        <li><a href="{{ $enlace['href'] }}" class="text-ink-600 transition-colors hover:text-brand-700">{{ $enlace['etiqueta'] }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="md:col-span-3">
                <h2 class="text-xs font-bold uppercase tracking-[0.16em] text-ink-400">Accesos</h2>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="/#acceso" class="text-ink-600 transition-colors hover:text-brand-700">Acceso ciudadano con DNI</a></li>
                    <li><a href="/mi-panel" class="text-ink-600 transition-colors hover:text-brand-700">Mi panel de citas</a></li>
                    <li><a href="{{ route('empleado.login') }}" class="text-ink-600 transition-colors hover:text-brand-700">Personal de salud (DNI)</a></li>
                    <li><a href="{{ route('login') }}" class="text-ink-600 transition-colors hover:text-brand-700">Administración</a></li>
                </ul>
            </div>

            <div class="md:col-span-3">
                <h2 class="text-xs font-bold uppercase tracking-[0.16em] text-ink-400">Atención</h2>
                <ul class="mt-4 space-y-2.5 text-sm text-ink-600">
                    <li class="flex items-start gap-2">
                        <x-icon name="building-2" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                        Posta Socabaya, Arequipa
                    </li>
                    <li class="flex items-start gap-2">
                        <x-icon name="clock" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                        Lun a vie 07:00–19:30 · Sáb 07:00–15:00
                    </li>
                    <li class="flex items-start gap-2">
                        <x-icon name="map-pin" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                        <a href="/directorio-postas" class="transition-colors hover:text-brand-700">Ver establecimientos y horarios</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-ink-100/80">
            <div class="container-page flex flex-col gap-3 py-6 text-xs text-ink-400 sm:flex-row sm:items-center sm:justify-between">
                <p>© {{ date('Y') }} Hampi · Socabaya Arequipa. Proyecto académico Universidad Continental.</p>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                    <span>GERESA Arequipa</span>
                </div>
            </div>
        </div>
    </footer>

    {{-- Navegación inferior para móviles y tablets (solo portal público) --}}
    @unless($esPanel)
        <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-ink-100 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur-md lg:hidden" aria-label="Accesos rápidos">
            <div class="grid grid-cols-5 py-1.5">
                @if(session('ciudadano_dni'))
                    <a href="/mi-panel" class="flex flex-col items-center gap-0.5 rounded-xl py-1.5 text-[10px] font-semibold transition-colors {{ request()->is('mi-panel') ? 'text-brand-700' : 'text-ink-500 hover:text-brand-700' }}">
                        <x-icon name="calendar-check" class="h-5 w-5" />
                        Mis citas
                    </a>
                    @foreach (array_slice(config('navegacion'), 1) as $enlace)
                        <a href="{{ $enlace['href'] }}" class="flex flex-col items-center gap-0.5 rounded-xl py-1.5 text-[10px] font-semibold transition-colors {{ request()->is($enlace['patron']) ? 'text-brand-700' : 'text-ink-500 hover:text-brand-700' }}">
                            <x-icon :name="$enlace['icono']" class="h-5 w-5" />
                            {{ $enlace['etiqueta_corta'] }}
                        </a>
                    @endforeach
                @else
                    @foreach (config('navegacion') as $enlace)
                        <a href="{{ $enlace['href'] }}" class="flex flex-col items-center gap-0.5 rounded-xl py-1.5 text-[10px] font-semibold transition-colors {{ request()->is($enlace['patron']) ? 'text-brand-700' : 'text-ink-500 hover:text-brand-700' }}">
                            <x-icon :name="$enlace['icono']" class="h-5 w-5" />
                            {{ $enlace['etiqueta_corta'] }}
                        </a>
                    @endforeach
                @endif
            </div>
        </nav>
    @endunless

    @stack('scripts')
</body>
</html>
