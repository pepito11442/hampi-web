@extends('hampi.layout')

@section('title', 'Médicos de turno hoy · Hampi')
@section('meta_description', 'Consulta el rol de médicos de turno de hoy en la Microred Socabaya: especialidad, consultorio y estado de atención.')

@section('content')
    @php
        $totalMedicos = $medicos->count();
        $atendiendo = $medicos->where('estado', 'Atendiendo')->count();
        $estados = [
            'Atendiendo' => ['badge-teal', 'activity'],
            'Turno Completo' => ['badge-slate', 'clock'],
            'Reprogramado' => ['badge-red', 'calendar'],
            'Programado' => ['badge-sky', 'clock'],
            'Finalizado' => ['badge-slate', 'check-circle'],
        ];
    @endphp
    <x-page-board>
        <x-slot:hero>
            <x-page-hero
                eyebrow="Rol de atención"
                title="Médicos de turno hoy"
                description="Especialidades, consultorios y estado de atención del turno de mañana y tarde en la Microred Socabaya."
                icon="stethoscope"
            >
                <x-slot:actions>
                    <span class="inline-flex min-h-10 items-center gap-2 border border-ink-300 bg-paper-50 px-3 text-xs font-semibold text-ink-700">
                        <x-icon name="calendar" class="h-4 w-4 text-brand-700" />
                        {{ now()->locale('es')->isoFormat('ddd D [de] MMMM') }}
                    </span>
                    <a href="/directorio-postas" class="btn btn-secondary btn-sm">
                        <x-icon name="map-pin" class="h-4 w-4" />
                        Ver postas
                    </a>
                </x-slot:actions>
            </x-page-hero>
        </x-slot:hero>

        <div class="container-page py-8 md:py-12">
            <div class="grid gap-6 lg:grid-cols-12">
                <div class="space-y-7 lg:col-span-8">
                    @foreach ([
                        [
                            'titulo' => 'Turno mañana',
                            'horario' => '07:00 a 13:00',
                            'icono' => 'sun',
                            'turnos' => $turnosManana,
                        ],
                        [
                            'titulo' => 'Turno tarde',
                            'horario' => '13:00 a 19:30',
                            'icono' => 'sunset',
                            'turnos' => $turnosTarde,
                        ],
                    ] as $bloque)
                        <section class="border-t-2 border-ink-950" data-reveal>
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-ink-300 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="icon-seal">
                                        <x-icon :name="$bloque['icono']" class="h-5 w-5" />
                                    </span>
                                    <div>
                                        <h2 class="font-display text-lg font-bold text-ink-950">{{ $bloque['titulo'] }}</h2>
                                        <p class="font-mono text-[10px] uppercase tracking-[0.06em] text-ink-500">{{ $bloque['horario'] }}</p>
                                    </div>
                                </div>
                                <span class="badge badge-slate">
                                    {{ $bloque['turnos']->count() }} {{ $bloque['turnos']->count() === 1 ? 'médico' : 'médicos' }}
                                </span>
                            </div>

                            @if ($bloque['turnos']->isEmpty())
                                <x-empty-state
                                    class="border-0 bg-transparent px-0 py-8"
                                    icon="calendar"
                                    title="Sin médicos programados"
                                    description="Aún no se registraron médicos para este turno."
                                />
                            @else
                                <ul class="divide-y divide-ink-200">
                                    @foreach ($bloque['turnos'] as $turno)
                                        @php
                                            $limpio = preg_replace('/^(Dr\.|Dra\.|Lic\.)\s*/u', '', $turno->medico_nombre) ?? $turno->medico_nombre;
                                            $partes = preg_split('/\s+/u', trim($limpio)) ?: [];
                                            $iniciales = mb_substr($partes[0] ?? '', 0, 1) . mb_substr($partes[1] ?? '', 0, 1);
                                            [$badgeClass, $badgeIcon] = $estados[$turno->estado] ?? ['badge-slate', 'info'];
                                        @endphp

                                        <li class="flex flex-col gap-3 py-4 transition-colors hover:bg-paper-50 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="flex items-start gap-3">
                                                <span class="grid h-10 w-10 shrink-0 place-items-center border border-ink-300 bg-paper-100 font-mono text-[11px] font-semibold text-brand-800">
                                                    {{ mb_strtoupper($iniciales) }}
                                                </span>
                                                <div>
                                                    <h3 class="font-display text-base font-bold leading-tight text-ink-950">{{ $turno->medico_nombre }}</h3>
                                                    <p class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-ink-600">
                                                        <span>{{ $turno->especialidad }}</span>
                                                        <span class="text-ink-300" aria-hidden="true">/</span>
                                                        <span class="font-mono text-[10px] text-ink-700">{{ $turno->consultorio }}</span>
                                                        <span class="text-ink-300" aria-hidden="true">/</span>
                                                        <span class="font-mono text-[10px]">{{ $turno->hora_inicio }}–{{ $turno->hora_fin }}</span>
                                                        @if (in_array($turno->turno, ['Todo el día', 'Sábado'], true))
                                                            <span class="badge badge-amber">{{ $turno->turno }}</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <span class="badge {{ $badgeClass }} self-start sm:self-auto">
                                                <x-icon :name="$badgeIcon" class="h-3.5 w-3.5" />
                                                {{ $turno->estado }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </section>
                    @endforeach

                    <p class="border-t border-ink-200 pt-3 font-mono text-[10px] uppercase tracking-[0.05em] text-ink-500">
                        Los médicos con turno de todo el día aparecen en ambos bloques.
                    </p>
                </div>

                <aside class="space-y-6 lg:col-span-4">
                    <section class="border-t-2 border-ink-950" data-reveal>
                        <h2 class="border-b border-ink-300 py-3 font-display text-base font-bold text-ink-950">Resumen del día</h2>
                        <dl class="divide-y divide-ink-200">
                            <div class="flex items-center justify-between gap-3 py-3">
                                <dt class="flex items-center gap-2 text-xs font-semibold text-ink-600">
                                    <x-icon name="users" class="h-4 w-4 text-ink-400" />
                                    Médicos en rol
                                </dt>
                                <dd class="font-display text-2xl font-bold tabular text-ink-950">{{ $totalMedicos }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-3">
                                <dt class="flex items-center gap-2 text-xs font-semibold text-ink-600">
                                    <x-icon name="activity" class="h-4 w-4 text-brand-700" />
                                    Atendiendo ahora
                                </dt>
                                <dd class="font-display text-2xl font-bold tabular text-brand-800">{{ $atendiendo }}</dd>
                            </div>
                        </dl>
                        <a href="/pizarrin-vacunas" class="btn btn-secondary mt-3 w-full">
                            <x-icon name="syringe" class="h-4 w-4" />
                            Ver vacunas disponibles
                        </a>
                    </section>

                    <section class="border border-maize-300 bg-maize-50 p-5" data-reveal>
                        <p class="flex items-center gap-2 font-mono text-[10px] font-semibold uppercase tracking-[0.08em] text-maize-900">
                            <x-icon name="alert-circle" class="h-4 w-4" />
                            Atención de urgencias
                        </p>
                        <p class="mt-2 text-xs leading-relaxed text-maize-900/90">
                            Urgencias médicas y triaje atienden de forma continua. Los pacientes graves son derivados a los hospitales de referencia de Arequipa.
                        </p>
                    </section>

                    <section class="border border-ink-300 bg-paper-50 p-5" data-reveal>
                        <h2 class="font-display text-base font-bold text-ink-950">¿Buscas otra especialidad?</h2>
                        <p class="mt-2 text-xs leading-relaxed text-ink-600">
                            Revisa el directorio de establecimientos para conocer la ubicación y los horarios de atención de cada posta de la microred.
                        </p>
                        <a href="/directorio-postas" class="link-arrow mt-2">
                            Ver directorio
                            <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                        </a>
                    </section>
                </aside>
            </div>
        </div>
    </x-page-board>
@endsection
