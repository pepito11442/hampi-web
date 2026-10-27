@extends('hampi.layout')

@section('title', 'Horarios mensuales · Administración Hampi')
@section('meta_description', 'Genera y revisa los horarios mensuales de médicos y enfermeros de la Microred Socabaya.')

@section('content')
    @php
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
        $turnosAsignados = $horarios->count();
        $progreso = $user ? (int) min(100, round(($horasTotales / 150) * 100)) : 0;
        $horasTexto = rtrim(rtrim(number_format($horasTotales, 1, '.', ''), '0'), '.');
    @endphp
    <x-page-board>
        <x-slot:hero>
            <x-page-hero
                eyebrow="Administración"
                title="Horarios mensuales"
                description="Cada médico y enfermero cumple 150 horas al mes. El generador reparte los turnos respetando los domingos sin atención."
                icon="calendar"
            >
                <x-slot:actions>
                    <a href="{{ route('admin.equipo.index') }}" class="btn btn-primary btn-sm">
                        <x-icon name="users" class="h-4 w-4" />
                        Gestionar equipo
                    </a>
                </x-slot:actions>
            </x-page-hero>
        </x-slot:hero>

        <x-slot:nav>
            @include('hampi.partials.admin-nav')
        </x-slot:nav>

        <div class="container-page py-6 md:py-9">
            @if ($errors->any())
                <div role="alert" class="mb-6 flex items-start gap-3 border border-stamp-300 bg-stamp-50 p-4 text-sm text-stamp-800">
                    <x-icon name="alert-circle" class="mt-0.5 h-5 w-5 shrink-0" />
                    <div>
                        <p class="font-bold">No se pudo completar la operación</p>
                        <ul class="mt-1 list-inside list-disc space-y-0.5 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-ink-300 bg-paper-50 border border-ink-300 sm:p-4">
                <div class="flex gap-1 overflow-x-auto" role="group" aria-label="Filtrar horarios por rol">
                    @foreach ([
                        ['key' => 'todos', 'label' => 'Todo el equipo', 'count' => $conteos['todos'], 'icon' => 'users'],
                        ['key' => 'doctores', 'label' => 'Médicos', 'count' => $conteos['doctores'], 'icon' => 'stethoscope'],
                        ['key' => 'enfermeros', 'label' => 'Enfermería', 'count' => $conteos['enfermeros'], 'icon' => 'heart-pulse'],
                    ] as $pestana)
                        <a
                            href="{{ route('admin.horarios.index', array_filter(['rol' => $pestana['key'], 'anio' => $anio, 'mes' => $mes])) }}"
                            class="inline-flex min-h-11 shrink-0 items-center gap-2 border-b-2 px-3 text-xs font-semibold transition-colors {{ $rol === $pestana['key'] ? 'border-signal-600 bg-paper-50 text-ink-950' : 'border-transparent text-ink-600 hover:bg-paper-200 hover:text-ink-950' }}"
                            @if ($rol === $pestana['key']) aria-current="page" @endif
                        >
                            <x-icon :name="$pestana['icon']" class="h-4 w-4" />
                            {{ $pestana['label'] }}
                            <span class="border border-current px-1.5 py-0.5 font-mono text-[9px]">{{ $pestana['count'] }}</span>
                        </a>
                    @endforeach
                </div>

                <p class="hidden font-mono text-[10px] uppercase tracking-[0.05em] text-ink-500 sm:block">Meta mensual: 150 h</p>
            </div>

            @if ($personal->isEmpty())
                <x-empty-state
                    class="mt-5"
                    icon="calendar"
                    title="No hay personal para programar"
                    description="Primero crea las cuentas de médicos o enfermeros. Después podrás generar su horario mensual de 150 horas."
                >
                    <x-slot:action>
                        <a href="{{ route('admin.equipo.index') }}" class="btn btn-primary">Crear cuenta de trabajador</a>
                    </x-slot:action>
                </x-empty-state>
            @else
                {{-- Selector de trabajador y periodo --}}
                <section class="mt-5 border border-ink-300 bg-paper-50 p-4" data-reveal>
                    <form action="{{ route('admin.horarios.index') }}" method="GET" class="grid gap-4 md:grid-cols-12 md:items-end">
                        @if ($rol !== 'todos')
                            <input type="hidden" name="rol" value="{{ $rol }}">
                        @endif

                        <div class="md:col-span-12 lg:col-span-6">
                            <label for="user_id" class="label">Trabajador</label>
                            <select name="user_id" id="user_id" class="input">
                                @foreach ($personal as $persona)
                                    <option value="{{ $persona->id }}" @selected($user && $user->id === $persona->id)>
                                        {{ $persona->name }} · {{ $persona->rol_label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-5 lg:col-span-3">
                            <label for="mes" class="label">Mes</label>
                            <select name="mes" id="mes" class="input">
                                @foreach ($meses as $numero => $nombre)
                                    <option value="{{ $numero }}" @selected($mes === $numero)>{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-4 lg:col-span-2">
                            <label for="anio" class="label">Año</label>
                            <input type="number" name="anio" id="anio" min="2020" max="2100" value="{{ $anio }}" class="input">
                        </div>

                        <div class="md:col-span-3 lg:col-span-1">
                            <button type="submit" class="btn btn-primary w-full">
                                <x-icon name="search" class="h-4 w-4" />
                                <span class="sr-only">Consultar horario</span>
                            </button>
                        </div>
                    </form>
                </section>

                @if ($user)
                    {{-- Resumen del trabajador --}}
                    <section class="mt-5 border border-ink-300 bg-paper-50" data-reveal>
                        <div class="grid gap-5 p-4 sm:p-5 lg:grid-cols-12 lg:items-center">
                            <div class="flex items-start gap-4 lg:col-span-5">
                                @if ($user->foto_url)
                                    <img src="{{ $user->foto_url }}" alt="Foto de {{ $user->name }}" class="h-16 w-16 shrink-0 border border-ink-300 object-cover">
                                @else
                                    <span class="grid h-16 w-16 shrink-0 place-items-center border border-ink-300 bg-paper-100 font-display text-lg font-bold text-brand-800">
                                        {{ $user->iniciales() }}
                                    </span>
                                @endif

                                <div class="min-w-0">
                                    <h2 class="font-display text-lg font-bold leading-tight text-ink-950">{{ $user->name }}</h2>
                                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                                        <x-badge color="teal">{{ $user->rol_label }}</x-badge>
                                        <x-badge color="sky">{{ $meses[$mes] }} {{ $anio }}</x-badge>
                                    </div>
                                    <p class="mt-2 text-xs text-ink-600">
                                        DNI {{ $user->dni }} · {{ $user->especialidad ?: 'Especialidad por completar' }}
                                    </p>
                                </div>
                            </div>

                            <div class="border-t border-ink-200 pt-4 lg:col-span-4 lg:border-l lg:border-t-0 lg:pl-5 lg:pt-0">
                                <div class="flex items-end justify-between gap-3">
                                    <div>
                                        <p class="mono-label">Horas asignadas</p>
                                        <div class="mt-1 flex items-baseline gap-1.5">
                                            <span class="stat-number">{{ $horasTexto }}</span>
                                            <span class="font-mono text-[11px] font-semibold uppercase tracking-[0.04em] text-ink-500">de 150 h</span>
                                        </div>
                                    </div>
                                    <span class="badge {{ $horasTotales >= 150 ? 'badge-teal' : 'badge-amber' }}">
                                        {{ $turnosAsignados }} {{ $turnosAsignados === 1 ? 'turno' : 'turnos' }}
                                    </span>
                                </div>
                                <div class="mt-3 h-2 w-full bg-ink-100" role="progressbar" aria-valuemin="0" aria-valuemax="150" aria-valuenow="{{ $horasTotales }}" aria-label="Avance de horas del trabajador">
                                    <div class="h-full bg-brand-700 transition-[width] duration-500" style="width: {{ $progreso }}%"></div>
                                </div>
                                <p class="mt-1.5 font-mono text-[9px] uppercase tracking-[0.05em] text-ink-500">
                                    {{ $horasTotales >= 150 ? 'Cumple la meta mensual de 150 horas' : 'Aún no se alcanzan las 150 horas' }}
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2 border-t border-ink-200 pt-4 lg:col-span-3 lg:justify-end lg:border-t-0 lg:pt-0">
                                <form
                                    action="{{ route('admin.horarios.generar') }}"
                                    method="POST"
                                    @if ($turnosAsignados > 0) onsubmit="return confirm('Esto reemplazará el horario de {{ $meses[$mes] }} de {{ $user->name }}. ¿Continuar?');" @endif
                                >
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                    <input type="hidden" name="anio" value="{{ $anio }}">
                                    <input type="hidden" name="mes" value="{{ $mes }}">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <x-icon name="sparkles" class="h-4 w-4" />
                                        {{ $turnosAsignados > 0 ? 'Regenerar horario' : 'Generar horario' }}
                                    </button>
                                </form>

                                @if ($turnosAsignados > 0)
                                    <form action="{{ route('admin.horarios.destroy', [$user, $anio, $mes]) }}" method="POST" onsubmit="return confirm('¿Eliminar el horario de {{ $meses[$mes] }} de {{ $user->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <x-icon name="trash-2" class="h-4 w-4" />
                                            Eliminar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        {{-- Meses del año --}}
                        <div class="border-t border-ink-300 bg-paper-100 px-3 py-2">
                            <div class="flex gap-1 overflow-x-auto">
                                @foreach ($meses as $numero => $nombre)
                                    <a
                                        href="{{ route('admin.horarios.index', array_filter(['user_id' => $user->id, 'anio' => $anio, 'mes' => $numero, 'rol' => $rol !== 'todos' ? $rol : null])) }}"
                                        class="inline-flex min-h-9 shrink-0 items-center border px-2.5 font-mono text-[10px] font-semibold uppercase tracking-[0.04em] transition-colors {{ $mes === $numero ? 'border-ink-950 bg-ink-950 text-paper-50' : 'border-ink-300 bg-paper-50 text-ink-600 hover:border-ink-500 hover:text-ink-950' }}"
                                        @if ($mes === $numero) aria-current="page" @endif
                                    >
                                        {{ $nombre }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    {{-- Calendario --}}
                    <section class="mt-5 border border-ink-300 bg-paper-50" data-reveal>
                        <div class="flex flex-col gap-2 border-b-2 border-ink-950 p-4 sm:flex-row sm:items-start sm:justify-between sm:p-5">
                            <div>
                                <p class="mono-label">Calendario del mes</p>
                                <h2 class="mt-1 font-display text-xl font-bold text-ink-950">{{ $meses[$mes] }} {{ $anio }}</h2>
                                <p class="mt-1 text-xs leading-relaxed text-ink-600">
                                    Turnos asignados a {{ $user->name }}. Los domingos no se atiende y los sábados solo se trabaja de 07:00 a 15:00.
                                </p>
                            </div>
                            <x-badge :color="$horasTotales >= 150 ? 'teal' : 'amber'">
                                {{ $horasTotales >= 150 ? '150 h cumplidas' : 'Meta: 150 h' }}
                            </x-badge>
                        </div>

                        <div class="p-3 sm:p-5">
                            @if ($turnosAsignados === 0)
                                <x-empty-state
                                    class="border-0 px-0 py-6"
                                    icon="calendar"
                                    title="Sin horario generado"
                                    description="Genera el horario de este mes. El sistema distribuirá turnos y garantizará exactamente 150 horas."
                                >
                                    <x-slot:action>
                                        <form action="{{ route('admin.horarios.generar') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                                            <input type="hidden" name="anio" value="{{ $anio }}">
                                            <input type="hidden" name="mes" value="{{ $mes }}">
                                            <button type="submit" class="btn btn-primary">
                                                <x-icon name="sparkles" class="h-4 w-4" />
                                                Generar horario de {{ $meses[$mes] }}
                                            </button>
                                        </form>
                                    </x-slot:action>
                                </x-empty-state>
                            @else
                                @include('hampi.partials.calendario', ['anio' => $anio, 'mes' => $mes, 'horarios' => $horarios])
                            @endif
                        </div>
                    </section>

                    {{-- Reglas del generador --}}
                    <section class="mt-5 border border-maize-300 bg-maize-50 p-5" data-reveal>
                        <div class="flex items-start gap-3">
                            <span class="icon-seal border-maize-400 bg-maize-100 text-maize-800">
                                <x-icon name="info" class="h-5 w-5" />
                            </span>
                            <div class="text-xs leading-relaxed text-maize-900/90">
                                <p class="font-display text-base font-bold text-ink-950">Reglas del horario mensual</p>
                                <ul class="mt-1.5 list-inside list-disc space-y-0.5">
                                    <li>Lunes a viernes: mañana 07:00–13:00 (6 h), tarde 13:00–19:30 (6.5 h) o todo el día 07:00–19:00 (12 h).</li>
                                    <li>Sábados: solo turno completo de 07:00 a 15:00 (8 h).</li>
                                    <li>Domingos: sin atención.</li>
                                    <li>El generador garantiza exactamente 150 horas por mes y puede regenerarse si se necesita otro reparto.</li>
                                </ul>
                            </div>
                        </div>
                    </section>
                @endif
            @endif
        </div>
    </x-page-board>
@endsection
