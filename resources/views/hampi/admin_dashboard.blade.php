@extends('hampi.layout')

@section('title', 'Panel de gestión · Hampi')
@section('meta_description', 'Panel de gestión de médicos y pizarrín de vacunas de la Microred Socabaya.')

@section('content')
    @php
        $vacunasDisponibles = $vacunas->where('stock_estado', 'Disponible')->count();
        $vacunasUrgentes = $vacunas->where('es_urgente', true)->count();
    @endphp
    <x-page-board>
        <x-slot:hero>
            <x-page-hero
                eyebrow="Administración"
                title="Panel de gestión"
                :description="'Centro de Salud San Martín de Socabaya · Responsable: ' . (Auth::user()->name ?? 'Personal de salud')"
                icon="settings"
            >
                <x-slot:actions>
                    <a href="/" class="btn btn-primary btn-sm">
                        <x-icon name="external-link" class="h-4 w-4" />
                        Ver portal público
                    </a>
                    <form action="/personal/logout" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">
                            <x-icon name="log-out" class="h-4 w-4" />
                            Cerrar sesión
                        </button>
                    </form>
                </x-slot:actions>
            </x-page-hero>
        </x-slot:hero>

        <x-slot:nav>
            @include('hampi.partials.admin-nav')
        </x-slot:nav>

        <div class="container-page py-6 md:py-9 ">
            @if ($errors->any())
                <div role="alert" class="mb-6 flex items-start gap-3 border border-stamp-300 bg-stamp-50 p-4 text-sm text-stamp-800" data-reveal>
                    <x-icon name="alert-circle" class="mt-0.5 h-5 w-5 shrink-0" />
                    <div>
                        <p class="font-bold">Revisa los datos del formulario</p>
                        <ul class="mt-1 list-inside list-disc space-y-0.5 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- Resumen del día: una sola franja, no cinco tarjetas iguales. --}}
            <section class="grid border-y-2 border-ink-950 bg-paper-50 sm:grid-cols-2 xl:grid-cols-4" data-reveal>
                <div class="border-b border-ink-200 p-4 sm:border-r xl:border-b-0">
                    <p class="mono-label">Médicos en rol de hoy</p>
                    <p class="stat-number mt-2">{{ $medicosHoy->count() }}</p>
                    <p class="mt-1 text-xs text-ink-500">Agenda generada y ajustes manuales</p>
                </div>
                <div class="border-b border-ink-200 p-4 xl:border-b-0 xl:border-r">
                    <p class="mono-label">Atendiendo ahora</p>
                    <p class="stat-number mt-2">{{ $turnosAtendiendo }}</p>
                    <p class="mt-1 text-xs text-ink-500">Estado actual del rol</p>
                </div>
                <div class="border-b border-ink-200 p-4 sm:border-b-0 sm:border-r">
                    <p class="mono-label">Vacunas disponibles</p>
                    <p class="stat-number mt-2">{{ $vacunasDisponibles }}</p>
                    <p class="mt-1 text-xs text-ink-500">{{ $vacunasUrgentes }} avisos marcados como urgentes</p>
                </div>
                <div class="p-4">
                    <p class="mono-label">Personal de salud</p>
                    <p class="stat-number mt-2">{{ $personalCount }}</p>
                    <p class="mt-1 text-xs text-ink-500">{{ $horariosMes }} turnos programados este mes</p>
                </div>
            </section>

            <div class="mt-6 grid gap-6 xl:grid-cols-12">
                {{-- Rol de hoy --}}
                <section class="border border-ink-300 bg-paper-50 xl:col-span-7" data-reveal>
                    <div class="flex flex-wrap items-end justify-between gap-3 border-b-2 border-ink-950 p-4 sm:p-5">
                        <div>
                            <p class="mono-label">Parte de hoy</p>
                            <h2 class="mt-1 font-display text-xl font-bold text-ink-950">Rol de médicos y turnos</h2>
                        </div>
                        <a href="{{ route('admin.horarios.index') }}" class="link-arrow text-xs">
                            Gestionar horarios
                            <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                        </a>
                    </div>

                    <div class="p-4 sm:p-5">
                        @if ($medicosHoy->isEmpty())
                            <x-empty-state class="border-0 px-0 py-6" icon="stethoscope" title="Sin médicos registrados" description="No hay médicos programados ni manuales para hoy. Genera un horario o agrega un médico visitante." />
                        @else
                            <div class="divide-y divide-ink-200">
                                @foreach ($medicosHoy as $turno)
                                    <div class="flex flex-col gap-3 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex items-start gap-3">
                                            <span class="grid h-9 w-9 shrink-0 place-items-center border border-ink-300 bg-paper-100 font-mono text-[10px] font-semibold text-brand-800">
                                                {{ mb_strtoupper(mb_substr($turno->medico_nombre, 0, 1)) }}
                                            </span>
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h3 class="font-display text-sm font-bold text-ink-950">{{ $turno->medico_nombre }}</h3>
                                                    <span class="badge {{ in_array($turno->turno, ['Mañana', 'Todo el día', 'Sábado'], true) ? 'badge-amber' : 'badge-slate' }}">{{ $turno->turno }}</span>
                                                    <span class="badge {{ $turno->fuente === 'horario' ? 'badge-teal' : 'badge-slate' }}">{{ $turno->fuente === 'horario' ? 'Horario' : 'Manual' }}</span>
                                                </div>
                                                <p class="mt-1 text-xs leading-relaxed text-ink-600">
                                                    {{ $turno->especialidad }} <span class="text-ink-300">/</span>
                                                    <span class="font-mono text-[10px] text-ink-700">{{ $turno->consultorio }}</span>
                                                    @if ($turno->hora_inicio && $turno->hora_fin)
                                                        <span class="text-ink-300">/</span> <span class="font-mono text-[10px]">{{ $turno->hora_inicio }}–{{ $turno->hora_fin }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                            @if ($turno->fuente === 'manual' && $turno->manual_id)
                                                <form action="/admin/turnos/{{ $turno->manual_id }}/estado" method="POST">
                                                    @csrf
                                                    <select
                                                        name="estado"
                                                        onchange="this.form.submit()"
                                                        aria-label="Cambiar estado de {{ $turno->medico_nombre }}"
                                                        class="min-h-10 cursor-pointer border border-ink-300 bg-paper-50 px-2.5 text-xs font-semibold text-ink-700"
                                                    >
                                                        <option value="Atendiendo" {{ $turno->estado === 'Atendiendo' ? 'selected' : '' }}>Atendiendo</option>
                                                        <option value="Turno Completo" {{ $turno->estado === 'Turno Completo' ? 'selected' : '' }}>Turno completo</option>
                                                        <option value="Reprogramado" {{ $turno->estado === 'Reprogramado' ? 'selected' : '' }}>Reprogramado</option>
                                                    </select>
                                                </form>

                                                <form action="/admin/turnos/{{ $turno->manual_id }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar a este médico del rol?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex h-10 w-10 items-center justify-center border border-ink-300 text-ink-500 transition-colors hover:border-stamp-300 hover:bg-stamp-50 hover:text-stamp-700" title="Eliminar médico" aria-label="Eliminar a {{ $turno->medico_nombre }} del rol">
                                                        <x-icon name="trash-2" class="h-4 w-4" />
                                                    </button>
                                                </form>
                                            @else
                                                <span class="badge {{ $turno->estado === 'Atendiendo' ? 'badge-teal' : ($turno->estado === 'Finalizado' ? 'badge-slate' : 'badge-sky') }}">{{ $turno->estado }}</span>
                                                <a href="{{ route('admin.horarios.index', ['user_id' => $turno->user_id, 'anio' => now()->year, 'mes' => now()->month]) }}" class="link-arrow text-xs">
                                                    Ver horario
                                                    <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <details class="group mt-5 border border-ink-300 bg-paper-100">
                            <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-3 px-3.5 py-2.5 text-xs font-semibold text-ink-800">
                                <span class="flex items-center gap-2">
                                    <x-icon name="user-plus" class="h-4 w-4 text-brand-700" />
                                    Registrar médico visitante
                                </span>
                                <x-icon name="plus" class="h-4 w-4 text-ink-500 transition-transform group-open:rotate-45" />
                            </summary>

                            <form action="/admin/turnos/guardar" method="POST" class="space-y-3.5 border-t border-ink-300 p-3.5">
                                @csrf
                                <p class="text-[11px] leading-relaxed text-ink-600">
                                    Para médicos con cuenta y horario mensual, usa
                                    <a href="{{ route('admin.horarios.index') }}" class="font-semibold text-brand-700 hover:text-brand-800">Horarios</a>.
                                    Este formulario es para médicos visitantes o ajustes puntuales.
                                </p>

                                <div>
                                    <label for="medico_nombre" class="label">Nombre del médico</label>
                                    <input type="text" name="medico_nombre" id="medico_nombre" required value="{{ old('medico_nombre') }}" placeholder="Ej. Dr. Carlos Valdivia" class="input">
                                </div>

                                <div>
                                    <label for="especialidad" class="label">Especialidad</label>
                                    <select name="especialidad" id="especialidad" required class="input">
                                        <option value="Medicina General">Medicina General</option>
                                        <option value="Pediatría">Pediatría</option>
                                        <option value="Obstetricia / Ginecología">Obstetricia / Ginecología</option>
                                        <option value="Odontología">Odontología</option>
                                        <option value="Control CRED y Vacunación">Control CRED y Vacunación</option>
                                        <option value="Psicología">Psicología</option>
                                        <option value="Laboratorio y Toma de Muestras">Laboratorio y Toma de Muestras</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label for="turno" class="label">Turno</label>
                                        <select name="turno" id="turno" class="input">
                                            <option value="Mañana">Mañana</option>
                                            <option value="Tarde">Tarde</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="consultorio" class="label">Consultorio</label>
                                        <input type="text" name="consultorio" id="consultorio" required value="{{ old('consultorio') }}" placeholder="Ej. Cons. 3" class="input">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-sm w-full">
                                    <x-icon name="plus" class="h-4 w-4" />
                                    Agregar al rol de hoy
                                </button>
                            </form>
                        </details>
                    </div>
                </section>

                {{-- Pizarrín --}}
                <section class="border border-ink-300 bg-paper-50 xl:col-span-5" data-reveal>
                    <div class="flex flex-wrap items-end justify-between gap-3 border-b-2 border-ink-950 p-4 sm:p-5">
                        <div>
                            <p class="mono-label">Tablón</p>
                            <h2 class="mt-1 font-display text-xl font-bold text-ink-950">Pizarrín de vacunas</h2>
                        </div>
                        <a href="/pizarrin-vacunas" class="link-arrow text-xs">
                            Ver público
                            <x-icon name="external-link" class="h-3.5 w-3.5" />
                        </a>
                    </div>

                    <div class="p-4 sm:p-5">
                        @if ($vacunas->isEmpty())
                            <x-empty-state class="border-0 px-0 py-6" icon="syringe" title="Sin avisos publicados" description="Publica el primer aviso de vacuna desde el formulario." />
                        @else
                            <div class="divide-y divide-ink-200">
                                @foreach ($vacunas as $vacuna)
                                    <div class="flex items-start justify-between gap-3 py-4 first:pt-0 last:pb-0">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h3 class="font-display text-sm font-bold text-ink-950">{{ $vacuna->titulo }}</h3>
                                                <span class="badge {{ $vacuna->stock_estado === 'Disponible' ? 'badge-teal' : ($vacuna->stock_estado === 'Por agotarse' ? 'badge-amber' : 'badge-red') }}">
                                                    {{ $vacuna->stock_estado }}
                                                </span>
                                                @if ($vacuna->es_urgente)
                                                    <span class="badge badge-amber">Urgente</span>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-xs leading-relaxed text-ink-600">
                                                <span class="font-medium text-ink-700">Grupo:</span> {{ $vacuna->grupo_objetivo }}
                                            </p>
                                            <p class="mt-0.5 text-xs leading-relaxed text-ink-600">
                                                <span class="font-medium text-ink-700">Requisito:</span> {{ $vacuna->requisitos }}
                                            </p>
                                        </div>

                                        <form action="/admin/pizarrin/{{ $vacuna->id }}" method="POST" onsubmit="return confirm('¿Seguro que deseas retirar este aviso de vacuna?')" class="shrink-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-10 w-10 items-center justify-center border border-ink-300 text-ink-500 transition-colors hover:border-stamp-300 hover:bg-stamp-50 hover:text-stamp-700" title="Retirar aviso" aria-label="Retirar el aviso {{ $vacuna->titulo }}">
                                                <x-icon name="trash-2" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <details class="group mt-5 border border-ink-300 bg-paper-100">
                            <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-3 px-3.5 py-2.5 text-xs font-semibold text-ink-800">
                                <span class="flex items-center gap-2">
                                    <x-icon name="plus" class="h-4 w-4 text-brand-700" />
                                    Publicar nuevo aviso
                                </span>
                                <x-icon name="plus" class="h-4 w-4 text-ink-500 transition-transform group-open:rotate-45" />
                            </summary>

                            <form action="/admin/pizarrin/guardar" method="POST" class="space-y-3.5 border-t border-ink-300 p-3.5">
                                @csrf

                                <div>
                                    <label for="titulo" class="label">Vacuna o campaña</label>
                                    <input type="text" name="titulo" id="titulo" required value="{{ old('titulo') }}" placeholder="Ej. Vacuna VPH escolar" class="input">
                                </div>

                                <div>
                                    <label for="grupo_objetivo" class="label">Grupo objetivo</label>
                                    <input type="text" name="grupo_objetivo" id="grupo_objetivo" required value="{{ old('grupo_objetivo') }}" placeholder="Ej. Niñas de 9 a 13 años" class="input">
                                </div>

                                <div>
                                    <label for="stock_estado" class="label">Stock</label>
                                    <select name="stock_estado" id="stock_estado" class="input">
                                        <option value="Disponible">Disponible</option>
                                        <option value="Por agotarse">Por agotarse</option>
                                        <option value="Agotado">Agotado</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="requisitos" class="label">Requisitos</label>
                                    <input type="text" name="requisitos" id="requisitos" required value="{{ old('requisitos') }}" placeholder="Ej. DNI físico y carné" class="input">
                                </div>

                                <label class="flex min-h-11 items-center gap-2.5 border border-ink-300 bg-paper-50 px-3">
                                    <input type="checkbox" name="es_urgente" id="es_urgente" value="1" class="h-4 w-4 border-ink-400 text-brand-700 focus:ring-brand-500">
                                    <span class="text-xs font-medium text-ink-700">Marcar como urgente / campaña</span>
                                </label>

                                <button type="submit" class="btn btn-primary btn-sm w-full">
                                    <x-icon name="plus" class="h-4 w-4" />
                                    Publicar en el pizarrín
                                </button>
                            </form>
                        </details>
                    </div>
                </section>
            </div>

            <div class="mt-5 flex flex-col gap-2 border-t border-ink-200 pt-3 font-mono text-[10px] uppercase tracking-[0.05em] text-ink-500 sm:flex-row sm:items-center sm:justify-between">
                <p>Los cambios se reflejan en el portal público.</p>
                <p>Demo académica · Datos simulados</p>
            </div>
        </div>
    </x-page-board>
@endsection
