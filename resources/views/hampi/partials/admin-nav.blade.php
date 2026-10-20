@php
    $enlaces = [
        ['ruta' => 'admin.dashboard', 'etiqueta' => 'Panel', 'icono' => 'settings'],
        ['ruta' => 'admin.equipo.index', 'etiqueta' => 'Equipo de salud', 'icono' => 'users'],
        ['ruta' => 'admin.horarios.index', 'etiqueta' => 'Horarios', 'icono' => 'calendar'],
        ['ruta' => 'admin.campanas.index', 'etiqueta' => 'Campañas preventivas', 'icono' => 'shield-check'],
    ];
@endphp

<div class="container-page relative z-10 pt-6">
    <nav class="flex gap-1.5 overflow-x-auto rounded-2xl border border-ink-100 bg-white/85 p-1.5 shadow-card backdrop-blur" aria-label="Secciones de administración">
        @foreach ($enlaces as $enlace)
            <a
                href="{{ route($enlace['ruta']) }}"
                class="inline-flex shrink-0 items-center gap-2 rounded-xl px-3.5 py-2 text-xs font-bold transition-colors {{ request()->routeIs($enlace['ruta'].'*') ? 'bg-brand-600 text-white shadow-sm' : 'text-ink-600 hover:bg-brand-50 hover:text-brand-800' }}"
                @if (request()->routeIs($enlace['ruta'].'*')) aria-current="page" @endif
            >
                <x-icon :name="$enlace['icono']" class="h-4 w-4" />
                {{ $enlace['etiqueta'] }}
            </a>
        @endforeach
    </nav>
</div>
