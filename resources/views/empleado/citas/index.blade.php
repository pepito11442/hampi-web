@extends('hampi.layout')

@section('title', 'Mis citas · ' . $medico->name)
@section('meta_description', 'Agenda y revisa las citas de tus pacientes en la Microred Socabaya.')

@section('content')
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
                <x-icon name="calendar" class="h-4 w-4 text-brand-600" />
                Mi horario
            </a>
        </x-slot:actions>
    </x-page-hero>

    <div class="container-page py-8 md:py-12">
        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <div class="card p-5" data-reveal>
                <p class="stat-label">Citas de hoy</p>
                <p class="stat-number">{{ $citasHoy->count() }}</p>
                <p class="mt-1 text-xs text-ink-500">Pacientes programados para hoy.</p>
            </div>
            <div class="card p-5" data-reveal style="--reveal-delay: 60ms">
                <p class="stat-label">Próximas citas</p>
                <p class="stat-number">{{ $proximas->count() }}</p>
                <p class="mt-1 text-xs text-ink-500">Agendadas para los siguientes días.</p>
            </div>
            <div class="card p-5" data-reveal style="--reveal-delay: 120ms">
                <p class="stat-label">Historial</p>
                <p class="stat-number">{{ $historial->count() }}</p>
                <p class="mt-1 text-xs text-ink-500">Atenciones y cancelaciones anteriores.</p>
            </div>
        </div>

        <div class="space-y-6">
            <section class="card p-6" data-reveal>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="flex items-center gap-2 text-base font-bold text-ink-900">
                            <x-icon name="sun" class="h-5 w-5 text-brand-600" />
                            Citas de hoy
                        </h2>
                        <p class="mt-1 text-xs text-ink-500">Revisa a tus pacientes y cierra cada atención.</p>
                    </div>
                    <span class="badge badge-teal">{{ $citasHoy->count() }} {{ $citasHoy->count() === 1 ? 'cita' : 'citas' }}</span>
                </div>

                @if ($citasHoy->isEmpty())
                    <p class="mt-5 rounded-2xl border border-dashed border-ink-200 bg-ink-50/50 px-4 py-8 text-center text-sm text-ink-500">
                        No tienes citas programadas para hoy.
                    </p>
                @else
                    <ul class="mt-5 space-y-3">
                        @foreach ($citasHoy as $cita)
                            @include('empleado.citas.partials.cita', ['cita' => $cita, 'mostrarAcciones' => true])
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="card p-6" data-reveal>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="flex items-center gap-2 text-base font-bold text-ink-900">
                            <x-icon name="calendar-check" class="h-5 w-5 text-brand-600" />
                            Próximas citas
                        </h2>
                        <p class="mt-1 text-xs text-ink-500">Citas activas desde mañana en adelante.</p>
                    </div>
                    <a href="{{ route('empleado.citas.create') }}" class="btn btn-secondary btn-sm">
                        <x-icon name="plus" class="h-4 w-4" />
                        Nueva cita
                    </a>
                </div>

                @if ($proximas->isEmpty())
                    <p class="mt-5 rounded-2xl border border-dashed border-ink-200 bg-ink-50/50 px-4 py-8 text-center text-sm text-ink-500">
                        Todavía no tienes citas próximas. Agenda una desde el botón superior.
                    </p>
                @else
                    <ul class="mt-5 space-y-3">
                        @foreach ($proximas as $cita)
                            @include('empleado.citas.partials.cita', ['cita' => $cita, 'mostrarAcciones' => true])
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="card p-6" data-reveal>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="flex items-center gap-2 text-base font-bold text-ink-900">
                            <x-icon name="clipboard-list" class="h-5 w-5 text-brand-600" />
                            Historial
                        </h2>
                        <p class="mt-1 text-xs text-ink-500">Consultas pasadas y citas cerradas.</p>
                    </div>
                    <span class="badge badge-slate">{{ $historial->count() }} registros</span>
                </div>

                @if ($historial->isEmpty())
                    <p class="mt-5 rounded-2xl border border-dashed border-ink-200 bg-ink-50/50 px-4 py-8 text-center text-sm text-ink-500">
                        Aún no hay atenciones en tu historial.
                    </p>
                @else
                    <ul class="mt-5 space-y-3">
                        @foreach ($historial as $cita)
                            @include('empleado.citas.partials.cita', ['cita' => $cita, 'mostrarAcciones' => true])
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
@endsection
