@extends('hampi.layout')

@section('title', 'Campañas preventivas · Hampi')
@section('meta_description', 'Campañas de prevención y control de la Microred Socabaya: presión arterial, diabetes, anemia, salud bucal y planificación familiar.')

@section('content')
    <x-page-hero
        eyebrow="Microred Socabaya"
        title="Campañas preventivas"
        description="Actividades gratuitas de prevención y control organizadas por los establecimientos de la Microred Socabaya. Acércate con tu DNI y participa."
        icon="shield-check"
    >
        <x-slot:actions>
            <a href="/pizarrin-vacunas" class="btn btn-secondary btn-sm">
                <x-icon name="syringe" class="h-4 w-4 text-brand-600" />
                Campañas de vacunación
            </a>
            <a href="/directorio-postas" class="btn btn-secondary btn-sm">
                <x-icon name="map-pin" class="h-4 w-4 text-brand-600" />
                Ver postas y horarios
            </a>
        </x-slot:actions>
    </x-page-hero>

    <div class="container-page py-10 md:py-14">
        @if ($campanas->isEmpty())
            <x-empty-state
                icon="shield-check"
                title="No hay campañas preventivas publicadas"
                description="El personal de salud aún no registró campañas activas. Vuelve a consultar más tarde o revisa el pizarrín de vacunación."
            >
                <x-slot:action>
                    <a href="/pizarrin-vacunas" class="btn btn-secondary">Ver campañas de vacunación</a>
                </x-slot:action>
            </x-empty-state>
        @else
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($campanas as $campana)
                    @php
                        $color = match ($campana->estado) {
                            'En curso' => 'teal',
                            'Próxima' => 'sky',
                            default => 'slate',
                        };
                    @endphp

                    <article class="card card-hover flex flex-col p-6" data-reveal style="--reveal-delay: {{ $loop->index * 60 }}ms">
                        <div class="flex items-start justify-between gap-3">
                            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-brand-50 text-brand-700">
                                <x-icon name="shield-check" class="h-6 w-6" />
                            </span>
                            <x-badge :color="$color">{{ $campana->estado }}</x-badge>
                        </div>

                        <h2 class="mt-5 text-lg font-bold leading-snug tracking-tight text-ink-900">
                            {{ $campana->titulo }}
                        </h2>

                        <p class="mt-2 flex-1 text-sm leading-relaxed text-ink-500">
                            {{ $campana->descripcion }}
                        </p>

                        <dl class="mt-5 space-y-2.5 border-t border-ink-100 pt-4 text-xs">
                            @if ($campana->grupo_objetivo)
                                <div class="flex items-start gap-2">
                                    <x-icon name="users" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                                    <div>
                                        <dt class="font-bold text-ink-700">Dirigido a</dt>
                                        <dd class="text-ink-500">{{ $campana->grupo_objetivo }}</dd>
                                    </div>
                                </div>
                            @endif
                            @if ($campana->lugar)
                                <div class="flex items-start gap-2">
                                    <x-icon name="map-pin" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                                    <div>
                                        <dt class="font-bold text-ink-700">Lugar</dt>
                                        <dd class="text-ink-500">{{ $campana->lugar }}</dd>
                                    </div>
                                </div>
                            @endif
                            <div class="flex items-start gap-2">
                                <x-icon name="calendar" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                                <div>
                                    <dt class="font-bold text-ink-700">Fechas</dt>
                                    <dd class="text-ink-500">{{ $campana->periodo }}</dd>
                                </div>
                            </div>
                        </dl>
                    </article>
                @endforeach
            </div>
        @endif

        <section class="card mt-8 overflow-hidden" data-reveal>
            <div class="grid gap-8 p-6 md:grid-cols-3 md:p-8">
                <div class="md:col-span-1">
                    <span class="eyebrow">Participa</span>
                    <h2 class="mt-2 text-2xl font-bold tracking-tight text-ink-900">¿Cómo accedo a una campaña?</h2>
                    <p class="mt-2 text-sm leading-relaxed text-ink-500">
                        Las campañas son gratuitas y no requieren inscripción previa. Solo acércate al establecimiento en las fechas indicadas.
                    </p>
                </div>

                <ol class="grid gap-4 sm:grid-cols-3 md:col-span-2">
                    @foreach ([
                        ['icon' => 'id-card', 'title' => 'Lleva tu DNI', 'text' => 'DNI físico del paciente. Para menores, DNI o carné de vacunación.'],
                        ['icon' => 'clock', 'title' => 'Respeta el horario', 'text' => 'Las campañas se atienden de lunes a sábado, según la fecha publicada.'],
                        ['icon' => 'heart-pulse', 'title' => 'Consulta sin costo', 'text' => 'Control, orientación y derivación según el resultado de la evaluación.'],
                    ] as $paso)
                        <li class="rounded-2xl border border-ink-100 bg-ink-50/50 p-4">
                            <span class="grid h-10 w-10 place-items-center rounded-xl bg-white text-brand-700 shadow-sm">
                                <x-icon :name="$paso['icon']" class="h-5 w-5" />
                            </span>
                            <h3 class="mt-3 text-sm font-bold text-ink-900">{{ $paso['title'] }}</h3>
                            <p class="mt-1 text-xs leading-relaxed text-ink-500">{{ $paso['text'] }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    </div>
@endsection
