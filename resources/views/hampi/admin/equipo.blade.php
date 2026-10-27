@extends('hampi.layout')

@section('title', 'Equipo de salud · Administración Hampi')
@section('meta_description', 'Crea y administra las cuentas de médicos y enfermeros de la Microred Socabaya.')

@section('content')
    <x-page-board>
        <x-slot:hero>
            <x-page-hero
                eyebrow="Administración"
                title="Equipo de salud"
                description="Crea las cuentas de médicos y enfermeros. Ellos ingresan al panel del trabajador con su DNI y la contraseña que asignes."
                icon="users"
            >
                <x-slot:actions>
                    <a href="{{ route('admin.horarios.index') }}" class="btn btn-primary btn-sm">
                        <x-icon name="calendar" class="h-4 w-4" />
                        Ver horarios
                    </a>
                </x-slot:actions>
            </x-page-hero>
        </x-slot:hero>

        <x-slot:nav>
            @include('hampi.partials.admin-nav')
        </x-slot:nav>

        <div class="container-page py-6 md:py-9 ">
            @if ($errors->any())
                <div role="alert" class="mb-6 flex items-start gap-3 border border-stamp-300 bg-stamp-50 p-4 text-sm text-stamp-800">
                    <x-icon name="alert-circle" class="mt-0.5 h-5 w-5 shrink-0" />
                    <div>
                        <p class="font-bold">Revisa los datos ingresados</p>
                        <ul class="mt-1 list-inside list-disc space-y-0.5 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-ink-300 bg-paper-50 border border-ink-300 sm:p-4">
                <div class="flex gap-1 overflow-x-auto" role="group" aria-label="Filtrar equipo por rol">
                    @foreach ([
                        ['key' => 'todos', 'label' => 'Todo el equipo', 'count' => $conteos['todos'], 'icon' => 'users'],
                        ['key' => 'doctores', 'label' => 'Médicos', 'count' => $conteos['doctores'], 'icon' => 'stethoscope'],
                        ['key' => 'enfermeros', 'label' => 'Enfermería', 'count' => $conteos['enfermeros'], 'icon' => 'heart-pulse'],
                    ] as $pestana)
                        <a
                            href="{{ route('admin.equipo.index', ['rol' => $pestana['key']]) }}"
                            class="inline-flex min-h-11 shrink-0 items-center gap-2 border-b-2 px-3 text-xs font-semibold transition-colors {{ $rol === $pestana['key'] ? 'border-signal-600 bg-paper-50 text-ink-950' : 'border-transparent text-ink-600 hover:bg-paper-200 hover:text-ink-950' }}"
                            @if ($rol === $pestana['key']) aria-current="page" @endif
                        >
                            <x-icon :name="$pestana['icon']" class="h-4 w-4" />
                            {{ $pestana['label'] }}
                            <span class="border border-current px-1.5 py-0.5 font-mono text-[9px]">{{ $pestana['count'] }}</span>
                        </a>
                    @endforeach
                </div>

                <p class="hidden font-mono text-[10px] uppercase tracking-[0.05em] text-ink-500 sm:block">
                    {{ $equipo->count() }} {{ $equipo->count() === 1 ? 'cuenta listada' : 'cuentas listadas' }}
                </p>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-12 ">
                {{-- Crear cuenta --}}
                <section class="border border-ink-300 bg-paper-50 lg:col-span-5 xl:col-span-4" data-reveal>
                    <div class="border-b-2 border-ink-950 p-4">
                        <p class="mono-label">Alta de personal</p>
                        <h2 class="mt-1 flex items-center gap-2 font-display text-lg font-bold text-ink-950">
                            <x-icon name="user-plus" class="h-5 w-5 text-brand-700" />
                            Nueva cuenta de trabajador
                        </h2>
                        <p class="mt-1 text-xs leading-relaxed text-ink-600">El acceso será con el DNI y la contraseña que definas aquí.</p>
                    </div>

                    <form action="{{ route('admin.equipo.store') }}" method="POST" class="space-y-4 p-4">
                        @csrf

                        <div>
                            <label for="name" class="label">Nombre completo</label>
                            <input type="text" name="name" id="name" required value="{{ old('name') }}" placeholder="Ej. Dra. María Paredes" class="input">
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="dni" class="label">DNI</label>
                                <input type="text" name="dni" id="dni" required value="{{ old('dni') }}" inputmode="numeric" maxlength="8" pattern="[0-9]{8}" placeholder="8 dígitos" class="input font-mono tracking-[0.15em]">
                            </div>
                            <div>
                                <label for="role" class="label">Rol</label>
                                <select name="role" id="role" required class="input">
                                    <option value="enfermero" @selected(old('role', 'enfermero') === 'enfermero')>Enfermero/a</option>
                                    <option value="doctor" @selected(old('role') === 'doctor')>Médico/a</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="email" class="label">Correo</label>
                            <input type="email" name="email" id="email" required value="{{ old('email') }}" placeholder="nombre@hampi.pe" class="input">
                        </div>

                        <div>
                            <label for="especialidad" class="label">Especialidad</label>
                            <input type="text" name="especialidad" id="especialidad" value="{{ old('especialidad') }}" placeholder="Ej. Medicina General, Pediatría, Enfermería" class="input">
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="telefono" class="label">Teléfono</label>
                                <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" placeholder="Ej. 987 654 321" class="input">
                            </div>
                            <div>
                                <label for="establecimiento" class="label">Establecimiento</label>
                                <input type="text" name="establecimiento" id="establecimiento" value="{{ old('establecimiento', 'C.S. San Martín de Socabaya') }}" class="input">
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="password" class="label">Contraseña</label>
                                <input type="password" name="password" id="password" required autocomplete="new-password" placeholder="Mínimo 8 caracteres" class="input">
                            </div>
                            <div>
                                <label for="password_confirmation" class="label">Confirmar</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password" placeholder="Repite la contraseña" class="input">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-full">
                            <x-icon name="user-plus" class="h-4 w-4" />
                            Crear cuenta
                        </button>

                        <p class="border-t border-ink-200 pt-3 font-mono text-[9px] uppercase leading-relaxed tracking-[0.05em] text-ink-500">
                            El trabajador podrá editar su foto, biografía, títulos, experiencia y especialidad. El nombre, DNI y rol solo los modifica administración.
                        </p>
                    </form>
                </section>

                {{-- Listado --}}
                <section class="lg:col-span-7 bg-paper-50 p-3 sm:p-4 border border-ink-300 xl:col-span-8">
                    <div class="flex flex-wrap items-end justify-between gap-3 border-b-2 border-ink-950 pb-2">
                        <div>
                            <p class="mono-label">Directorio interno</p>
                            <h2 class="mt-1 font-display text-xl font-bold text-ink-950">Equipo registrado</h2>
                        </div>
                        <span class="font-mono text-[10px] uppercase tracking-[0.05em] text-ink-500">Microred Socabaya</span>
                    </div>

                    @if ($equipo->isEmpty())
                        <x-empty-state
                            class="mt-5"
                            icon="users"
                            title="Aún no hay trabajadores registrados"
                            description="Crea la primera cuenta de médico o enfermero con el formulario de la izquierda. Después podrás generarle su horario mensual."
                        />
                    @else
                        <div class="divide-y divide-ink-200">
                            @foreach ($equipo as $empleado)
                                <article class="py-5" data-reveal style="--reveal-delay: {{ $loop->index * 30 }}ms">
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="flex items-start gap-3">
                                            @if ($empleado->foto_url)
                                                <img src="{{ $empleado->foto_url }}" alt="Foto de {{ $empleado->name }}" class="h-12 w-12 shrink-0 border border-ink-300 object-cover">
                                            @else
                                                <span class="grid h-12 w-12 shrink-0 place-items-center border border-ink-300 bg-paper-100 font-display text-sm font-bold text-brand-800">
                                                    {{ $empleado->iniciales() }}
                                                </span>
                                            @endif

                                            <div class="min-w-0">
                                                <h3 class="font-display text-base font-bold text-ink-950">{{ $empleado->name }}</h3>
                                                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                                    <x-badge color="teal">{{ $empleado->rol_label }}</x-badge>
                                                    <x-badge :color="$empleado->activo ? 'sky' : 'red'">{{ $empleado->activo ? 'Activo' : 'Inactivo' }}</x-badge>
                                                </div>
                                                <p class="mt-1.5 text-xs text-ink-600">
                                                    {{ $empleado->especialidad ?: 'Especialidad por completar' }}
                                                </p>
                                            </div>
                                        </div>

                                        <a href="{{ route('admin.horarios.index', ['user_id' => $empleado->id]) }}" class="btn btn-secondary btn-sm shrink-0">
                                            <x-icon name="calendar" class="h-4 w-4" />
                                            Horario
                                        </a>
                                    </div>

                                    <dl class="mt-4 grid gap-x-6 gap-y-2 border-t border-ink-200 pt-3 text-xs sm:grid-cols-2">
                                        <div class="flex items-center gap-2">
                                            <dt class="mono-label">DNI</dt>
                                            <dd class="font-mono font-semibold tracking-[0.08em] text-ink-800">{{ $empleado->dni }}</dd>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <dt class="mono-label">Correo</dt>
                                            <dd class="truncate text-ink-700">{{ $empleado->email }}</dd>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <dt class="mono-label">Teléfono</dt>
                                            <dd class="text-ink-700">{{ $empleado->telefono ?: 'No registrado' }}</dd>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <dt class="mono-label">Establecimiento</dt>
                                            <dd class="truncate text-ink-700">{{ $empleado->establecimiento }}</dd>
                                        </div>
                                    </dl>

                                    <details class="group mt-3 border border-ink-300 bg-paper-100">
                                        <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-3 px-3.5 py-2.5 text-xs font-semibold text-ink-800">
                                            <span class="inline-flex items-center gap-2">
                                                <x-icon name="settings" class="h-4 w-4 text-brand-700" />
                                                Editar datos de la cuenta
                                            </span>
                                            <x-icon name="chevron-down" class="h-4 w-4 text-ink-500 transition-transform group-open:rotate-180" />
                                        </summary>

                                        <div class="border-t border-ink-300 p-3.5">
                                            <form action="{{ route('admin.equipo.update', $empleado) }}" method="POST" class="space-y-4">
                                                @csrf

                                                <div class="grid gap-4 sm:grid-cols-2">
                                                    <div>
                                                        <label for="name-{{ $empleado->id }}" class="label">Nombre completo</label>
                                                        <input type="text" name="name" id="name-{{ $empleado->id }}" required value="{{ $empleado->name }}" class="input">
                                                    </div>
                                                    <div>
                                                        <label for="dni-{{ $empleado->id }}" class="label">DNI</label>
                                                        <input type="text" name="dni" id="dni-{{ $empleado->id }}" required value="{{ $empleado->dni }}" inputmode="numeric" maxlength="8" pattern="[0-9]{8}" class="input font-mono tracking-[0.15em]">
                                                    </div>
                                                </div>

                                                <div class="grid gap-4 sm:grid-cols-2">
                                                    <div>
                                                        <label for="email-{{ $empleado->id }}" class="label">Correo</label>
                                                        <input type="email" name="email" id="email-{{ $empleado->id }}" required value="{{ $empleado->email }}" class="input">
                                                    </div>
                                                    <div>
                                                        <label for="role-{{ $empleado->id }}" class="label">Rol</label>
                                                        <select name="role" id="role-{{ $empleado->id }}" required class="input">
                                                            <option value="enfermero" @selected($empleado->role === 'enfermero' || $empleado->role === 'enfermera')>Enfermero/a</option>
                                                            <option value="doctor" @selected($empleado->role === 'doctor')>Médico/a</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="grid gap-4 sm:grid-cols-2">
                                                    <div>
                                                        <label for="especialidad-{{ $empleado->id }}" class="label">Especialidad</label>
                                                        <input type="text" name="especialidad" id="especialidad-{{ $empleado->id }}" value="{{ $empleado->especialidad }}" class="input">
                                                    </div>
                                                    <div>
                                                        <label for="telefono-{{ $empleado->id }}" class="label">Teléfono</label>
                                                        <input type="text" name="telefono" id="telefono-{{ $empleado->id }}" value="{{ $empleado->telefono }}" class="input">
                                                    </div>
                                                </div>

                                                <div>
                                                    <label for="establecimiento-{{ $empleado->id }}" class="label">Establecimiento</label>
                                                    <input type="text" name="establecimiento" id="establecimiento-{{ $empleado->id }}" value="{{ $empleado->establecimiento }}" class="input">
                                                </div>

                                                <div class="grid gap-4 sm:grid-cols-2">
                                                    <div>
                                                        <label for="password-{{ $empleado->id }}" class="label">Nueva contraseña <span class="normal-case tracking-normal text-ink-500">(opcional)</span></label>
                                                        <input type="password" name="password" id="password-{{ $empleado->id }}" autocomplete="new-password" placeholder="Dejar vacío para mantener" class="input">
                                                    </div>
                                                    <div>
                                                        <label for="password-confirmation-{{ $empleado->id }}" class="label">Confirmar contraseña</label>
                                                        <input type="password" name="password_confirmation" id="password-confirmation-{{ $empleado->id }}" autocomplete="new-password" placeholder="Repite la nueva contraseña" class="input">
                                                    </div>
                                                </div>

                                                <label class="flex min-h-11 items-center gap-2.5 border border-ink-300 bg-paper-50 px-3 text-xs font-semibold text-ink-700">
                                                    <input type="checkbox" name="activo" value="1" class="h-4 w-4 border-ink-400 text-brand-700 focus:ring-brand-500" @checked($empleado->activo)>
                                                    Cuenta activa (puede ingresar al panel)
                                                </label>

                                                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-ink-300 pt-3">
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <x-icon name="check" class="h-4 w-4" />
                                                        Guardar cambios
                                                    </button>
                                                </div>
                                            </form>

                                            <form action="{{ route('admin.equipo.destroy', $empleado) }}" method="POST" class="mt-3 flex justify-end border-t border-ink-200 pt-3" onsubmit="return confirm('¿Eliminar la cuenta de {{ $empleado->name }}? También se borrarán sus horarios. Esta acción no se puede deshacer.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <x-icon name="trash-2" class="h-4 w-4" />
                                                    Eliminar cuenta
                                                </button>
                                            </form>
                                        </div>
                                    </details>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </x-page-board>
@endsection
