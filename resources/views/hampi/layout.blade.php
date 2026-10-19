<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hampi - Tu salud, más cerca (Socabaya, Arequipa)</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        turquesa: {
                            DEFAULT: '#0D9488',
                            hover: '#0F766E',
                            light: '#CCFBF1'
                        },
                        celeste: {
                            DEFAULT: '#0284C7',
                            light: '#E0F2FE'
                        }
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex flex-col antialiased">

    <!-- HEADER / NAVBAR -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm backdrop-blur-md bg-white/95">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between">
            
            <!-- Logo -->
            <a href="/" class="flex items-center space-x-3 group">
                <div class="w-10 h-10 bg-turquesa-light text-turquesa rounded-xl flex items-center justify-center transition-transform group-hover:scale-105 duration-200">
                    <i data-lucide="shield-plus" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-900 tracking-tight leading-tight flex items-center gap-2">
                        Hampi
                        <span class="hidden sm:inline-block text-[11px] font-semibold bg-teal-50 text-turquesa px-2 py-0.5 rounded-full border border-turquesa/20">
                            Socabaya
                        </span>
                    </h1>
                    <p class="text-xs text-slate-500">Arequipa · Perú</p>
                </div>
            </a>

            <!-- NAVEGACIÓN EN COMPUTADORA -->
            <nav class="hidden md:flex items-center space-x-2">
                @if(session('ciudadano_dni'))
                    <!-- Menú Exclusivo para el Ciudadano que ya ingresó su DNI -->
                    <a href="/mi-panel" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5 {{ request()->is('mi-panel') ? 'bg-turquesa text-white' : 'text-slate-600 hover:text-turquesa hover:bg-slate-100' }}">
                        <i data-lucide="calendar-check" class="w-4 h-4"></i>
                        <span>Mis Citas</span>
                    </a>
                @endif

                <!-- Enlaces Públicos siempre disponibles -->
                <a href="/medicos-hoy" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5 {{ request()->is('medicos-hoy') ? 'bg-turquesa text-white' : 'text-slate-600 hover:text-turquesa hover:bg-slate-100' }}">
                    <i data-lucide="stethoscope" class="w-4 h-4"></i>
                    <span>Médicos del Día</span>
                </a>
                <a href="/pizarrin-vacunas" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5 {{ request()->is('pizarrin-vacunas') ? 'bg-turquesa text-white' : 'text-slate-600 hover:text-turquesa hover:bg-slate-100' }}">
                    <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                    <span>Pizarrín de Vacunas</span>
                </a>
                <a href="/directorio-postas" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5 {{ request()->is('directorio-postas') ? 'bg-turquesa text-white' : 'text-slate-600 hover:text-turquesa hover:bg-slate-100' }}">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                    <span>Postas</span>
                </a>
            </nav>

            <!-- Acceso / Cerrar Sesión -->
            <div class="flex items-center space-x-2">
                @if(session('ciudadano_dni'))
                    <a href="/" class="text-xs text-red-600 hover:text-red-700 font-medium flex items-center space-x-1 px-3 py-1.5 rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span>Salir</span>
                    </a>
                @else
                    <a href="/personal/login" class="text-xs text-turquesa font-semibold px-3 py-1.5 rounded-lg border border-turquesa/30 hover:bg-turquesa-light transition-all flex items-center gap-1.5 shadow-sm">
                        <i data-lucide="user-cog" class="w-4 h-4"></i>
                        <span>Personal de Salud</span>
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- CONTENEDOR PRINCIPAL -->
    <main class="flex-grow max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8 pb-24 md:pb-12">
        @if(session('mensaje'))
            <div class="mb-6 p-4 bg-teal-50 border border-teal-200 text-teal-800 rounded-xl text-sm flex items-center shadow-sm">
                <i data-lucide="info" class="w-5 h-5 mr-3 text-teal-600 shrink-0"></i>
                <span>{{ session('mensaje') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- FOOTER EN COMPUTADORA -->
    <footer class="hidden md:block bg-white border-t border-slate-200 py-6 mt-auto text-xs text-slate-500">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p>© 2026 Hampi · Microred Socabaya (Arequipa). Proyecto Académico Universidad Continental.</p>
            <div class="flex items-center space-x-4 text-slate-400">
                <span>Ley N.° 29733 (Protección de Datos)</span>
                <span>•</span>
                <span>GERESA Arequipa</span>
            </div>
        </div>
    </footer>

    <!-- BOTTOM NAVIGATION BAR (Solo si NO está en la pantalla de login) -->
    @if(!request()->is('/'))
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 z-50 shadow-lg">
        <div class="grid {{ session('ciudadano_dni') ? 'grid-cols-4' : 'grid-cols-3' }} py-2">
            @if(session('ciudadano_dni'))
            <a href="/mi-panel" class="flex flex-col items-center justify-center text-[11px] {{ request()->is('mi-panel') ? 'text-turquesa font-bold' : 'text-slate-500 hover:text-turquesa' }}">
                <i data-lucide="calendar-check" class="w-5 h-5 mb-0.5"></i>
                <span>Mis Citas</span>
            </a>
            @endif
            <a href="/medicos-hoy" class="flex flex-col items-center justify-center text-[11px] {{ request()->is('medicos-hoy') ? 'text-turquesa font-bold' : 'text-slate-500 hover:text-turquesa' }}">
                <i data-lucide="stethoscope" class="w-5 h-5 mb-0.5"></i>
                <span>Médicos</span>
            </a>
            <a href="/pizarrin-vacunas" class="flex flex-col items-center justify-center text-[11px] {{ request()->is('pizarrin-vacunas') ? 'text-turquesa font-bold' : 'text-slate-500 hover:text-turquesa' }}">
                <i data-lucide="clipboard-list" class="w-5 h-5 mb-0.5"></i>
                <span>Pizarrín</span>
            </a>
            <a href="/directorio-postas" class="flex flex-col items-center justify-center text-[11px] {{ request()->is('directorio-postas') ? 'text-turquesa font-bold' : 'text-slate-500 hover:text-turquesa' }}">
                <i data-lucide="map-pin" class="w-5 h-5 mb-0.5"></i>
                <span>Postas</span>
            </a>
        </div>
    </nav>
    @endif

    <script>
        lucide.createIcons();
    </script>
</body>
</html>