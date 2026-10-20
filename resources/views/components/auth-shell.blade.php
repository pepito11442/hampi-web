@props([
    'formEyebrow' => 'Área restringida',
    'formTitle' => 'Personal de salud',
    'formLead' => '',
    'bullets' => [],
])

<div class="container-page py-10 sm:py-16">
    <div class="mx-auto grid max-w-5xl overflow-hidden rounded-[2rem] border border-ink-100 bg-white shadow-lift lg:grid-cols-2">
        {{-- Panel institucional --}}
        <div class="relative hidden overflow-hidden bg-ink-950 p-10 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="pointer-events-none absolute inset-0 bg-dot-grid opacity-30" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -left-20 -top-20 h-64 w-64 rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-24 -right-16 h-72 w-72 rounded-full bg-sky-500/20 blur-3xl" aria-hidden="true"></div>

            <div class="relative">
                <a href="/" class="inline-flex items-center gap-3" aria-label="Hampi, volver al inicio">
                    <span class="grid h-11 w-11 place-items-center rounded-2xl bg-white/10 text-white ring-1 ring-white/20">
                        <x-icon name="heart-pulse" class="h-6 w-6" stroke-width="2" />
                    </span>
                    <span class="leading-tight">
                        <span class="block text-lg font-bold">Hampi</span>
                        <span class="block text-[11px] text-white/60">Salud comunitaria · Socabaya</span>
                    </span>
                </a>

                <h1 class="mt-12 text-3xl font-bold leading-tight tracking-tight">
                    {{ $heading ?? '' }}
                </h1>
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-white/70">
                    {{ $lead ?? '' }}
                </p>

                <ul class="mt-8 space-y-4 text-sm">
                    @foreach ($bullets as $bullet)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-brand-500/20 text-brand-300">
                                <x-icon :name="$bullet['icon']" class="h-4 w-4" />
                            </span>
                            <span class="text-white/80">{{ $bullet['text'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="relative mt-10 text-[11px] text-white/40">
                Microred Socabaya · GERESA Arequipa
            </p>
        </div>

        {{-- Formulario --}}
        <div class="p-6 sm:p-10">
            <div class="mx-auto max-w-sm">
                <div class="flex items-center gap-3 lg:hidden">
                    <span class="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-glow">
                        <x-icon name="heart-pulse" class="h-6 w-6" stroke-width="2" />
                    </span>
                    <span class="leading-tight">
                        <span class="block text-base font-bold text-ink-900">Hampi</span>
                        <span class="block text-[11px] text-ink-400">Salud comunitaria · Socabaya</span>
                    </span>
                </div>

                <div class="mt-8 lg:mt-0">
                    <span class="eyebrow">{{ $formEyebrow }}</span>
                    <h2 class="mt-2 text-2xl font-bold tracking-tight text-ink-900">{{ $formTitle }}</h2>
                    <p class="mt-2 text-sm leading-relaxed text-ink-500">{{ $formLead }}</p>
                </div>

                {{ $slot }}

                {{ $footer ?? '' }}

                {{ $note ?? '' }}
            </div>
        </div>
    </div>
</div>
