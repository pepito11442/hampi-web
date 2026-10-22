@extends('hampi.layout')

@section('title', 'Campañas preventivas · Hampi')
@section('meta_description', 'Campañas de prevención y control de la Microred Socabaya: presión arterial, diabetes, anemia, salud bucal y planificación familiar.')

@section('content')
    @php
        $destacada = $campanas->first(fn ($campana) => $campana->estado === 'En curso')
            ?? $campanas->first(fn ($campana) => $campana->estado === 'Próxima')
            ?? $campanas->first();
        $resto = $destacada ? $campanas->reject(fn ($campana) => $campana->is($destacada)) : $campanas;
        $estadoColor = [
            'En curso' => 'badge-teal',
            'Próxima' => 'badge-sky',
            'Finalizada' => 'badge-slate',
        ];
    @endphp

    <x-page-hero
        eyebrow="Campañas de la microred"
        title="Campañas preventivas"
        description="Actividades de prevención y control organizadas por los establecimientos de la Microred Socabaya. Acércate con tu DNI y participa."
        icon="shield-check"
    >
        <x-slot:actions>
            <a href="/pizarrin-vacunas" class="btn btn-secondary btn-sm">
                <x-icon name="syringe" class="h-4 w-4" />
                Vacunación
            </a>
            <a href="/directorio-postas" class="btn btn-secondary btn-sm">
                <x-icon name="map-pin" class="h-4 w-4" />
                Postas y horarios
            </a>
        </x-slot:actions>
    </x-page-hero>

    <div class="container-page py-8 md:py-12">
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
            @if ($destacada)
                <article class="grid border-2 border-ink-950 bg-paper-50 lg:grid-cols-12" data-reveal>
                    <div class="p-5 sm:p-7 lg:col-span-7">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-ink-300 pb-3">
                            <span class="mono-label">Cartel de campaña / {{ $destacada->periodo }}</span>
                            <span class="badge {{ $estadoColor[$destacada->estado] ?? 'badge-slate' }}">{{ $destacada->estado }}</span>
                        </div>

                        <h2 class="mt-5 max-w-2xl text-balance font-display text-3xl font-bold leading-[1.02] tracking-[-0.04em] text-ink-950 sm:text-5xl">
                            {{ $destacada->titulo }}
                        </h2>

                        <p class="mt-4 max-w-xl text-base leading-relaxed text-ink-600">
                            {{ $destacada->descripcion }}
                        </p>

                        <dl class="mt-6 grid gap-0 border-t border-ink-300 sm:grid-cols-2">
                            <div class="border-b border-ink-200 py-3 sm:border-r sm:pr-4">
                                <dt class="mono-label">Dirigido a</dt>
                                <dd class="mt-1 text-sm font-semibold text-ink-800">{{ $destacada->grupo_objetivo ?: 'Público general' }}</dd>
                            </div>
                            <div class="border-b border-ink-200 py-3 sm:pl-4">
                                <dt class="mono-label">Lugar</dt>
                                <dd class="mt-1 text-sm font-semibold text-ink-800">{{ $destacada->lugar ?: 'Por confirmar' }}</dd>
                            </div>
                        </dl>

                        <div class="mt-5 flex flex-wrap items-center gap-3">
                            <a href="/directorio-postas" class="btn btn-primary">
                                <x-icon name="map-pin" class="h-4 w-4" />
                                Cómo llegar
                            </a>
                            <span class="font-mono text-[10px] uppercase tracking-[0.05em] text-ink-500">Sin inscripción previa</span>
                        </div>
                    </div>

                    <div class="relative overflow-hidden border-t border-ink-950 bg-brand-900 p-5 text-paper-50 sm:p-7 lg:col-span-5 lg:border-l lg:border-t-0">
                        <div class="pointer-events-none absolute inset-0 bg-dot-grid opacity-25" aria-hidden="true"></div>
                        <div class="relative">
                            <p class="mono-label text-paper-100/65">Fecha de atención</p>
                            <div class="mt-3 flex items-end gap-3 border-b border-paper-50/20 pb-4">
                                <span class="font-display text-7xl font-extrabold leading-none tracking-[-0.06em] text-paper-50">
                                    {{ $destacada->fecha_inicio?->format('d') ?? '—' }}
                                </span>
                                <span class="pb-1 font-mono text-[10px] uppercase leading-relaxed tracking-[0.08em] text-paper-100/70">
                                    {{ $destacada->fecha_inicio?->locale('es')->isoFormat('MMM') ?? 'Fecha' }}<br>
                                    {{ $destacada->fecha_inicio?->format('Y') ?? 'por confirmar' }}
                                </span>
                            </div>

                            <dl class="mt-5 space-y-4 text-sm">
                                <div>
                                    <dt class="flex items-center gap-2 font-semibold text-paper-50">
                                        <x-icon name="calendar" class="h-4 w-4 shrink-0 text-signal-300" />
                                        Periodo
                                    </dt>
                                    <dd class="mt-1 pl-6 text-paper-100/75">{{ $destacada->periodo }}</dd>
                                </div>
                                <div>
                                    <dt class="flex items-center gap-2 font-semibold text-paper-50">
                                        <x-icon name="id-card" class="h-4 w-4 shrink-0 text-signal-300" />
                                        Qué llevar
                                    </dt>
                                    <dd class="mt-1 pl-6 text-paper-100/75">DNI físico. Para menores, DNI o carné de vacunación.</dd>
                                </div>
                            </dl>

                            <p class="mt-6 border-t border-paper-50/20 pt-3 font-mono text-[9px] uppercase leading-relaxed tracking-[0.06em] text-paper-100/55">
                                La atención es gratuita. Confirma fecha y lugar antes de acudir.
                            </p>
                        </div>
                    </div>
                </article>
            @endif

            @if ($resto->isNotEmpty())
                <section class="mt-8" data-reveal>
                    <div class="flex flex-wrap items-end justify-between gap-3 border-b-2 border-ink-950 pb-2">
                        <h2 class="font-display text-xl font-bold text-ink-950">Siguientes campañas</h2>
                        <span class="font-mono text-[10px] uppercase tracking-[0.06em] text-ink-500">{{ $resto->count() }} en el calendario</span>
                    </div>

                    <div class="divide-y divide-ink-200">
                        @foreach ($resto as $campana)
                            <article class="grid gap-3 py-5 sm:grid-cols-12 sm:items-start">
                                <div class="sm:col-span-2">
                                    <p class="font-mono text-[11px] font-semibold uppercase tracking-[0.06em] text-brand-800">{{ $campana->fecha_inicio?->locale('es')->isoFormat('D MMM') ?? 'Por confirmar' }}</p>
                                    <p class="mt-1 font-mono text-[10px] text-ink-500">{{ $campana->periodo }}</p>
                                </div>
                                <div class="sm:col-span-7">
                                    <h3 class="font-display text-lg font-bold leading-tight text-ink-950">{{ $campana->titulo }}</h3>
                                    <p class="mt-1 max-w-2xl text-sm leading-relaxed text-ink-600">{{ $campana->descripcion }}</p>
                                    <p class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-ink-600">
                                        <span class="flex items-center gap-1.5"><x-icon name="users" class="h-3.5 w-3.5 text-ink-400" />{{ $campana->grupo_objetivo ?: 'Público general' }}</span>
                                        <span class="flex items-center gap-1.5"><x-icon name="map-pin" class="h-3.5 w-3.5 text-ink-400" />{{ $campana->lugar ?: 'Por confirmar' }}</span>
                                    </p>
                                </div>
                                <div class="sm:col-span-3 sm:text-right">
                                    <span class="badge {{ $estadoColor[$campana->estado] ?? 'badge-slate' }}">{{ $campana->estado }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        @endif

        <section class="mt-10 border-t-2 border-ink-950" data-reveal>
            <div class="grid gap-6 py-6 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <h2 class="font-display text-2xl font-bold tracking-[-0.03em] text-ink-950">¿Cómo accedo a una campaña?</h2>
                    <p class="mt-2 max-w-sm text-sm leading-relaxed text-ink-600">
                        Las campañas no requieren inscripción previa. Acércate al establecimiento en las fechas indicadas.
                    </p>
                </div>

                <ol class="border-t border-ink-300 lg:col-span-8">
                    @foreach ([
                        ['title' => 'Lleva tu DNI', 'text' => 'DNI físico del paciente. Para menores, DNI o carné de vacunación.'],
                        ['title' => 'Respeta el horario', 'text' => 'Las campañas se atienden de lunes a sábado, según la fecha publicada.'],
                        ['title' => 'Consulta sin costo', 'text' => 'Control, orientación y derivación según el resultado de la evaluación.'],
                    ] as $paso)
                        <li class="grid grid-cols-12 gap-3 border-b border-ink-200 py-4">
                            <span class="col-span-2 font-mono text-[11px] text-ink-500 sm:col-span-1">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <div class="col-span-10 grid gap-1 sm:col-span-11 sm:grid-cols-12">
                                <h3 class="font-display text-base font-bold text-ink-950 sm:col-span-4">{{ $paso['title'] }}</h3>
                                <p class="text-sm leading-relaxed text-ink-600 sm:col-span-8">{{ $paso['text'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    </div>
@endsection
