@php
    $enlaces = [
        ['ruta' => 'admin.dashboard', 'etiqueta' => 'Panel', 'icono' => 'settings'],
        ['ruta' => 'admin.equipo.index', 'etiqueta' => 'Equipo de salud', 'icono' => 'users'],
        ['ruta' => 'admin.horarios.index', 'etiqueta' => 'Horarios', 'icono' => 'calendar'],
        ['ruta' => 'admin.campanas.index', 'etiqueta' => 'Campañas preventivas', 'etiqueta_corta' => 'Campañas', 'icono' => 'shield-check'],
    ];
@endphp

<div class="container-page relative z-10">
    <nav class="grid grid-cols-2 border-b border-ink-300 sm:flex sm:gap-1" aria-label="Secciones de administración">
        @foreach ($enlaces as $enlace)
            <a
                href="{{ route($enlace['ruta']) }}"
                class="inline-flex min-h-11 items-center gap-2 border-b-2 px-3.5 text-xs font-semibold transition-colors sm:shrink-0 {{ request()->routeIs($enlace['ruta'].'*') ? 'border-signal-600 bg-paper-50 text-ink-950' : 'border-transparent text-ink-600 hover:bg-paper-200 hover:text-ink-950' }}"
                aria-label="{{ $enlace['etiqueta'] }}"
                @if (request()->routeIs($enlace['ruta'].'*')) aria-current="page" @endif
            >
                <x-icon :name="$enlace['icono']" class="h-4 w-4" />
                <span class="sm:hidden">{{ $enlace['etiqueta_corta'] ?? $enlace['etiqueta'] }}</span>
                <span class="hidden sm:inline">{{ $enlace['etiqueta'] }}</span>
            </a>
        @endforeach
    </nav>
</div>
