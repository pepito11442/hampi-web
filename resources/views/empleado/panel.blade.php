@extends('hampi.layout')

@section('title', 'Mi panel de trabajo · ' . $user->name)
@section('meta_description', 'Consulta tu horario mensual y actualiza tu perfil profesional en el panel del personal de salud de Hampi.')

@section('content')
    @php
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
        $inicioMes = \Carbon\Carbon::create($anio, $mes, 1);
        $mesAnterior = $inicioMes->copy()->subMonth();
        $mesSiguiente = $inicioMes->copy()->addMonth();
        $turnosAsignados = $horarios->count();
        $cumpleMeta = $totalHoras >= 150;
        $progreso = (int) min(100, round(($totalHoras / 150) * 100));
        $horasTexto = rtrim(rtrim(number_format($totalHoras, 1, '.', ''), '0'), '.');
    @endphp

    <x-page-hero
        eyebrow="Panel del trabajador"
        :title="'Hola, ' . $user->name"
        :description="$user->rol_label . ' · ' . ($user->especialidad ?: 'Microred Socabaya') . ' · ' . $meses[$mes] . ' ' . $anio"
        icon="user-check"
    >
        <x-slot:actions>
            @if ($user->role === \App\Models\User::ROL_DOCTOR)
                <a href="{{ route('empleado.citas.index') }}" class="btn btn-primary btn-sm">
                    <x-icon name="calendar-check" class="h-4 w-4" />
                    Mis citas
                </a>
            @endif
            <a href="/" class="btn btn-secondary btn-sm">
                <x-icon name="home" class="h-4 w-4 text-brand-600" />
                Ir al portal
            </a>
            <form action="{{ route('empleado.logout') }}" method="POST">
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

        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Perfil y resumen --}}
            <aside class="space-y-6 lg:col-span-4">
                <section class="card p-6" data-reveal>
                    <div class="flex items-start gap-4">
                        @if ($user->foto_url)
                            <img
                                src="{{ $user->foto_url }}"
                                alt="Foto de perfil de {{ $user->name }}"
                                class="h-20 w-20 shrink-0 rounded-3xl object-cover ring-4 ring-brand-50"
                            >
                        @else
                            <span class="grid h-20 w-20 shrink-0 place-items-center rounded-3xl bg-gradient-to-br from-brand-500 to-brand-700 text-2xl font-bold text-white shadow-glow">
                                {{ $user->iniciales() }}
                            </span>
                        @endif

                        <div class="min-w-0">
                            <h2 class="text-lg font-bold leading-tight text-ink-900">{{ $user->name }}</h2>
                            <div class="mt-1.5 flex flex-wrap gap-2">
                                <x-badge color="teal">{{ $user->rol_label }}</x-badge>
                                <x-badge :color="$user->activo ? 'sky' : 'red'">{{ $user->activo ? 'Activo' : 'Inactivo' }}</x-badge>
                            </div>
                            <p class="mt-2 text-xs leading-relaxed text-ink-500">
                                {{ $user->especialidad ?: 'Especialidad por completar' }}
                            </p>
                        </div>
                    </div>

                    <dl class="mt-5 space-y-2.5 text-xs">
                        <div class="flex items-center justify-between gap-3 border-t border-ink-100 pt-2.5">
                            <dt class="font-semibold text-ink-500">DNI</dt>
                            <dd class="font-bold tracking-wider text-ink-800">{{ $user->dni }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-t border-ink-100 pt-2.5">
                            <dt class="font-semibold text-ink-500">Correo</dt>
                            <dd class="truncate font-semibold text-ink-800">{{ $user->email }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-t border-ink-100 pt-2.5">
                            <dt class="font-semibold text-ink-500">Teléfono</dt>
                            <dd class="font-semibold text-ink-800">{{ $user->telefono ?: 'No registrado' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-t border-ink-100 pt-2.5">
                            <dt class="font-semibold text-ink-500">Establecimiento</dt>
                            <dd class="text-right font-semibold text-ink-800">{{ $user->establecimiento }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="card p-6" data-reveal style="--reveal-delay: 60ms">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="stat-label">Horas de {{ $meses[$mes] }}</p>
                            <p class="stat-number">{{ $horasTexto }} h</p>
                        </div>
                        <x-badge :color="$cumpleMeta ? 'teal' : 'amber'">
                            {{ $cumpleMeta ? 'Meta cumplida' : 'Meta: 150 h' }}
                        </x-badge>
                    </div>

                    <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-ink-100" role="progressbar" aria-valuemin="0" aria-valuemax="150" aria-valuenow="{{ $totalHoras }}" aria-label="Avance de horas mensuales">
                        <div class="h-full rounded-full bg-gradient-to-r from-brand-400 to-brand-600 transition-all duration-500" style="width: {{ $progreso }}%"></div>
                    </div>

                    <p class="mt-2 text-[11px] text-ink-400">
                        {{ $turnosAsignados }} {{ $turnosAsignados === 1 ? 'turno asignado' : 'turnos asignados' }} · 150 h mensuales
                    </p>

                    @if (! $cumpleMeta)
                        <p class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] leading-relaxed text-amber-800">
                            Te faltan {{ rtrim(rtrim(number_format(150 - $totalHoras, 1, '.', ''), '0'), '.') }} h para completar la meta mensual.
                        </p>
                    @endif
                </section>

                <section class="card p-6" data-reveal style="--reveal-delay: 120ms">
                    <h2 class="flex items-center gap-2 text-sm font-bold text-ink-900">
                        <x-icon name="user" class="h-4 w-4 text-brand-600" />
                        Mi perfil profesional
                    </h2>

                    <div class="mt-4 space-y-4 text-sm">
                        <div>
                            <h3 class="text-[10px] font-bold uppercase tracking-[0.16em] text-ink-400">Biografía</h3>
                            <p class="mt-1 whitespace-pre-line text-xs leading-relaxed text-ink-600">
                                {{ $user->biografia ?: 'Aún no registraste tu biografía. Puedes hacerlo desde el formulario de edición.' }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-[10px] font-bold uppercase tracking-[0.16em] text-ink-400">Títulos</h3>
                            <p class="mt-1 whitespace-pre-line text-xs leading-relaxed text-ink-600">
                                {{ $user->titulos ?: 'Sin títulos registrados.' }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-[10px] font-bold uppercase tracking-[0.16em] text-ink-400">Experiencia</h3>
                            <p class="mt-1 whitespace-pre-line text-xs leading-relaxed text-ink-600">
                                {{ $user->experiencia ?: 'Sin experiencia registrada.' }}
                            </p>
                        </div>
                    </div>
                </section>
            </aside>

            {{-- Horario y edición --}}
            <div class="space-y-6 lg:col-span-8">
                <section class="card p-6" data-reveal>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <span class="eyebrow">Calendario mensual</span>
                            <h2 class="mt-1 text-xl font-bold tracking-tight text-ink-900">{{ $meses[$mes] }} {{ $anio }}</h2>
                            <p class="mt-1 text-xs leading-relaxed text-ink-500">
                                Turnos asignados por administración. Los domingos no hay atención.
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <a
                                href="{{ route('empleado.panel', ['anio' => $mesAnterior->year, 'mes' => $mesAnterior->month]) }}"
                                class="btn btn-secondary btn-sm"
                                aria-label="Ver el mes anterior"
                            >
                                <x-icon name="arrow-left" class="h-4 w-4" />
                                <span class="sr-only">Mes anterior</span>
                            </a>
                            <a href="{{ route('empleado.panel') }}" class="btn btn-ghost btn-sm">Este mes</a>
                            <a
                                href="{{ route('empleado.panel', ['anio' => $mesSiguiente->year, 'mes' => $mesSiguiente->month]) }}"
                                class="btn btn-secondary btn-sm"
                                aria-label="Ver el mes siguiente"
                            >
                                <x-icon name="arrow-right" class="h-4 w-4" />
                                <span class="sr-only">Mes siguiente</span>
                            </a>
                        </div>
                    </div>

                    <div class="mt-6">
                        @include('hampi.partials.calendario', ['anio' => $anio, 'mes' => $mes, 'horarios' => $horarios])
                    </div>
                </section>

                <section class="card p-6" id="editar-perfil" data-reveal>
                    <div class="border-b border-ink-100 pb-4">
                        <h2 class="flex items-center gap-2 text-base font-bold text-ink-900">
                            <x-icon name="settings" class="h-5 w-5 text-brand-600" />
                            Editar mi perfil
                        </h2>
                        <p class="mt-1 text-xs leading-relaxed text-ink-500">
                            Actualiza tu foto y tus datos profesionales. Tu nombre, DNI y rol son gestionados por administración.
                        </p>
                    </div>

                    <form action="{{ route('empleado.perfil.update') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-5">
                        @csrf
                        <input type="hidden" name="anio" value="{{ $anio }}">
                        <input type="hidden" name="mes" value="{{ $mes }}">

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="email" class="label">Correo</label>
                                <input type="email" name="email" id="email" required value="{{ old('email', $user->email) }}" class="input">
                                @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="telefono" class="label">Teléfono</label>
                                <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $user->telefono) }}" placeholder="Ej. 987 654 321" class="input">
                                @error('telefono') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="especialidad" class="label">Especialidad</label>
                            <input
                                type="text"
                                name="especialidad"
                                id="especialidad"
                                value="{{ old('especialidad', $user->especialidad) }}"
                                placeholder="Ej. Medicina General, Pediatría, Enfermería"
                                class="input"
                            >
                            @error('especialidad') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="biografia" class="label">Biografía</label>
                            <textarea name="biografia" id="biografia" rows="4" class="input" placeholder="Cuéntale a la comunidad sobre tu formación y tu trabajo.">{{ old('biografia', $user->biografia) }}</textarea>
                            @error('biografia') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="titulos" class="label">Títulos</label>
                                <textarea name="titulos" id="titulos" rows="4" class="input" placeholder="Ej. Médico Cirujano, Universidad Nacional de San Agustín">{{ old('titulos', $user->titulos) }}</textarea>
                                @error('titulos') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="experiencia" class="label">Experiencia</label>
                                <textarea name="experiencia" id="experiencia" rows="4" class="input" placeholder="Ej. 5 años en atención primaria y control CRED.">{{ old('experiencia', $user->experiencia) }}</textarea>
                                @error('experiencia') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="foto_perfil" class="label">Foto de perfil</label>
                            <input
                                type="file"
                                name="foto_perfil"
                                id="foto_perfil"
                                accept="image/jpeg,image/png,image/webp"
                                class="block w-full cursor-pointer rounded-2xl border border-dashed border-ink-200 bg-ink-50/50 px-4 py-3 text-xs text-ink-500 file:mr-3 file:rounded-full file:border-0 file:bg-brand-600 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-brand-700"
                            >
                            <p class="mt-1.5 text-[11px] text-ink-400">JPG, PNG o WebP · máximo 2 MB.</p>
                            @error('foto_perfil') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary">
                                <x-icon name="check" class="h-4 w-4" />
                                Guardar cambios
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
@endsection
