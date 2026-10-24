<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f4f2ea">

    <title>@yield('title', 'Hampi · Microred Socabaya')</title>
    <meta name="description" content="@yield('meta_description', 'Hampi conecta a los vecinos de Socabaya con su microred de salud: médicos de turno, vacunas, campañas y directorio de postas.')">

    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <script>
        document.documentElement.classList.add('js');
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-paper-100 text-ink-800">
    <a href="#contenido" class="skip-link">Saltar al contenido principal</a>

    {{-- Un solo fondo para todo el portal: la hoja de la posta. --}}
    <div class="bg-ledger pointer-events-none fixed inset-0 z-0" aria-hidden="true"></div>

    @php
        $enPantallaDeAcceso = request()->routeIs('login') || request()->routeIs('empleado.login');
    @endphp

    <header data-site-header class="site-header sticky top-0 z-40 border-b border-ink-300 bg-paper-50">
        <div class="hidden border-b border-ink-800 bg-ink-950 text-paper-100 lg:block">
            <div class="container-page flex h-9 items-center justify-between gap-6 font-mono text-[10px] uppercase tracking-[0.08em]">
                <p class="flex items-center gap-2 text-paper-100/85">
                    <x-icon name="map-pin" class="h-3.5 w-3.5 text-signal-400" />
                    Microred Socabaya · Arequipa, Perú
                </p>
                <div class="flex items-center gap-6">
                    <span class="flex items-center gap-1.5 text-paper-100/70">
                        <x-icon name="clock" class="h-3.5 w-3.5 text-signal-400" />
                        Lun a vie 07:00–19:30 · Sáb 07:00–15:00
                    </span>
                    @auth
                        @if(auth()->user()->esAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="flex min-h-9 items-center gap-1.5 font-semibold text-paper-50 transition-colors hover:text-signal-300">
                                <x-icon name="settings" class="h-3.5 w-3.5" />
                                Panel administrativo
                            </a>
                        @else
                            <a href="{{ route('empleado.panel') }}" class="flex min-h-9 items-center gap-1.5 font-semibold text-paper-50 transition-colors hover:text-signal-300">
                                <x-icon name="user-check" class="h-3.5 w-3.5" />
                                Mi panel de trabajo
                            </a>
                        @endif
                    @else
                        <a href="{{ route('empleado.login') }}" class="flex min-h-9 items-center gap-1.5 font-semibold text-paper-50 transition-colors hover:text-signal-300">
                            <x-icon name="user-cog" class="h-3.5 w-3.5" />
                            Personal de salud
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        <div class="container-page flex h-16 items-center justify-between gap-3">
            <x-site-logo :badge="true" />

            <nav class="hidden items-stretch self-stretch lg:flex" aria-label="Navegación principal">
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
                @auth
                    @if(auth()->user()->esAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm" aria-label="Panel administrativo">
                            <x-icon name="settings" class="h-4 w-4" />
                            <span class="hidden sm:inline">Panel administrativo</span>
                            <span class="sm:hidden">Panel</span>
                        </a>
                    @else
                        <a href="{{ route('empleado.panel') }}" class="btn btn-secondary btn-sm" aria-label="Mi panel de trabajo">
                            <x-icon name="user-check" class="h-4 w-4" />
                            <span class="hidden sm:inline">Mi panel de trabajo</span>
                            <span class="sm:hidden">Mi panel</span>
                        </a>
                    @endif
                @elseif(session('ciudadano_dni'))
                    <a href="/mi-panel" class="btn btn-primary btn-sm" aria-label="Mi panel de citas">
                        <x-icon name="calendar-check" class="h-4 w-4" />
                        <span class="hidden sm:inline">Mis citas</span>
                        <span class="sm:hidden">Panel</span>
                    </a>
                    <a href="/cerrar-sesion-ciudadano" class="btn btn-ghost btn-sm hidden lg:inline-flex">
                        <x-icon name="log-out" class="h-4 w-4" />
                        Salir
                    </a>
                @elseif ($enPantallaDeAcceso)
                    <a href="/" class="btn btn-secondary btn-sm" aria-label="Portal ciudadano">
                        <x-icon name="arrow-left" class="h-4 w-4" />
                        <span class="hidden sm:inline">Portal ciudadano</span>
                        <span class="sm:hidden">Portal</span>
                    </a>
                @else
                    <a href="/#acceso" class="btn btn-primary btn-sm" aria-label="Acceso con DNI">
                        <x-icon name="id-card" class="h-4 w-4" />
                        <span class="hidden sm:inline">Acceso con DNI</span>
                        <span class="sm:hidden">DNI</span>
                    </a>
                @endif

                <button
                    type="button"
                    data-menu-toggle
                    aria-controls="mobile-menu"
                    aria-expanded="false"
                    class="inline-flex h-11 w-11 items-center justify-center border border-ink-300 bg-paper-50 text-ink-700 transition-colors hover:border-ink-500 hover:bg-paper-200 lg:hidden"
                >
                    <x-icon name="menu" class="h-5 w-5" data-menu-icon-open />
                    <x-icon name="x" class="hidden h-5 w-5" data-menu-icon-close />
                    <span class="sr-only">Abrir menú de navegación</span>
                </button>
            </div>
        </div>

        <div id="mobile-menu" hidden class="border-t border-ink-300 bg-paper-50 lg:hidden">
            <nav class="container-page flex flex-col py-3" aria-label="Navegación móvil">
                @foreach (config('navegacion') as $enlace)
                    <a href="{{ $enlace['href'] }}" class="nav-link border-b border-ink-200 {{ request()->is($enlace['patron']) ? 'nav-link-active' : '' }}" @if (request()->is($enlace['patron'])) aria-current="page" @endif>
                        {{ $enlace['etiqueta'] }}
                    </a>
                @endforeach
                @if(session('ciudadano_dni'))
                    <a href="/mi-panel" class="nav-link border-b border-ink-200 {{ request()->is('mi-panel') ? 'nav-link-active' : '' }}">Mi panel de citas</a>
                    <a href="/cerrar-sesion-ciudadano" class="nav-link border-b border-ink-200 text-stamp-700">Cerrar sesión ciudadana</a>
                @endif

                @auth
                    @if(auth()->user()->esAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="nav-link border-b border-ink-200">Panel administrativo</a>
                    @else
                        <a href="{{ route('empleado.panel') }}" class="nav-link border-b border-ink-200">Mi panel de trabajo</a>
                    @endif
                    <form action="{{ auth()->user()->esAdmin() ? route('logout') : route('empleado.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nav-link w-full border-b border-ink-200 text-left text-stamp-700">Cerrar sesión del personal</button>
                    </form>
                @else
                    <a href="{{ route('empleado.login') }}" class="nav-link">Acceso del personal (DNI)</a>
                @endauth
            </nav>
        </div>
    </header>

    @if(session('mensaje'))
        <div class="relative z-10 container-page pt-5">
            <div role="status" class="flex items-start gap-3 border border-brand-300 bg-brand-50 p-4 text-sm text-brand-900">
                <x-icon name="check-circle" class="mt-0.5 h-5 w-5 shrink-0 text-brand-700" />
                <p>{{ session('mensaje') }}</p>
            </div>
        </div>
    @endif

    @php
        $esPanel = request()->is('admin/*') || request()->is('personal/*') || request()->is('empleado/*');
    @endphp

    <main id="contenido" tabindex="-1" class="relative z-10 flex-grow scroll-mt-24 outline-none {{ $esPanel ? '' : 'pb-24 lg:pb-0' }}">
        @yield('content')
    </main>

    <footer class="relative z-10 mt-auto border-t-2 border-ink-950 bg-paper-50 {{ $esPanel ? '' : 'pb-20 lg:pb-0' }}">
        <div class="container-page grid gap-10 py-12 md:grid-cols-12 md:py-14">
            <div class="md:col-span-4">
                <x-site-logo subtitulo="Microred Socabaya · Arequipa" />
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-ink-600">
                    El tablón digital de la microred: médicos de turno, disponibilidad de vacunas, campañas preventivas y establecimientos de salud.
                </p>

            </div>

            <div class="md:col-span-2">
                <h2 class="mono-label text-ink-500">Portal</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach (config('navegacion') as $enlace)
                        <li><a href="{{ $enlace['href'] }}" class="inline-flex min-h-8 items-center text-ink-700 transition-colors hover:text-brand-700">{{ $enlace['etiqueta'] }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="md:col-span-3">
                <h2 class="mono-label text-ink-500">Accesos</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="/#acceso" class="inline-flex min-h-8 items-center text-ink-700 transition-colors hover:text-brand-700">Acceso ciudadano con DNI</a></li>
                    <li><a href="/mi-panel" class="inline-flex min-h-8 items-center text-ink-700 transition-colors hover:text-brand-700">Mi panel de citas</a></li>
                    <li><a href="{{ route('empleado.login') }}" class="inline-flex min-h-8 items-center text-ink-700 transition-colors hover:text-brand-700">Personal de salud (DNI)</a></li>
                    <li><a href="{{ route('login') }}" class="inline-flex min-h-8 items-center text-ink-700 transition-colors hover:text-brand-700">Administración</a></li>
                </ul>
            </div>

            <div class="md:col-span-3">
                <h2 class="mono-label text-ink-500">Atención</h2>
                <ul class="mt-4 space-y-3 text-sm text-ink-700">
                    <li class="flex items-start gap-2">
                        <x-icon name="building-2" class="mt-0.5 h-4 w-4 shrink-0 text-brand-700" />
                        Posta Socabaya, Arequipa
                    </li>
                    <li class="flex items-start gap-2">
                        <x-icon name="clock" class="mt-0.5 h-4 w-4 shrink-0 text-brand-700" />
                        Lun a vie 07:00–19:30 · Sáb 07:00–15:00
                    </li>
                    <li class="flex items-start gap-2">
                        <x-icon name="map-pin" class="mt-0.5 h-4 w-4 shrink-0 text-brand-700" />
                        <a href="/directorio-postas" class="transition-colors hover:text-brand-700">Ver establecimientos y horarios</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-ink-300">
            <div class="container-page flex flex-col gap-2 py-5 font-mono text-[10px] uppercase tracking-[0.06em] text-ink-500 sm:flex-row sm:items-center sm:justify-between">
                <p>© {{ date('Y') }} Hampi · Microred Socabaya · Proyecto académico</p>
                <p>Elaborado por Ore, Valdivia y Rodríguez · con apoyo de OpenCode y DeepSeek</p>
            </div>
        </div>
    </footer>

    @unless($esPanel)
        <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-ink-300 bg-paper-50 pb-[env(safe-area-inset-bottom)] lg:hidden" aria-label="Accesos rápidos">
            <div class="grid grid-cols-5">
                @if(session('ciudadano_dni'))
                    <a href="/mi-panel" class="flex min-h-14 flex-col items-center justify-center gap-1 border-t-2 px-1 py-2 text-[10px] font-semibold {{ request()->is('mi-panel') ? 'border-signal-600 text-ink-950' : 'border-transparent text-ink-500 hover:text-ink-900' }}" @if(request()->is('mi-panel')) aria-current="page" @endif>
                        <x-icon name="calendar-check" class="h-5 w-5" />
                        Mis citas
                    </a>
                    @foreach (array_slice(config('navegacion'), 1) as $enlace)
                        <a href="{{ $enlace['href'] }}" class="flex min-h-14 flex-col items-center justify-center gap-1 border-t-2 px-1 py-2 text-[10px] font-semibold {{ request()->is($enlace['patron']) ? 'border-signal-600 text-ink-950' : 'border-transparent text-ink-500 hover:text-ink-900' }}" @if(request()->is($enlace['patron'])) aria-current="page" @endif>
                            <x-icon :name="$enlace['icono']" class="h-5 w-5" />
                            {{ $enlace['etiqueta_corta'] }}
                        </a>
                    @endforeach
                @else
                    @foreach (config('navegacion') as $enlace)
                        <a href="{{ $enlace['href'] }}" class="flex min-h-14 flex-col items-center justify-center gap-1 border-t-2 px-1 py-2 text-[10px] font-semibold {{ request()->is($enlace['patron']) ? 'border-signal-600 text-ink-950' : 'border-transparent text-ink-500 hover:text-ink-900' }}" @if(request()->is($enlace['patron'])) aria-current="page" @endif>
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
