@extends('hampi.layout')

@section('title', 'Mis citas · ' . $medico->name)
@section('meta_description', 'Agenda y revisa las citas de tus pacientes en la Microred Socabaya.')

@section('content')
    <x-page-board>
        <x-slot:hero>
            <x-page-hero
                eyebrow="Agenda médica"
                title="Mis citas"
                :description="'Pacientes agendados por ' . $medico->name . ' · ' . ($medico->especialidad ?: 'Medicina General')"
                icon="calendar-check"
            >
                <x-slot:actions>
                    <a href="{{ route('empleado.citas.create') }}" class="btn btn-primary btn-sm">
                        <x-icon name="plus" class="h-4 w-4" />
                        Agendar cita
                    </a>
                    <a href="{{ route('empleado.panel') }}" class="btn btn-secondary btn-sm">
                        <x-icon name="calendar" class="h-4 w-4" />
                        Mi horario
                    </a>
                </x-slot:actions>
            </x-page-hero>
        </x-slot:hero>

        <div class="container-page py-6 md:py-9">
            <section class="grid border-y-2 border-ink-950 bg-paper-50 sm:grid-cols-3" data-reveal>
                <div class="border-b border-ink-200 p-4 sm:border-b-0 sm:border-r">
                    <p class="mono-label">Citas de hoy</p>
                    <p class="stat-number mt-2">{{ $citasHoy->count() }}</p>
                    <p class="mt-1 text-xs text-ink-500">Pacientes programados para hoy.</p>
                </div>
                <div class="border-b border-ink-200 p-4 sm:border-b-0 sm:border-r">
                    <p class="mono-label">Próximas citas</p>
                    <p class="stat-number mt-2">{{ $proximas->count() }}</p>
                    <p class="mt-1 text-xs text-ink-500">Agendadas para los siguientes días.</p>
                </div>
                <div class="p-4">
                    <p class="mono-label">Historial</p>
                    <p class="stat-number mt-2">{{ $historial->count() }}</p>
                    <p class="mt-1 text-xs text-ink-500">Atenciones y cancelaciones anteriores.</p>
                </div>
            </section>

            <div class="mt-7 space-y-7">
                <section data-reveal>
                    <div class="flex flex-wrap items-end justify-between gap-3 border-b-2 border-ink-950 pb-2">
                        <div>
                            <p class="mono-label">Agenda del día</p>
                            <h2 class="mt-1 flex items-center gap-2 font-display text-xl font-bold text-ink-950">
                                <x-icon name="sun" class="h-5 w-5 text-brand-700" />
                                Citas de hoy
                            </h2>
                            <p class="mt-1 text-xs text-ink-600">Revisa a tus pacientes y cierra cada atención.</p>
                        </div>
                        <span class="badge badge-teal">{{ $citasHoy->count() }} {{ $citasHoy->count() === 1 ? 'cita' : 'citas' }}</span>
                    </div>

                    @if ($citasHoy->isEmpty())
                        <p class="border-b border-ink-200 py-8 text-sm text-ink-600">No tienes citas programadas para hoy.</p>
                    @else
                        <ul>
                            @foreach ($citasHoy as $cita)
                                @include('empleado.citas.partials.cita', ['cita' => $cita, 'mostrarAcciones' => true])
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section data-reveal>
                    <div class="flex flex-wrap items-end justify-between gap-3 border-b-2 border-ink-950 pb-2">
                        <div>
                            <p class="mono-label">Siguientes días</p>
                            <h2 class="mt-1 flex items-center gap-2 font-display text-xl font-bold text-ink-950">
                                <x-icon name="calendar-check" class="h-5 w-5 text-brand-700" />
                                Próximas citas
                            </h2>
                            <p class="mt-1 text-xs text-ink-600">Citas activas desde mañana en adelante.</p>
                        </div>
                        <a href="{{ route('empleado.citas.create') }}" class="btn btn-secondary btn-sm">
                            <x-icon name="plus" class="h-4 w-4" />
                            Nueva cita
                        </a>
                    </div>

                    @if ($proximas->isEmpty())
                        <p class="border-b border-ink-200 py-8 text-sm text-ink-600">Todavía no tienes citas próximas. Agenda una desde el botón superior.</p>
                    @else
                        <ul>
                            @foreach ($proximas as $cita)
                                @include('empleado.citas.partials.cita', ['cita' => $cita, 'mostrarAcciones' => true])
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section data-reveal>
                    <div class="flex flex-wrap items-end justify-between gap-3 border-b-2 border-ink-950 pb-2">
                        <div>
                            <p class="mono-label">Registro</p>
                            <h2 class="mt-1 flex items-center gap-2 font-display text-xl font-bold text-ink-950">
                                <x-icon name="clipboard-list" class="h-5 w-5 text-brand-700" />
                                Historial
                            </h2>
                            <p class="mt-1 text-xs text-ink-600">Consultas pasadas y citas cerradas.</p>
                        </div>
                        <span class="badge badge-slate">{{ $historial->count() }} registros</span>
                    </div>

                    @if ($historial->isEmpty())
                        <p class="border-b border-ink-200 py-8 text-sm text-ink-600">Aún no hay atenciones en tu historial.</p>
                    @else
                        <ul>
                            @foreach ($historial as $cita)
                                @include('empleado.citas.partials.cita', ['cita' => $cita, 'mostrarAcciones' => true])
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>
        </div>
    </x-page-board>
@endsection
