@extends('hampi.layout')

@section('title', 'Directorio de postas · Hampi')
@section('meta_description', 'Ubica los establecimientos de salud de la Microred Socabaya con su dirección, teléfono y horario de atención.')

@section('content')
    <x-page-board>
        <x-slot:hero>
            <x-page-hero
                eyebrow="Red de establecimientos"
                title="Directorio de postas"
                description="Ubica los establecimientos de la Microred Socabaya y consulta su dirección, teléfono y horario de atención."
                icon="map-pin"
            >
                <x-slot:actions>
                    <span class="inline-flex min-h-10 items-center gap-2 border border-ink-300 bg-paper-50 px-3 font-mono text-[10px] uppercase tracking-[0.05em] text-ink-600">
                        <x-icon name="building-2" class="h-4 w-4 text-brand-700" />
                        {{ $postas->count() }} {{ $postas->count() === 1 ? 'establecimiento activo' : 'establecimientos activos' }}
                    </span>
                    <span class="inline-flex min-h-10 items-center gap-2 border border-ink-300 bg-paper-50 px-3 font-mono text-[10px] uppercase tracking-[0.05em] text-ink-600">
                        <x-icon name="map" class="h-4 w-4 text-brand-700" />
                        Socabaya · Arequipa
                    </span>
                </x-slot:actions>
            </x-page-hero>
        </x-slot:hero>

        <div class="container-page py-8 md:py-12">
            @if ($postas->isEmpty())
                <x-empty-state
                    icon="building-2"
                    title="No hay establecimientos registrados"
                    description="En este momento el directorio no tiene postas activas. Comunícate con la Microred Socabaya para más información."
                    data-reveal
                />
            @else
                <div class="flex flex-wrap items-end justify-between gap-3 border-b-2 border-ink-950 pb-2" data-reveal>
                    <h2 class="font-display text-xl font-bold text-ink-950">Fichas de establecimientos</h2>
                    <p class="font-mono text-[10px] uppercase tracking-[0.06em] text-ink-500">Directorio de la microred</p>
                </div>

                <div class="divide-y divide-ink-200">
                    @foreach ($postas as $posta)
                        @php
                            $mapQuery = urlencode($posta->nombre . ', ' . $posta->direccion . ', Socabaya, Arequipa, Perú');
                            $telefonoDigitos = preg_replace('/\D+/', '', (string) $posta->telefono);
                        @endphp

                        <article class="grid gap-4 py-5 transition-colors hover:bg-paper-50 lg:grid-cols-12 lg:gap-5" data-reveal>
                            <div class="flex items-start justify-between gap-3 lg:col-span-1 lg:block">
                                <span class="font-mono text-[11px] text-ink-500">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="badge badge-slate mt-2 block w-fit">Cat. {{ $posta->categoria }}</span>
                            </div>

                            <div class="lg:col-span-4">
                                <h2 class="font-display text-xl font-bold leading-tight tracking-[-0.025em] text-ink-950">{{ $posta->nombre }}</h2>
                                <p class="mt-1 flex items-center gap-1.5 font-mono text-[10px] uppercase tracking-[0.05em] text-brand-800">
                                    <span class="h-1.5 w-1.5 bg-brand-600" aria-hidden="true"></span>
                                    Establecimiento activo
                                </p>
                            </div>

                            <dl class="grid gap-3 border-t border-ink-200 pt-3 text-sm sm:grid-cols-3 lg:col-span-4 lg:border-t-0 lg:pt-0">
                                <div>
                                    <dt class="mono-label">Dirección</dt>
                                    <dd class="mt-1 leading-relaxed text-ink-700">{{ $posta->direccion }}</dd>
                                </div>
                                <div>
                                    <dt class="mono-label">Horario</dt>
                                    <dd class="mt-1 leading-relaxed text-ink-700">{{ $posta->horario_atencion }}</dd>
                                </div>
                                <div>
                                    <dt class="mono-label">Teléfono</dt>
                                    <dd class="mt-1 leading-relaxed text-ink-700">
                                        @if ($posta->telefono)
                                            <a href="tel:{{ $telefonoDigitos }}" class="transition-colors hover:text-brand-700">{{ $posta->telefono }}</a>
                                        @else
                                            Sin teléfono
                                        @endif
                                    </dd>
                                </div>
                            </dl>

                            <div class="flex flex-wrap items-start gap-2 lg:col-span-3 lg:justify-end">
                                <a
                                    href="https://www.google.com/maps/search/?api=1&query={{ $mapQuery }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn btn-primary btn-sm"
                                >
                                    <x-icon name="navigation" class="h-4 w-4" />
                                    Ver ubicación
                                    <span class="sr-only">de {{ $posta->nombre }} en Google Maps</span>
                                </a>

                                @if ($posta->telefono)
                                    <a href="tel:{{ $telefonoDigitos }}" class="btn btn-secondary btn-sm">
                                        <x-icon name="phone" class="h-4 w-4" />
                                        Llamar
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-8 flex flex-col gap-4 border border-maize-300 bg-maize-50 p-5 sm:flex-row sm:items-center sm:justify-between" data-reveal>
                    <div class="flex items-start gap-3">
                        <span class="icon-seal border-maize-400 bg-maize-100 text-maize-800">
                            <x-icon name="info" class="h-5 w-5" />
                        </span>
                        <div>
                            <h2 class="font-display text-base font-bold text-ink-950">Antes de acudir</h2>
                            <p class="mt-1 text-xs leading-relaxed text-ink-700">
                                Lleva tu DNI físico y, si corresponde, tu carné de vacunación. Los horarios pueden variar en campañas y feriados.
                            </p>
                        </div>
                    </div>
                    <a href="/pizarrin-vacunas" class="btn btn-secondary btn-sm mt-2 shrink-0 sm:mt-0">
                        <x-icon name="syringe" class="h-4 w-4" />
                        Ver vacunas
                    </a>
                </div>
            @endif
        </div>
    </x-page-board>
@endsection
