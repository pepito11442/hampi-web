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

    <x-page-hero
        eyebrow="Rol de atención"
        title="Médicos de turno hoy"
        description="Especialidades, consultorios y estado de atención del turno de mañana y tarde en la Microred Socabaya."
        icon="stethoscope"
    >
        <x-slot:actions>
            <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white px-4 py-2 text-xs font-semibold text-ink-600">
                <x-icon name="calendar" class="h-4 w-4 text-brand-600" />
                {{ now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}
            </span>
            <a href="/directorio-postas" class="btn btn-secondary btn-sm">
                <x-icon name="map-pin" class="h-4 w-4 text-brand-600" />
                Ver postas
            </a>
        </x-slot:actions>
    </x-page-hero>

    <div class="container-page py-8 md:py-12">
        <div class="grid gap-6 lg:grid-cols-12">
            <div class="space-y-6 lg:col-span-8">
                {{-- Turno mañana --}}
                <section class="card p-6" data-reveal>
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-ink-100 pb-4">
                        <div class="flex items-center gap-3">
                            <span class="grid h-11 w-11 place-items-center rounded-2xl bg-amber-50 text-amber-600">
                                <x-icon name="sun" class="h-5 w-5" />
                            </span>
                            <div>
                                <h2 class="text-base font-bold text-ink-900">Turno mañana</h2>
                                <p class="text-xs text-ink-500">07:00 a 13:00</p>
                            </div>
                        </div>
                        <span class="badge badge-amber">{{ $turnosManana->count() }} {{ $turnosManana->count() === 1 ? 'médico' : 'médicos' }}</span>
                    </div>

                    @if ($turnosManana->isEmpty())
                        <x-empty-state
                            class="mt-5 border-0 bg-transparent px-0 py-8"
                            icon="sun"
                            title="Sin médicos programados"
                            description="Aún no se registraron médicos para el turno de mañana."
                        />
                    @else
                        <ul class="mt-4 space-y-3">
                            @foreach ($turnosManana as $turno)
                                @php
                                    $limpio = preg_replace('/^(Dr\.|Dra\.|Lic\.)\s*/u', '', $turno->medico_nombre) ?? $turno->medico_nombre;
                                    $partes = preg_split('/\s+/u', trim($limpio)) ?: [];
                                    $iniciales = mb_substr($partes[0] ?? '', 0, 1) . mb_substr($partes[1] ?? '', 0, 1);
                                    [$badgeClass, $badgeIcon] = $estados[$turno->estado] ?? ['badge-slate', 'info'];
                                @endphp

                                <li class="flex flex-col gap-3 rounded-2xl border border-ink-100 bg-ink-50/40 p-4 transition-colors hover:border-brand-200 hover:bg-brand-50/30 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white text-sm font-bold text-brand-700 shadow-sm ring-1 ring-ink-100">
                                            {{ mb_strtoupper($iniciales) }}
                                        </span>
                                        <div>
                                            <h3 class="text-sm font-bold text-ink-900">{{ $turno->medico_nombre }}</h3>
                                            <p class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-ink-500">
                                                <span>{{ $turno->especialidad }}</span>
                                                <span class="text-ink-300" aria-hidden="true">•</span>
                                                <span class="font-medium text-ink-600">{{ $turno->consultorio }}</span>
                                                <span class="text-ink-300" aria-hidden="true">•</span>
                                                <span>{{ $turno->hora_inicio }}–{{ $turno->hora_fin }}</span>
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

                {{-- Turno tarde --}}
                <section class="card p-6" data-reveal style="--reveal-delay: 90ms">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-ink-100 pb-4">
                        <div class="flex items-center gap-3">
                            <span class="grid h-11 w-11 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                                <x-icon name="sunset" class="h-5 w-5" />
                            </span>
                            <div>
                                <h2 class="text-base font-bold text-ink-900">Turno tarde</h2>
                                <p class="text-xs text-ink-500">13:00 a 19:30</p>
                            </div>
                        </div>
                        <span class="badge badge-amber">{{ $turnosTarde->count() }} {{ $turnosTarde->count() === 1 ? 'médico' : 'médicos' }}</span>
                    </div>

                    @if ($turnosTarde->isEmpty())
                        <x-empty-state
                            class="mt-5 border-0 bg-transparent px-0 py-8"
                            icon="sunset"
                            title="Sin médicos programados"
                            description="Aún no se registraron médicos para el turno de tarde."
                        />
                    @else
                        <ul class="mt-4 space-y-3">
                            @foreach ($turnosTarde as $turno)
                                @php
                                    $limpio = preg_replace('/^(Dr\.|Dra\.|Lic\.)\s*/u', '', $turno->medico_nombre) ?? $turno->medico_nombre;
                                    $partes = preg_split('/\s+/u', trim($limpio)) ?: [];
                                    $iniciales = mb_substr($partes[0] ?? '', 0, 1) . mb_substr($partes[1] ?? '', 0, 1);
                                    [$badgeClass, $badgeIcon] = $estados[$turno->estado] ?? ['badge-slate', 'info'];
                                @endphp

                                <li class="flex flex-col gap-3 rounded-2xl border border-ink-100 bg-ink-50/40 p-4 transition-colors hover:border-brand-200 hover:bg-brand-50/30 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white text-sm font-bold text-brand-700 shadow-sm ring-1 ring-ink-100">
                                            {{ mb_strtoupper($iniciales) }}
                                        </span>
                                        <div>
                                            <h3 class="text-sm font-bold text-ink-900">{{ $turno->medico_nombre }}</h3>
                                            <p class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-ink-500">
                                                <span>{{ $turno->especialidad }}</span>
                                                <span class="text-ink-300" aria-hidden="true">•</span>
                                                <span class="font-medium text-ink-600">{{ $turno->consultorio }}</span>
                                                <span class="text-ink-300" aria-hidden="true">•</span>
                                                <span>{{ $turno->hora_inicio }}–{{ $turno->hora_fin }}</span>
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
            </div>

            <aside class="space-y-6 lg:col-span-4">
                <section class="card p-6" data-reveal>
                    <h2 class="text-sm font-bold text-ink-900">Resumen del día</h2>
                    <dl class="mt-4 space-y-3">
                        <div class="flex items-center justify-between rounded-2xl bg-ink-50 px-4 py-3">
                            <dt class="flex items-center gap-2 text-xs font-semibold text-ink-500">
                                <x-icon name="users" class="h-4 w-4 text-ink-400" />
                                Médicos en rol
                            </dt>
                            <dd class="text-lg font-bold text-ink-900">{{ $totalMedicos }}</dd>
                        </div>
                        <div class="flex items-center justify-between rounded-2xl bg-brand-50 px-4 py-3">
                            <dt class="flex items-center gap-2 text-xs font-semibold text-brand-800">
                                <x-icon name="activity" class="h-4 w-4 text-brand-600" />
                                Atendiendo ahora
                            </dt>
                            <dd class="text-lg font-bold text-brand-800">{{ $atendiendo }}</dd>
                        </div>
                    </dl>
                    <a href="/pizarrin-vacunas" class="btn btn-secondary mt-4 w-full">
                        <x-icon name="syringe" class="h-4 w-4 text-brand-600" />
                        Ver vacunas disponibles
                    </a>
                </section>

                <section class="rounded-3xl border border-amber-200 bg-amber-50/80 p-5" data-reveal style="--reveal-delay: 80ms">
                    <p class="flex items-center gap-2 text-xs font-bold text-amber-900">
                        <x-icon name="info" class="h-4 w-4" />
                        Atención de urgencias
                    </p>
                    <p class="mt-2 text-xs leading-relaxed text-amber-900/80">
                        Urgencias médicas y triaje atienden de forma continua. Los pacientes graves son derivados a los hospitales de referencia de Arequipa.
                    </p>
                </section>

                <section class="card p-6" data-reveal style="--reveal-delay: 160ms">
                    <h2 class="text-sm font-bold text-ink-900">¿Buscas otra especialidad?</h2>
                    <p class="mt-2 text-xs leading-relaxed text-ink-500">
                        Revisa el directorio de establecimientos para conocer la ubicación y los horarios de atención de cada posta de la microred.
                    </p>
                    <a href="/directorio-postas" class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 hover:text-brand-800">
                        Ver directorio
                        <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                    </a>
                </section>
            </aside>
        </div>

        <p class="mt-4 text-center text-xs text-ink-400">
            Los médicos con turno de todo el día aparecen en ambos bloques.
        </p>
    </div>
@endsection
