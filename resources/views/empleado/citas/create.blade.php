@extends('hampi.layout')

@section('title', 'Agendar cita · ' . $medico->name)
@section('meta_description', 'Busca a un paciente por DNI y reserva un bloque de atención dentro de tu horario.')

@section('content')
    <x-page-hero
        eyebrow="Agenda médica"
        title="Agendar cita"
        description="Busca al paciente por DNI. Si aún no tiene ficha, la crearemos al confirmar la cita."
        icon="calendar-check"
    >
        <x-slot:actions>
            <a href="{{ route('empleado.citas.index') }}" class="btn btn-secondary btn-sm">
                <x-icon name="arrow-left" class="h-4 w-4" />
                Mis citas
            </a>
            <a href="{{ route('empleado.panel') }}" class="btn btn-secondary btn-sm">
                <x-icon name="calendar" class="h-4 w-4" />
                Mi horario
            </a>
        </x-slot:actions>
    </x-page-hero>

    <div class="container-page py-6 md:py-9">
        @if ($errors->any())
            <div role="alert" class="mb-6 flex items-start gap-3 border border-stamp-300 bg-stamp-50 p-4 text-sm text-stamp-800" data-reveal>
                <x-icon name="alert-circle" class="mt-0.5 h-5 w-5 shrink-0" />
                <div>
                    <p class="font-bold">Revisa los datos de la cita</p>
                    <ul class="mt-1 list-inside list-disc space-y-0.5 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <section class="border border-ink-300 bg-paper-50" data-reveal>
            <div class="flex items-start gap-3 border-b-2 border-ink-950 p-4">
                <span class="icon-seal">
                    <x-icon name="search" class="h-5 w-5" />
                </span>
                <div>
                    <p class="mono-label">Paso 1 / Identificación</p>
                    <h2 class="mt-1 font-display text-lg font-bold text-ink-950">Buscar paciente</h2>
                    <p class="mt-1 text-xs leading-relaxed text-ink-600">
                        Ingresa el DNI de 8 dígitos para consultar si ya tiene una ficha en la microred.
                    </p>
                </div>
            </div>

            <form action="{{ route('empleado.citas.create') }}" method="GET" class="flex flex-col gap-3 p-4 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <label for="dni_busqueda" class="label">DNI del paciente</label>
                    <input
                        type="text"
                        name="dni"
                        id="dni_busqueda"
                        value="{{ $dni }}"
                        inputmode="numeric"
                        maxlength="8"
                        pattern="[0-9]{8}"
                        placeholder="Ej. 72345678"
                        class="input font-mono tracking-[0.18em]"
                        required
                    >
                    @if ($errorDni)
                        <p class="mt-1 text-xs text-stamp-700">{{ $errorDni }}</p>
                    @endif
                </div>
                <button type="submit" class="btn btn-primary sm:mb-0">
                    <x-icon name="search" class="h-4 w-4" />
                    Buscar paciente
                </button>
            </form>
        </section>

        @if ($dni !== '' && ! $errorDni)
            <div class="mt-6 grid gap-6 lg:grid-cols-12">
                <div class="space-y-6 lg:col-span-8">
                    @if ($paciente)
                        <section class="border border-brand-200 bg-brand-50 p-4" data-reveal>
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <span class="grid h-12 w-12 shrink-0 place-items-center border border-brand-300 bg-paper-50 font-display text-base font-bold text-brand-800">
                                        {{ mb_substr($paciente->nombres, 0, 1) }}{{ mb_substr($paciente->apellidos, 0, 1) }}
                                    </span>
                                    <div>
                                        <span class="badge badge-teal">Paciente registrado</span>
                                        <h2 class="mt-2 font-display text-base font-bold text-ink-950">{{ $paciente->nombres }} {{ $paciente->apellidos }}</h2>
                                        <p class="mt-1 text-xs text-ink-600">DNI {{ $paciente->dni }} · {{ $paciente->telefono ?: 'Sin teléfono registrado' }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('empleado.citas.create') }}" class="btn btn-ghost btn-sm">Buscar otro</a>
                            </div>
                        </section>
                    @else
                        <section class="border border-maize-300 bg-maize-50 p-4 text-sm" data-reveal>
                            <p class="flex items-center gap-2 font-display font-bold text-maize-900">
                                <x-icon name="user-plus" class="h-4 w-4" />
                                Paciente no registrado
                            </p>
                            <p class="mt-1 text-xs leading-relaxed text-maize-900/90">
                                Completa sus nombres y apellidos. La ficha se creará automáticamente al confirmar la cita.
                            </p>
                        </section>
                    @endif

                    <form
                        action="{{ route('empleado.citas.store') }}"
                        method="POST"
                        class="border border-ink-300 bg-paper-50"
                        data-citas-form
                        data-reveal
                    >
                        @csrf
                        <input type="hidden" name="dni" value="{{ $dni }}">

                        <div class="border-b-2 border-ink-950 p-4">
                            <p class="mono-label">Paso 2 / Reserva</p>
                            <h2 class="mt-1 flex items-center gap-2 font-display text-lg font-bold text-ink-950">
                                <x-icon name="calendar-check" class="h-5 w-5 text-brand-700" />
                                Datos de la cita
                            </h2>
                            <p class="mt-1 text-xs leading-relaxed text-ink-600">
                                Solo se muestran bloques de 30 minutos dentro de tu horario asignado.
                            </p>
                        </div>

                        <div class="space-y-5 p-4">
                            @unless ($paciente)
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="nombres" class="label">Nombres</label>
                                        <input type="text" name="nombres" id="nombres" value="{{ old('nombres') }}" class="input" required>
                                        @error('nombres') <p class="mt-1 text-xs text-stamp-700">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="apellidos" class="label">Apellidos</label>
                                        <input type="text" name="apellidos" id="apellidos" value="{{ old('apellidos') }}" class="input" required>
                                        @error('apellidos') <p class="mt-1 text-xs text-stamp-700">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="telefono" class="label">Teléfono <span class="normal-case tracking-normal text-ink-500">(opcional)</span></label>
                                        <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" placeholder="Ej. 987 654 321" class="input">
                                        @error('telefono') <p class="mt-1 text-xs text-stamp-700">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            @endunless

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label for="posta_id" class="label">Establecimiento</label>
                                    <select name="posta_id" id="posta_id" class="input" required @disabled($postas->isEmpty())>
                                        @forelse ($postas as $posta)
                                            <option value="{{ $posta->id }}" @selected((int) old('posta_id', $postaPredeterminada) === $posta->id)>
                                                {{ $posta->nombre }}
                                            </option>
                                        @empty
                                            <option value="">No hay establecimientos activos</option>
                                        @endforelse
                                    </select>
                                    @error('posta_id') <p class="mt-1 text-xs text-stamp-700">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="fecha" class="label">Fecha</label>
                                    <input
                                        type="date"
                                        name="fecha"
                                        id="fecha"
                                        min="{{ today()->toDateString() }}"
                                        value="{{ $fecha->toDateString() }}"
                                        class="input"
                                        data-citas-fecha
                                        required
                                    >
                                    @error('fecha') <p class="mt-1 text-xs text-stamp-700">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="hora" class="label">Horario disponible</label>
                                    <select
                                        name="hora"
                                        id="hora"
                                        class="input"
                                        data-citas-hora
                                        data-disponibilidad-url="{{ route('empleado.citas.disponibilidad') }}"
                                        required
                                        @disabled(empty($slots))
                                    >
                                        @forelse ($slots as $slot)
                                            <option value="{{ $slot['hora'] }}" @selected(old('hora') === $slot['hora'])>
                                                {{ $slot['etiqueta'] }}
                                            </option>
                                        @empty
                                            <option value="">Sin horarios disponibles</option>
                                        @endforelse
                                    </select>
                                    @error('hora') <p class="mt-1 text-xs text-stamp-700">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="border border-brand-200 bg-brand-50/60 p-3.5">
                                <p class="flex items-center gap-2 font-mono text-[10px] font-semibold uppercase tracking-[0.06em] text-brand-800">
                                    <x-icon name="clock" class="h-4 w-4" />
                                    Turno asignado
                                </p>
                                <p class="mt-1 text-xs leading-relaxed text-brand-900/80" data-citas-resumen role="status" aria-live="polite">
                                    @if ($horario)
                                        {{ $horario->turno_label }} · {{ $horario->hora_inicio }} a {{ $horario->hora_fin }}
                                    @else
                                        No tienes un turno asignado para la fecha seleccionada.
                                    @endif
                                </p>
                            </div>

                            @if ($postas->isEmpty())
                                <p class="border border-stamp-300 bg-stamp-50 p-3 text-xs text-stamp-800">
                                    No hay establecimientos activos. Un administrador debe habilitar una posta antes de agendar.
                                </p>
                            @endif
                        </div>

                        <div class="flex flex-col gap-3 border-t border-ink-300 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-[11px] leading-relaxed text-ink-600">
                                La cita quedará en estado <strong class="font-semibold text-ink-800">Confirmada</strong>. Podrás marcarla como atendida o cancelarla desde Mis citas.
                            </p>
                            <button
                                type="submit"
                                class="btn btn-primary shrink-0"
                                data-citas-submit
                                @disabled(empty($slots) || $postas->isEmpty())
                            >
                                <x-icon name="calendar-check" class="h-4 w-4" />
                                Confirmar cita
                            </button>
                        </div>
                    </form>
                </div>

                <aside class="space-y-6 lg:col-span-4">
                    <section class="border border-ink-300 bg-paper-50" data-reveal>
                        <div class="flex items-center gap-3 border-b-2 border-ink-950 p-4">
                            @if ($medico->foto_url)
                                <img src="{{ $medico->foto_url }}" alt="Foto de {{ $medico->name }}" class="h-12 w-12 shrink-0 border border-ink-300 object-cover">
                            @else
                                <span class="grid h-12 w-12 shrink-0 place-items-center border border-ink-300 bg-paper-100 font-display text-sm font-bold text-brand-800">
                                    {{ $medico->iniciales() }}
                                </span>
                            @endif
                            <div class="min-w-0">
                                <p class="mono-label">Médico responsable</p>
                                <h2 class="mt-0.5 truncate font-display text-sm font-bold text-ink-950">{{ $medico->name }}</h2>
                            </div>
                        </div>
                        <dl class="space-y-2.5 p-4 text-xs">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-ink-600">Especialidad</dt>
                                <dd class="text-right font-semibold text-ink-800">{{ $medico->especialidad ?: 'Medicina General' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 border-t border-ink-200 pt-2.5">
                                <dt class="text-ink-600">Establecimiento</dt>
                                <dd class="text-right font-semibold text-ink-800">{{ $medico->establecimiento }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="border-t-2 border-ink-950" data-reveal>
                        <h2 class="border-b border-ink-300 py-3 font-display text-base font-bold text-ink-950">Reglas de agenda</h2>
                        <ul class="divide-y divide-ink-200 text-xs leading-relaxed text-ink-600">
                            @foreach ([
                                'Solo puedes reservar dentro de tus turnos asignados.',
                                'Cada cita ocupa un bloque de 30 minutos.',
                                'Un mismo paciente no puede tener dos citas activas a la misma hora.',
                                'Los domingos no hay atención en la microred.',
                            ] as $regla)
                                <li class="flex gap-2 py-3">
                                    <x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand-700" />
                                    {{ $regla }}
                                </li>
                            @endforeach
                        </ul>
                    </section>
                </aside>
            </div>
        @else
            <div class="mt-6 border border-dashed border-ink-300 bg-paper-50 px-6 py-12 text-center" data-reveal>
                <span class="icon-seal mx-auto">
                    <x-icon name="id-card" class="h-6 w-6" />
                </span>
                <h2 class="mt-4 font-display text-lg font-bold text-ink-950">Empieza con el DNI del paciente</h2>
                <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-ink-600">
                    Cuando encuentres o registres a la persona, podrás elegir la fecha y uno de los bloques libres de tu agenda.
                </p>
            </div>
        @endif
    </div>
@endsection
