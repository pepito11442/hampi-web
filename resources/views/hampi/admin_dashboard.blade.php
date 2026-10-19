@extends('hampi.layout')

@section('title', 'Panel de gestión · Hampi')
@section('meta_description', 'Panel de gestión de médicos y pizarrín de vacunas de la Microred Socabaya.')

@section('content')
    @php
        $turnosAtendiendo = $turnos->where('estado', 'Atendiendo')->count();
        $vacunasDisponibles = $vacunas->where('stock_estado', 'Disponible')->count();
        $vacunasUrgentes = $vacunas->where('es_urgente', true)->count();
    @endphp

    <x-page-hero
        eyebrow="Enfermería / Admisión"
        title="Panel de gestión hospitalaria"
        :description="'Centro de Salud San Martín de Socabaya · Responsable: ' . (Auth::user()->name ?? 'Personal de salud')"
        icon="settings"
    >
        <x-slot:actions>
            <a href="/medicos-hoy" class="btn btn-secondary btn-sm">
                <x-icon name="stethoscope" class="h-4 w-4 text-brand-600" />
                Ver rol público
            </a>
            <a href="/pizarrin-vacunas" class="btn btn-secondary btn-sm">
                <x-icon name="syringe" class="h-4 w-4 text-brand-600" />
                Ver pizarrín público
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

    <div class="container-page py-8 md:py-12">
        @if ($errors->any())
            <div role="alert" class="mb-6 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700" data-reveal>
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

        {{-- Indicadores --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="card flex items-center gap-4 p-5" data-reveal>
                <span class="grid h-11 w-11 place-items-center rounded-2xl bg-brand-50 text-brand-700">
                    <x-icon name="users" class="h-5 w-5" />
                </span>
                <div>
                    <p class="stat-number">{{ $turnos->count() }}</p>
                    <p class="stat-label">Médicos en rol</p>
                </div>
            </div>
            <div class="card flex items-center gap-4 p-5" data-reveal style="--reveal-delay: 60ms">
                <span class="grid h-11 w-11 place-items-center rounded-2xl bg-sky-50 text-sky-700">
                    <x-icon name="activity" class="h-5 w-5" />
                </span>
                <div>
                    <p class="stat-number">{{ $turnosAtendiendo }}</p>
                    <p class="stat-label">Atendiendo ahora</p>
                </div>
            </div>
            <div class="card flex items-center gap-4 p-5" data-reveal style="--reveal-delay: 120ms">
                <span class="grid h-11 w-11 place-items-center rounded-2xl bg-amber-50 text-amber-700">
                    <x-icon name="syringe" class="h-5 w-5" />
                </span>
                <div>
                    <p class="stat-number">{{ $vacunasDisponibles }}</p>
                    <p class="stat-label">Vacunas disponibles</p>
                </div>
            </div>
            <div class="card flex items-center gap-4 p-5" data-reveal style="--reveal-delay: 180ms">
                <span class="grid h-11 w-11 place-items-center rounded-2xl bg-rose-50 text-rose-700">
                    <x-icon name="sparkles" class="h-5 w-5" />
                </span>
                <div>
                    <p class="stat-number">{{ $vacunasUrgentes }}</p>
                    <p class="stat-label">Campañas urgentes</p>
                </div>
            </div>
        </div>

        {{-- Gestión de médicos --}}
        <section class="card mt-6 p-6" data-reveal>
            <div class="border-b border-ink-100 pb-4">
                <h2 class="flex items-center gap-2 text-base font-bold text-ink-900">
                    <x-icon name="stethoscope" class="h-5 w-5 text-brand-600" />
                    Gestión de médicos y turnos del día
                </h2>
                <p class="mt-1 text-xs text-ink-500">Agrega especialistas o actualiza su disponibilidad en tiempo real.</p>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                {{-- Formulario --}}
                <div class="rounded-2xl border border-ink-100 bg-ink-50/60 p-4">
                    <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-ink-600">
                        <x-icon name="user-plus" class="h-4 w-4 text-brand-600" />
                        Registrar nuevo médico
                    </h3>

                    <form action="/admin/turnos/guardar" method="POST" class="mt-4 space-y-3.5">
                        @csrf

                        <div>
                            <label for="medico_nombre" class="label">Nombre del médico</label>
                            <input type="text" name="medico_nombre" id="medico_nombre" required value="{{ old('medico_nombre') }}" placeholder="Ej. Dr. Carlos Valdivia" class="input py-2.5">
                        </div>

                        <div>
                            <label for="especialidad" class="label">Especialidad</label>
                            <select name="especialidad" id="especialidad" required class="input py-2.5">
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
                                <select name="turno" id="turno" class="input py-2.5">
                                    <option value="Mañana">Mañana</option>
                                    <option value="Tarde">Tarde</option>
                                </select>
                            </div>
                            <div>
                                <label for="consultorio" class="label">Consultorio</label>
                                <input type="text" name="consultorio" id="consultorio" required value="{{ old('consultorio') }}" placeholder="Ej. Cons. 3" class="input py-2.5">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm mt-2 w-full">
                            <x-icon name="plus" class="h-4 w-4" />
                            Agregar al rol de hoy
                        </button>
                    </form>
                </div>

                {{-- Lista --}}
                <div class="lg:col-span-2">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-ink-600">Médicos en rol hoy</h3>
                        <span class="badge badge-slate">{{ $turnos->count() }}</span>
                    </div>

                    @if ($turnos->isEmpty())
                        <x-empty-state class="px-4 py-8" icon="stethoscope" title="Sin médicos registrados" description="Agrega el primer médico del día desde el formulario." />
                    @else
                        <ul class="space-y-2.5">
                            @foreach ($turnos as $turno)
                                <li class="flex flex-col gap-3 rounded-2xl border border-ink-100 bg-white p-4 transition-colors hover:border-brand-200 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700">
                                            <x-icon name="stethoscope" class="h-4 w-4" />
                                        </span>
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h4 class="text-sm font-bold text-ink-900">{{ $turno->medico_nombre }}</h4>
                                                <span class="badge {{ $turno->turno === 'Mañana' ? 'badge-amber' : 'badge-slate' }}">{{ $turno->turno }}</span>
                                            </div>
                                            <p class="mt-0.5 text-xs text-ink-500">
                                                {{ $turno->especialidad }} · <span class="font-medium text-ink-700">{{ $turno->consultorio }}</span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2">
                                        <form action="/admin/turnos/{{ $turno->id }}/estado" method="POST">
                                            @csrf
                                            <select
                                                name="estado"
                                                onchange="this.form.submit()"
                                                aria-label="Cambiar estado de {{ $turno->medico_nombre }}"
                                                class="cursor-pointer rounded-full border px-3 py-1.5 text-xs font-semibold transition-colors {{ $turno->estado === 'Atendiendo' ? 'border-brand-200 bg-brand-50 text-brand-800' : ($turno->estado === 'Turno Completo' ? 'border-ink-200 bg-ink-50 text-ink-700' : 'border-rose-200 bg-rose-50 text-rose-700') }}"
                                            >
                                                <option value="Atendiendo" {{ $turno->estado === 'Atendiendo' ? 'selected' : '' }}>Atendiendo</option>
                                                <option value="Turno Completo" {{ $turno->estado === 'Turno Completo' ? 'selected' : '' }}>Turno Completo</option>
                                                <option value="Reprogramado" {{ $turno->estado === 'Reprogramado' ? 'selected' : '' }}>Reprogramado</option>
                                            </select>
                                        </form>

                                        <form action="/admin/turnos/{{ $turno->id }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar a este médico del rol?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-ink-200 text-ink-400 transition-colors hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600" title="Eliminar médico" aria-label="Eliminar a {{ $turno->medico_nombre }} del rol">
                                                <x-icon name="trash-2" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </section>

        {{-- Gestión del pizarrín --}}
        <section class="card mt-6 p-6" data-reveal>
            <div class="border-b border-ink-100 pb-4">
                <h2 class="flex items-center gap-2 text-base font-bold text-ink-900">
                    <x-icon name="clipboard-list" class="h-5 w-5 text-brand-600" />
                    Gestión del pizarrín digital de vacunas
                </h2>
                <p class="mt-1 text-xs text-ink-500">Publica nuevos lotes de vacunas o retira avisos cuando el stock se agote.</p>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                {{-- Formulario --}}
                <div class="rounded-2xl border border-ink-100 bg-ink-50/60 p-4">
                    <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-ink-600">
                        <x-icon name="plus" class="h-4 w-4 text-brand-600" />
                        Publicar nuevo aviso
                    </h3>

                    <form action="/admin/pizarrin/guardar" method="POST" class="mt-4 space-y-3.5">
                        @csrf

                        <div>
                            <label for="titulo" class="label">Vacuna o campaña</label>
                            <input type="text" name="titulo" id="titulo" required value="{{ old('titulo') }}" placeholder="Ej. Vacuna VPH escolar" class="input py-2.5">
                        </div>

                        <div>
                            <label for="grupo_objetivo" class="label">Grupo objetivo</label>
                            <input type="text" name="grupo_objetivo" id="grupo_objetivo" required value="{{ old('grupo_objetivo') }}" placeholder="Ej. Niñas de 9 a 13 años" class="input py-2.5">
                        </div>

                        <div>
                            <label for="stock_estado" class="label">Stock</label>
                            <select name="stock_estado" id="stock_estado" class="input py-2.5">
                                <option value="Disponible">Disponible</option>
                                <option value="Por agotarse">Por agotarse</option>
                                <option value="Agotado">Agotado</option>
                            </select>
                        </div>

                        <div>
                            <label for="requisitos" class="label">Requisitos</label>
                            <input type="text" name="requisitos" id="requisitos" required value="{{ old('requisitos') }}" placeholder="Ej. DNI físico y carné" class="input py-2.5">
                        </div>

                        <div class="flex items-center gap-2.5 rounded-xl border border-ink-100 bg-white p-3">
                            <input type="checkbox" name="es_urgente" id="es_urgente" class="h-4 w-4 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                            <label for="es_urgente" class="text-xs font-medium text-ink-600">Marcar como urgente / campaña</label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm mt-2 w-full">
                            <x-icon name="plus" class="h-4 w-4" />
                            Publicar en el pizarrín
                        </button>
                    </form>
                </div>

                {{-- Lista --}}
                <div class="lg:col-span-2">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-ink-600">Avisos publicados</h3>
                        <span class="badge badge-slate">{{ $vacunas->count() }}</span>
                    </div>

                    @if ($vacunas->isEmpty())
                        <x-empty-state class="px-4 py-8" icon="syringe" title="Sin avisos publicados" description="Publica el primer aviso de vacuna desde el formulario." />
                    @else
                        <ul class="space-y-2.5">
                            @foreach ($vacunas as $vacuna)
                                <li class="flex flex-col gap-3 rounded-2xl border border-ink-100 bg-white p-4 transition-colors hover:border-brand-200 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-start gap-3">
                                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $vacuna->es_urgente ? 'bg-amber-50 text-amber-700' : 'bg-brand-50 text-brand-700' }}">
                                            <x-icon name="syringe" class="h-4 w-4" />
                                        </span>
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h4 class="text-sm font-bold text-ink-900">{{ $vacuna->titulo }}</h4>
                                                <span class="badge {{ $vacuna->stock_estado === 'Disponible' ? 'badge-teal' : ($vacuna->stock_estado === 'Por agotarse' ? 'badge-amber' : 'badge-red') }}">
                                                    {{ $vacuna->stock_estado }}
                                                </span>
                                                @if ($vacuna->es_urgente)
                                                    <span class="badge badge-amber">Urgente</span>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-xs leading-relaxed text-ink-500">
                                                <span class="font-medium text-ink-700">Grupo:</span> {{ $vacuna->grupo_objetivo }} ·
                                                <span class="font-medium text-ink-700">Requisito:</span> {{ $vacuna->requisitos }}
                                            </p>
                                        </div>
                                    </div>

                                    <form action="/admin/pizarrin/{{ $vacuna->id }}" method="POST" onsubmit="return confirm('¿Seguro que deseas retirar este aviso de vacuna?')" class="shrink-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-ink-200 text-ink-400 transition-colors hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600" title="Retirar aviso" aria-label="Retirar el aviso {{ $vacuna->titulo }}">
                                            <x-icon name="trash-2" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection
