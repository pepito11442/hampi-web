@props([
    'formEyebrow' => 'Área restringida',
    'formTitle' => 'Personal de salud',
    'formLead' => '',
    'bullets' => [],
])

<div class="container-page py-8 sm:py-12">
    <div class="mx-auto grid max-w-5xl border border-ink-300 bg-paper-50 shadow-lift lg:grid-cols-2">
        <div class="relative hidden overflow-hidden bg-brand-900 p-9 text-paper-50 lg:flex lg:flex-col lg:justify-between">
            <div class="pointer-events-none absolute inset-0 bg-dot-grid opacity-30" aria-hidden="true"></div>

            <div class="relative">
                <a href="/" class="inline-flex min-h-11 items-center gap-3" aria-label="Hampi, volver al inicio">
                    <span class="relative grid h-10 w-10 place-items-center border-2 border-paper-50 bg-brand-700 text-paper-50">
                        <span class="font-display text-lg font-extrabold leading-none">H</span>
                    </span>
                    <span class="leading-tight">
                        <span class="block font-display text-lg font-extrabold">Hampi</span>
                        <span class="block font-mono text-[9px] uppercase tracking-[0.08em] text-paper-100/65">Microred Socabaya</span>
                    </span>
                </a>

                <p class="mt-12 text-balance text-3xl font-bold leading-[1.06] tracking-[-0.035em]">
                    {{ $heading ?? '' }}
                </p>
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-paper-100/75">
                    {{ $lead ?? '' }}
                </p>

                <ul class="mt-8 space-y-4 text-sm">
                    @foreach ($bullets as $bullet)
                        <li class="flex items-start gap-3 border-t border-paper-50/15 pt-3">
                            <span class="mt-0.5 grid h-8 w-8 shrink-0 place-items-center border border-paper-50/25 text-signal-300">
                                <x-icon :name="$bullet['icon']" class="h-4 w-4" />
                            </span>
                            <span class="text-paper-100/85">{{ $bullet['text'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="relative mt-10 flex items-end justify-between gap-4 border-t border-paper-50/20 pt-4">
                <p class="font-mono text-[9px] uppercase tracking-[0.08em] text-paper-100/55">
                    Socabaya · Arequipa
                </p>
                <span class="border-2 border-signal-400 px-2 py-1 font-mono text-[9px] uppercase tracking-[0.1em] text-signal-300">Demo</span>
            </div>
        </div>

        <div class="p-6 sm:p-9">
            <div class="mx-auto max-w-sm">
                <div class="border-b border-ink-300 pb-3">
                    <span class="mono-label text-ink-600">{{ $formEyebrow }}</span>
                    <h1 class="mt-2 font-display text-2xl font-bold tracking-[-0.03em] text-ink-950">{{ $formTitle }}</h1>
                    <p class="mt-2 text-sm leading-relaxed text-ink-600">{{ $formLead }}</p>
                </div>

                {{ $slot }}

                {{ $footer ?? '' }}

                {{ $note ?? '' }}

                <p class="mt-8 border-t border-ink-200 pt-3 font-mono text-[10px] uppercase leading-relaxed tracking-[0.06em] text-ink-500">
                    Demo académica · Datos simulados · Credenciales de prueba en el README.
                </p>
            </div>
        </div>
    </div>
</div>
