@extends('hampi.layout')

@section('title', 'Directorio de postas · Hampi')
@section('meta_description', 'Ubica los establecimientos de salud de la Microred Socabaya con su dirección, teléfono y horario de atención.')

@section('content')
    <x-page-hero
        eyebrow="Red de establecimientos"
        title="Directorio de postas"
        description="Ubica los establecimientos de la Microred Socabaya y consulta su dirección, teléfono y horario de atención."
        icon="map-pin"
    >
        <x-slot:actions>
            <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white px-4 py-2 text-xs font-semibold text-ink-600">
                <x-icon name="building-2" class="h-4 w-4 text-brand-600" />
                {{ $postas->count() }} {{ $postas->count() === 1 ? 'establecimiento activo' : 'establecimientos activos' }}
            </span>
            <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white px-4 py-2 text-xs font-semibold text-ink-600">
                <x-icon name="map" class="h-4 w-4 text-brand-600" />
                Red Arequipa-Caylloma
            </span>
        </x-slot:actions>
    </x-page-hero>

    <div class="container-page py-8 md:py-12">
        @if ($postas->isEmpty())
            <x-empty-state
                icon="building-2"
                title="No hay establecimientos registrados"
                description="En este momento el directorio no tiene postas activas. Comunícate con la Microred Socabaya para más información."
                data-reveal
            />
        @else
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($postas as $posta)
                    @php
                        $mapQuery = urlencode($posta->nombre . ', ' . $posta->direccion . ', Socabaya, Arequipa, Perú');
                        $telefonoDigitos = preg_replace('/\D+/', '', (string) $posta->telefono);
                    @endphp

                    <article class="card card-hover flex flex-col p-5" data-reveal>
                        <div class="flex items-start justify-between gap-3">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-700">
                                <x-icon name="building-2" class="h-5 w-5" />
                            </span>
                            <div class="flex flex-col items-end gap-1.5">
                                <span class="badge badge-teal">Categoría {{ $posta->categoria }}</span>
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-brand-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-brand-500" aria-hidden="true"></span>
                                    Activo
                                </span>
                            </div>
                        </div>

                        <h2 class="mt-4 text-base font-bold leading-snug text-ink-900">{{ $posta->nombre }}</h2>

                        <dl class="mt-4 flex-1 space-y-3 text-sm">
                            <div class="flex items-start gap-2.5">
                                <x-icon name="map-pin" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                                <div>
                                    <dt class="sr-only">Dirección</dt>
                                    <dd class="text-xs leading-relaxed text-ink-600">{{ $posta->direccion }}</dd>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <x-icon name="clock" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                                <div>
                                    <dt class="sr-only">Horario de atención</dt>
                                    <dd class="text-xs leading-relaxed text-ink-600">{{ $posta->horario_atencion }}</dd>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <x-icon name="phone" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                                <div>
                                    <dt class="sr-only">Teléfono</dt>
                                    <dd class="text-xs leading-relaxed text-ink-600">
                                        @if ($posta->telefono)
                                            <a href="tel:{{ $telefonoDigitos }}" class="transition-colors hover:text-brand-700">{{ $posta->telefono }}</a>
                                        @else
                                            Sin teléfono registrado
                                        @endif
                                    </dd>
                                </div>
                            </div>
                        </dl>

                        <div class="mt-5 flex flex-wrap items-center gap-2 border-t border-ink-100 pt-4">
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
                                    <x-icon name="phone" class="h-4 w-4 text-brand-600" />
                                    Llamar
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8 rounded-3xl border border-ink-100 bg-white p-5 sm:flex sm:items-center sm:justify-between sm:gap-6" data-reveal>
                <div class="flex items-start gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-amber-50 text-amber-700">
                        <x-icon name="info" class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-ink-900">Antes de acudir</h2>
                        <p class="mt-1 text-xs leading-relaxed text-ink-500">
                            Lleva tu DNI físico y, si corresponde, tu carné de vacunación. Los horarios pueden variar en campañas y feriados.
                        </p>
                    </div>
                </div>
                <a href="/pizarrin-vacunas" class="btn btn-secondary btn-sm mt-4 shrink-0 sm:mt-0">
                    <x-icon name="syringe" class="h-4 w-4 text-brand-600" />
                    Ver vacunas
                </a>
            </div>
        @endif
    </div>
@endsection
