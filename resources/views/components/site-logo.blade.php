@props([
    'href' => '/',
    'subtitulo' => 'Salud comunitaria · Arequipa',
    'badge' => false,
])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'group flex items-center gap-3']) }} aria-label="Hampi, volver al inicio">
    <span class="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-glow transition-transform duration-300 group-hover:scale-105">
        <x-icon name="heart-pulse" class="h-6 w-6" stroke-width="2" />
    </span>
    <span class="leading-tight">
        <span class="flex items-center gap-2">
            <span class="text-lg font-bold tracking-tight text-ink-900">Hampi</span>
            @if($badge)
                <span class="hidden rounded-full border border-brand-200/70 bg-brand-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-brand-700 sm:inline-block">
                    Socabaya
                </span>
            @endif
        </span>
        <span class="block text-[11px] font-medium text-ink-400">{{ $subtitulo }}</span>
    </span>
</a>
