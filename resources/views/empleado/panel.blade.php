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
                <x-icon name="home" class="h-4 w-4" />
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

    <div class="container-page py-6 md:py-9">
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

        <div class="grid gap-6 lg:grid-cols-12">
            <div class="space-y-6 lg:col-span-8">
                {{-- Resumen mensual --}}
                <section class="grid border-y-2 border-ink-950 bg-paper-50 sm:grid-cols-3" data-reveal>
                    <div class="border-b border-ink-200 p-4 sm:border-b-0 sm:border-r">
                        <p class="mono-label">Horas asignadas</p>
                        <div class="mt-2 flex items-baseline gap-1.5">
                            <span class="stat-number">{{ $horasTexto }}</span>
                            <span class="font-mono text-[11px] font-semibold uppercase tracking-[0.04em] text-ink-500">de 150 h</span>
                        </div>
                        <p class="mt-1 text-xs text-ink-500">{{ $meses[$mes] }} {{ $anio }}</p>
                    </div>
                    <div class="border-b border-ink-200 p-4 sm:border-b-0 sm:border-r">
                        <p class="mono-label">Turnos del mes</p>
                        <p class="stat-number mt-2">{{ $turnosAsignados }}</p>
                        <p class="mt-1 text-xs text-ink-500">Mañana, tarde, día completo y sábados</p>
                    </div>
                    <div class="p-4">
                        <p class="mono-label">Meta mensual</p>
                        <p class="mt-2 flex items-center gap-2">
                            <span class="badge {{ $cumpleMeta ? 'badge-teal' : 'badge-amber' }}">
                                {{ $cumpleMeta ? 'Cumplida' : 'En curso' }}
                            </span>
                        </p>
                        <div class="mt-3 h-2 w-full bg-ink-100" role="progressbar" aria-valuemin="0" aria-valuemax="150" aria-valuenow="{{ $totalHoras }}" aria-label="Avance de horas mensuales">
                            <div class="h-full bg-brand-700 transition-[width] duration-500" style="width: {{ $progreso }}%"></div>
                        </div>
                        <p class="mt-1.5 font-mono text-[9px] uppercase tracking-[0.05em] text-ink-500">
                            {{ $cumpleMeta ? '150 h completadas' : 'Faltan ' . rtrim(rtrim(number_format(150 - $totalHoras, 1, '.', ''), '0'), '.') . ' h' }}
                        </p>
                    </div>
                </section>

                {{-- Calendario --}}
                <section class="border border-ink-300 bg-paper-50" data-reveal>
                    <div class="flex flex-col gap-4 border-b-2 border-ink-950 p-4 sm:flex-row sm:items-end sm:justify-between sm:p-5">
                        <div>
                            <p class="mono-label">Calendario mensual</p>
                            <h2 class="mt-1 font-display text-xl font-bold text-ink-950">{{ $meses[$mes] }} {{ $anio }}</h2>
                            <p class="mt-1 text-xs leading-relaxed text-ink-600">Turnos asignados por administración. Los domingos no hay atención.</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <a
                                href="{{ route('empleado.panel', ['anio' => $mesAnterior->year, 'mes' => $mesAnterior->month]) }}"
                                class="btn btn-secondary btn-sm"
                                aria-label="Ver el mes anterior"
                            >
                                <x-icon name="arrow-left" class="h-4 w-4" />
                            </a>
                            <a href="{{ route('empleado.panel') }}" class="btn btn-ghost btn-sm">Este mes</a>
                            <a
                                href="{{ route('empleado.panel', ['anio' => $mesSiguiente->year, 'mes' => $mesSiguiente->month]) }}"
                                class="btn btn-secondary btn-sm"
                                aria-label="Ver el mes siguiente"
                            >
                                <x-icon name="arrow-right" class="h-4 w-4" />
                            </a>
                        </div>
                    </div>

                    <div class="p-3 sm:p-5">
                        @include('hampi.partials.calendario', ['anio' => $anio, 'mes' => $mes, 'horarios' => $horarios])
                    </div>
                </section>

                {{-- Edición de perfil --}}
                <section class="border border-ink-300 bg-paper-50" id="editar-perfil" data-reveal>
                    <div class="border-b-2 border-ink-950 p-4 sm:p-5">
                        <p class="mono-label">Ficha profesional</p>
                        <h2 class="mt-1 flex items-center gap-2 font-display text-xl font-bold text-ink-950">
                            <x-icon name="settings" class="h-5 w-5 text-brand-700" />
                            Editar mi perfil
                        </h2>
                        <p class="mt-1 text-xs leading-relaxed text-ink-600">
                            Actualiza tu foto y tus datos profesionales. Tu nombre, DNI y rol son gestionados por administración.
                        </p>
                    </div>

                    <form action="{{ route('empleado.perfil.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5 p-4 sm:p-5">
                        @csrf
                        <input type="hidden" name="anio" value="{{ $anio }}">
                        <input type="hidden" name="mes" value="{{ $mes }}">

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="email" class="label">Correo</label>
                                <input type="email" name="email" id="email" required value="{{ old('email', $user->email) }}" class="input">
                                @error('email') <p class="mt-1 text-xs text-stamp-700">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="telefono" class="label">Teléfono</label>
                                <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $user->telefono) }}" placeholder="Ej. 987 654 321" class="input">
                                @error('telefono') <p class="mt-1 text-xs text-stamp-700">{{ $message }}</p> @enderror
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
                            @error('especialidad') <p class="mt-1 text-xs text-stamp-700">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="biografia" class="label">Biografía</label>
                            <textarea name="biografia" id="biografia" rows="4" class="input" placeholder="Cuéntale a la comunidad sobre tu formación y tu trabajo.">{{ old('biografia', $user->biografia) }}</textarea>
                            @error('biografia') <p class="mt-1 text-xs text-stamp-700">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="titulos" class="label">Títulos</label>
                                <textarea name="titulos" id="titulos" rows="4" class="input" placeholder="Ej. Médico Cirujano, Universidad Nacional de San Agustín">{{ old('titulos', $user->titulos) }}</textarea>
                                @error('titulos') <p class="mt-1 text-xs text-stamp-700">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="experiencia" class="label">Experiencia</label>
                                <textarea name="experiencia" id="experiencia" rows="4" class="input" placeholder="Ej. 5 años en atención primaria y control CRED.">{{ old('experiencia', $user->experiencia) }}</textarea>
                                @error('experiencia') <p class="mt-1 text-xs text-stamp-700">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="foto_perfil" class="label">Foto de perfil</label>
                            <input
                                type="file"
                                name="foto_perfil"
                                id="foto_perfil"
                                accept="image/jpeg,image/png,image/webp"
                                class="block w-full cursor-pointer border border-dashed border-ink-300 bg-paper-100 px-3 py-3 text-xs text-ink-600 file:mr-3 file:border-0 file:bg-brand-700 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-paper-50 hover:file:bg-brand-800"
                            >
                            <p class="mt-1.5 font-mono text-[9px] uppercase tracking-[0.05em] text-ink-500">JPG, PNG o WebP · máximo 2 MB.</p>
                            @error('foto_perfil') <p class="mt-1 text-xs text-stamp-700">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex justify-end border-t border-ink-200 pt-4">
                            <button type="submit" class="btn btn-primary">
                                <x-icon name="check" class="h-4 w-4" />
                                Guardar cambios
                            </button>
                        </div>
                    </form>
                </section>
            </div>

            <aside class="space-y-6 lg:col-span-4">
                <section class="border border-ink-300 bg-paper-50" data-reveal>
                    <div class="relative border-b border-ink-300 bg-brand-900 p-5 text-paper-50">
                        <div class="pointer-events-none absolute inset-0 bg-dot-grid opacity-25" aria-hidden="true"></div>
                        <div class="relative flex items-start gap-4">
                            @if ($user->foto_url)
                                <img
                                    src="{{ $user->foto_url }}"
                                    alt="Foto de perfil de {{ $user->name }}"
                                    class="h-20 w-20 shrink-0 border-2 border-paper-50 object-cover"
                                >
                            @else
                                <span class="grid h-20 w-20 shrink-0 place-items-center border-2 border-paper-50 bg-brand-700 font-display text-2xl font-bold text-paper-50">
                                    {{ $user->iniciales() }}
                                </span>
                            @endif

                            <div class="min-w-0">
                                <h2 class="font-display text-lg font-bold leading-tight">{{ $user->name }}</h2>
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    <span class="border border-paper-50/30 px-1.5 py-1 font-mono text-[9px] uppercase tracking-[0.05em] text-paper-50">{{ $user->rol_label }}</span>
                                    <span class="border border-paper-50/30 px-1.5 py-1 font-mono text-[9px] uppercase tracking-[0.05em] text-paper-50">{{ $user->activo ? 'Activo' : 'Inactivo' }}</span>
                                </div>
                                <p class="mt-2 text-xs leading-relaxed text-paper-100/70">
                                    {{ $user->especialidad ?: 'Especialidad por completar' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <dl class="divide-y divide-ink-200 p-5 text-sm">
                        <div class="flex items-center justify-between gap-3 py-2.5 first:pt-0">
                            <dt class="text-ink-600">DNI</dt>
                            <dd class="font-mono text-xs font-semibold text-ink-800">{{ $user->dni }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <dt class="text-ink-600">Correo</dt>
                            <dd class="truncate font-semibold text-ink-800">{{ $user->email }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <dt class="text-ink-600">Teléfono</dt>
                            <dd class="font-semibold text-ink-800">{{ $user->telefono ?: 'No registrado' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3 py-2.5 last:pb-0">
                            <dt class="text-ink-600">Establecimiento</dt>
                            <dd class="text-right font-semibold text-ink-800">{{ $user->establecimiento }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="border-t-2 border-ink-950" data-reveal>
                    <h2 class="border-b border-ink-300 py-3 font-display text-base font-bold text-ink-950">Mi perfil profesional</h2>
                    <div class="divide-y divide-ink-200">
                        <div class="py-4 first:pt-3">
                            <h3 class="mono-label">Biografía</h3>
                            <p class="mt-1.5 whitespace-pre-line text-xs leading-relaxed text-ink-600">
                                {{ $user->biografia ?: 'Aún no registraste tu biografía. Puedes hacerlo desde el formulario de edición.' }}
                            </p>
                        </div>
                        <div class="py-4">
                            <h3 class="mono-label">Títulos</h3>
                            <p class="mt-1.5 whitespace-pre-line text-xs leading-relaxed text-ink-600">
                                {{ $user->titulos ?: 'Sin títulos registrados.' }}
                            </p>
                        </div>
                        <div class="py-4 last:pb-0">
                            <h3 class="mono-label">Experiencia</h3>
                            <p class="mt-1.5 whitespace-pre-line text-xs leading-relaxed text-ink-600">
                                {{ $user->experiencia ?: 'Sin experiencia registrada.' }}
                            </p>
                        </div>
                    </div>
                </section>

                <section class="border border-maize-300 bg-maize-50 p-5" data-reveal>
                    <p class="flex items-center gap-2 font-mono text-[10px] font-semibold uppercase tracking-[0.08em] text-maize-900">
                        <x-icon name="info" class="h-4 w-4" />
                        Recordatorio
                    </p>
                    <p class="mt-2 text-xs leading-relaxed text-maize-900/90">
                        La meta mensual es de 150 horas. Si detectas un error en tu horario, comunícate con administración.
                    </p>
                </section>
            </aside>
        </div>
    </div>
@endsection
