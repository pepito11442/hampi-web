@props([
    'href' => '/',
    'subtitulo' => 'Microred Socabaya',
    'badge' => false,
])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'group flex min-h-11 items-center gap-3']) }} aria-label="Hampi, volver al inicio">
    <span class="relative grid h-10 w-10 shrink-0 place-items-center border-2 border-ink-950 bg-brand-700 text-paper-50 transition-colors group-hover:bg-brand-800">
        <span class="font-display text-lg font-extrabold leading-none">H</span>
        <span class="absolute -bottom-1 -right-1 h-2.5 w-2.5 border border-ink-950 bg-signal-500" aria-hidden="true"></span>
    </span>
    <span class="leading-tight">
        <span class="flex items-center gap-2">
            <span class="font-display text-lg font-extrabold tracking-[-0.03em] text-ink-950">Hampi</span>
            @if($badge)
                <span class="hidden border border-ink-300 px-1.5 py-0.5 font-mono text-[9px] font-medium uppercase tracking-[0.08em] text-ink-600 sm:inline-block">
                    Socabaya
                </span>
            @endif
        </span>
        <span class="mt-0.5 block font-mono text-[9px] uppercase tracking-[0.08em] text-ink-500">{{ $subtitulo }}</span>
    </span>
</a>
