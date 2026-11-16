@extends('hampi.layout')

@section('title', 'Pedir una cita · Hampi')
@section('meta_description', 'Reserva una cita con los médicos de la Microred Socabaya.')

@section('content')
    @php
        $limiteAlcanzado = $citasActivas >= \App\Http\Controllers\CiudadanoController::MAX_CITAS_ACTIVAS;
    @endphp

    <x-page-board>
        <x-slot:hero>
            <x-page-hero
                eyebrow="Panel ciudadano"
                title="Pedir una cita"
                description="Elige al médico, el día y la hora. Solo aparecen los horarios que todavía están libres."
                icon="calendar-check"
            >
                <x-slot:actions>
                    <a href="{{ route('ciudadano.panel') }}" class="btn btn-secondary btn-sm">
                        <x-icon name="arrow-left" class="h-4 w-4" />
                        Mis citas
                    </a>
                </x-slot:actions>
            </x-page-hero>
        </x-slot:hero>

        <div class="container-page max-w-3xl py-6 md:py-9">
            @if ($errors->any())
                <div role="alert" class="mb-6 flex items-start gap-3 border border-stamp-300 bg-stamp-50 p-4 text-base text-stamp-800">
                    <x-icon name="alert-circle" class="mt-0.5 h-5 w-5 shrink-0" />
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($limiteAlcanzado)
                <div class="notice notice-warning mb-6 flex items-start gap-2 text-base">
                    <x-icon name="info" class="mt-1 h-4 w-4 shrink-0" />
                    <span>Ya tienes {{ $citasActivas }} citas pendientes. Para pedir otra, cancela una desde tu panel o espera a ser atendido.</span>
                </div>
            @endif

            @if ($medicos->isEmpty())
                <x-empty-state
                    icon="calendar"
                    title="No hay turnos disponibles"
                    description="Ningún médico tiene horarios publicados para los próximos días. Vuelve a intentarlo más tarde o acércate a tu posta."
                />
            @else
                <form
                    action="{{ route('ciudadano.citas.store') }}"
                    method="POST"
                    class="border-2 border-ink-950 bg-paper-50"
                    data-citas-form
                    @if ($limiteAlcanzado) data-citas-bloqueado @endif
                >
                    @csrf

                    <div class="space-y-6 p-5 sm:p-6">
                        <div>
                            <label for="medico" class="label text-sm">1. Médico</label>
                            <select name="medico" id="medico" class="input text-base" data-citas-medico required>
                                @foreach ($medicos as $opcion)
                                    <option value="{{ $opcion->id }}" @selected((int) old('medico', $medico?->id) === $opcion->id)>
                                        {{ $opcion->name }} — {{ $opcion->especialidad ?: 'Medicina General' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="fecha" class="label text-sm">2. Día</label>
                                <input
                                    type="date"
                                    name="fecha"
                                    id="fecha"
                                    min="{{ today()->toDateString() }}"
                                    max="{{ today()->addDays(\App\Http\Controllers\CiudadanoController::DIAS_RESERVA)->toDateString() }}"
                                    value="{{ old('fecha', $fecha->toDateString()) }}"
                                    class="input text-base"
                                    data-citas-fecha
                                    required
                                >
                            </div>

                            <div>
                                <label for="hora" class="label text-sm">3. Hora</label>
                                <select
                                    name="hora"
                                    id="hora"
                                    class="input text-base"
                                    data-citas-hora
                                    data-disponibilidad-url="{{ route('ciudadano.citas.disponibilidad') }}"
                                    required
                                    @disabled(empty($bloques))
                                >
                                    @forelse ($bloques as $bloque)
                                        <option value="{{ $bloque['hora'] }}" @selected(old('hora') === $bloque['hora'])>{{ $bloque['etiqueta'] }}</option>
                                    @empty
                                        <option value="">Sin horarios libres</option>
                                    @endforelse
                                </select>
                            </div>
                        </div>

                        <p class="border border-brand-200 bg-brand-50/60 p-3.5 text-sm leading-relaxed text-brand-900" data-citas-resumen role="status" aria-live="polite">
                            @if (empty($bloques))
                                No quedan horarios libres para ese día. Prueba con otra fecha.
                            @else
                                {{ count($bloques) }} {{ count($bloques) === 1 ? 'horario libre' : 'horarios libres' }} el {{ $fecha->locale('es')->isoFormat('dddd D [de] MMMM') }}.
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-ink-300 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                        <p class="text-sm leading-relaxed text-ink-600">
                            Tu cita quedará <strong class="text-ink-900">reservada</strong>. Llega 15 minutos antes con tu DNI.
                        </p>
                        <button type="submit" class="btn btn-primary shrink-0 text-base" data-citas-submit @disabled(empty($bloques) || $limiteAlcanzado)>
                            <x-icon name="calendar-check" class="h-5 w-5" />
                            Reservar cita
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </x-page-board>
@endsection
