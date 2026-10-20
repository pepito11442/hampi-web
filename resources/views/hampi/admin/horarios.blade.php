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
        $progreso = $user ? (int) min(100, round(($totalHoras / 150) * 100)) : 0;
        $horasTexto = rtrim(rtrim(number_format($totalHoras, 1, '.', ''), '0'), '.');
    @endphp

    <x-page-hero
        eyebrow="Administración"
        title="Horarios mensuales"
        description="Cada médico y enfermero cumple 150 horas al mes. El generador reparte los turnos de forma aleatoria respetando los domingos sin atención."
        icon="calendar"
    >
        <x-slot:actions>
            <a href="{{ route('admin.equipo.index') }}" class="btn btn-secondary btn-sm">
                <x-icon name="users" class="h-4 w-4 text-brand-600" />
                Gestionar equipo
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">
                <x-icon name="arrow-left" class="h-4 w-4 text-brand-600" />
                Volver al panel
            </a>
        </x-slot:actions>
    </x-page-hero>

    @include('hampi.partials.admin-nav')

    <div class="container-page py-8 md:py-12">
        @if ($errors->any())
            <div role="alert" class="mb-6 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
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

        {{-- Filtros por rol --}}
        <div class="mb-6 flex flex-wrap items-center gap-2">
            @foreach ([
                ['key' => 'todos', 'label' => 'Todo el equipo', 'count' => $conteos['todos'], 'icon' => 'users'],
                ['key' => 'doctores', 'label' => 'Médicos', 'count' => $conteos['doctores'], 'icon' => 'stethoscope'],
                ['key' => 'enfermeros', 'label' => 'Enfermería', 'count' => $conteos['enfermeros'], 'icon' => 'heart-pulse'],
            ] as $tab)
                <a
                    href="{{ route('admin.horarios.index', array_filter(['rol' => $tab['key'], 'anio' => $anio, 'mes' => $mes])) }}"
                    class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-bold transition-colors {{ $rol === $tab['key'] ? 'border-ink-950 bg-ink-950 text-white' : 'border-ink-200 bg-white text-ink-600 hover:border-brand-300 hover:text-brand-800' }}"
                    @if ($rol === $tab['key']) aria-current="page" @endif
                >
                    <x-icon :name="$tab['icon']" class="h-4 w-4" />
                    {{ $tab['label'] }}
                    <span class="rounded-full px-1.5 py-0.5 text-[10px] {{ $rol === $tab['key'] ? 'bg-white/15 text-white' : 'bg-ink-100 text-ink-500' }}">{{ $tab['count'] }}</span>
                </a>
            @endforeach
        </div>

        @if ($personal->isEmpty())
            <x-empty-state
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
            <section class="card p-5" data-reveal>
                <form action="{{ route('admin.horarios.index') }}" method="GET" class="grid gap-4 md:grid-cols-12 md:items-end">
                    @if ($rol !== 'todos')
                        <input type="hidden" name="rol" value="{{ $rol }}">
                    @endif

                    <div class="md:col-span-6">
                        <label for="user_id" class="label">Trabajador</label>
                        <select name="user_id" id="user_id" class="input">
                            @foreach ($personal as $persona)
                                <option value="{{ $persona->id }}" @selected($user && $user->id === $persona->id)>
                                    {{ $persona->name }} · {{ $persona->rol_label }}@if ($persona->especialidad) · {{ $persona->especialidad }}@endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-3">
                        <label for="mes" class="label">Mes</label>
                        <select name="mes" id="mes" class="input">
                            @foreach ($meses as $numero => $nombre)
                                <option value="{{ $numero }}" @selected($mes === $numero)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="anio" class="label">Año</label>
                        <input type="number" name="anio" id="anio" min="2020" max="2100" value="{{ $anio }}" class="input">
                    </div>

                    <div class="md:col-span-1">
                        <button type="submit" class="btn btn-primary w-full">
                            <x-icon name="search" class="h-4 w-4" />
                            <span class="md:hidden xl:inline">Ver</span>
                        </button>
                    </div>
                </form>
            </section>

            @if ($user)
                {{-- Resumen del trabajador --}}
                <section class="card mt-6 overflow-hidden" data-reveal>
                    <div class="grid gap-6 p-6 lg:grid-cols-12 lg:items-center">
                        <div class="flex items-start gap-4 lg:col-span-5">
                            @if ($user->foto_url)
                                <img src="{{ $user->foto_url }}" alt="Foto de {{ $user->name }}" class="h-16 w-16 shrink-0 rounded-3xl object-cover ring-4 ring-brand-50">
                            @else
                                <span class="grid h-16 w-16 shrink-0 place-items-center rounded-3xl bg-gradient-to-br from-brand-500 to-brand-700 text-lg font-bold text-white shadow-glow">
                                    {{ $user->iniciales() }}
                                </span>
                            @endif

                            <div class="min-w-0">
                                <h2 class="text-lg font-bold leading-tight text-ink-900">{{ $user->name }}</h2>
                                <div class="mt-1.5 flex flex-wrap gap-1.5">
                                    <x-badge color="teal">{{ $user->rol_label }}</x-badge>
                                    <x-badge color="sky">{{ $meses[$mes] }} {{ $anio }}</x-badge>
                                </div>
                                <p class="mt-2 text-xs text-ink-500">
                                    DNI {{ $user->dni }} · {{ $user->especialidad ?: 'Especialidad por completar' }}
                                </p>
                            </div>
                        </div>

                        <div class="lg:col-span-4">
                            <div class="flex items-end justify-between gap-3">
                                <div>
                                    <p class="stat-label">Horas asignadas</p>
                                    <p class="stat-number">{{ $horasTexto }} <span class="text-lg font-semibold text-ink-400">/ 150 h</span></p>
                                </div>
                                <span class="text-xs font-bold {{ $totalHoras >= 150 ? 'text-brand-700' : 'text-amber-700' }}">
                                    {{ $turnosAsignados }} {{ $turnosAsignados === 1 ? 'turno' : 'turnos' }}
                                </span>
                            </div>
                            <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-ink-100" role="progressbar" aria-valuemin="0" aria-valuemax="150" aria-valuenow="{{ $totalHoras }}" aria-label="Avance de horas del trabajador">
                                <div class="h-full rounded-full bg-gradient-to-r from-brand-400 to-brand-600 transition-all duration-500" style="width: {{ $progreso }}%"></div>
                            </div>
                            <p class="mt-2 text-[11px] text-ink-400">
                                {{ $totalHoras >= 150 ? 'Cumple la meta mensual de 150 horas.' : 'Aún no se alcanzan las 150 horas.' }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2 lg:col-span-3 lg:justify-end">
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
                    <div class="border-t border-ink-100 bg-ink-50/40 px-3 py-3">
                        <div class="flex gap-1.5 overflow-x-auto">
                            @foreach ($meses as $numero => $nombre)
                                <a
                                    href="{{ route('admin.horarios.index', array_filter(['user_id' => $user->id, 'anio' => $anio, 'mes' => $numero, 'rol' => $rol !== 'todos' ? $rol : null])) }}"
                                    class="shrink-0 rounded-full px-3 py-1.5 text-[11px] font-bold transition-colors {{ $mes === $numero ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-ink-500 ring-1 ring-ink-100 hover:text-brand-700' }}"
                                    @if ($mes === $numero) aria-current="page" @endif
                                >
                                    {{ $nombre }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- Calendario --}}
                <section class="card mt-6 p-6" data-reveal>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <span class="eyebrow">Calendario del mes</span>
                            <h2 class="mt-1 text-xl font-bold tracking-tight text-ink-900">{{ $meses[$mes] }} {{ $anio }}</h2>
                            <p class="mt-1 text-xs leading-relaxed text-ink-500">
                                Turnos asignados a {{ $user->name }}. Los domingos no se atiende y los sábados solo se trabaja de 07:00 a 15:00.
                            </p>
                        </div>
                        <x-badge :color="$totalHoras >= 150 ? 'teal' : 'amber'">
                            {{ $totalHoras >= 150 ? '150 h cumplidas' : 'Meta: 150 h' }}
                        </x-badge>
                    </div>

                    <div class="mt-6">
                        @if ($turnosAsignados === 0)
                            <x-empty-state
                                icon="calendar"
                                title="Sin horario generado"
                                description="Genera el horario de este mes. El sistema distribuirá turnos aleatorios y garantizará exactamente 150 horas."
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
                <section class="card mt-6 border-brand-100 bg-brand-50/50 p-5" data-reveal>
                    <div class="flex items-start gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-brand-700 shadow-sm">
                            <x-icon name="info" class="h-5 w-5" />
                        </span>
                        <div class="text-xs leading-relaxed text-ink-600">
                            <p class="font-bold text-ink-900">Reglas del horario mensual</p>
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
@endsection
