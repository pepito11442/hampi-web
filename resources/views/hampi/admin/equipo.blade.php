@extends('hampi.layout')

@section('title', 'Equipo de salud · Administración Hampi')
@section('meta_description', 'Crea y administra las cuentas de médicos y enfermeros de la Microred Socabaya.')

@section('content')
    <x-page-hero
        eyebrow="Administración"
        title="Equipo de salud"
        description="Crea las cuentas de médicos y enfermeros. Ellos ingresarán al panel del trabajador con su DNI y la contraseña que asignes."
        icon="users"
    >
        <x-slot:actions>
            <a href="{{ route('admin.horarios.index') }}" class="btn btn-secondary btn-sm">
                <x-icon name="calendar" class="h-4 w-4 text-brand-600" />
                Ver horarios
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
                    <p class="font-bold">Revisa los datos ingresados</p>
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
                    href="{{ route('admin.equipo.index', ['rol' => $tab['key']]) }}"
                    class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-bold transition-colors {{ $rol === $tab['key'] ? 'border-ink-950 bg-ink-950 text-white' : 'border-ink-200 bg-white text-ink-600 hover:border-brand-300 hover:text-brand-800' }}"
                    @if ($rol === $tab['key']) aria-current="page" @endif
                >
                    <x-icon :name="$tab['icon']" class="h-4 w-4" />
                    {{ $tab['label'] }}
                    <span class="rounded-full px-1.5 py-0.5 text-[10px] {{ $rol === $tab['key'] ? 'bg-white/15 text-white' : 'bg-ink-100 text-ink-500' }}">{{ $tab['count'] }}</span>
                </a>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Crear cuenta --}}
            <section class="card p-6 lg:col-span-5 xl:col-span-4 lg:sticky lg:top-24 lg:self-start" data-reveal>
                <div class="flex items-start gap-3">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-700">
                        <x-icon name="user-plus" class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-ink-900">Nueva cuenta de trabajador</h2>
                        <p class="mt-1 text-xs leading-relaxed text-ink-500">El acceso será con el DNI y la contraseña que definas aquí.</p>
                    </div>
                </div>

                <form action="{{ route('admin.equipo.store') }}" method="POST" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="label">Nombre completo</label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}" placeholder="Ej. Dra. María Paredes" class="input">
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="dni" class="label">DNI</label>
                            <input type="text" name="dni" id="dni" required value="{{ old('dni') }}" inputmode="numeric" maxlength="8" pattern="[0-9]{8}" placeholder="8 dígitos" class="input tracking-[0.15em]">
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

                    <p class="text-[11px] leading-relaxed text-ink-400">
                        El trabajador podrá cambiar su foto, biografía, títulos, experiencia y especialidad desde su panel. El nombre, DNI y rol solo los modifica administración.
                    </p>
                </form>
            </section>

            {{-- Listado --}}
            <section class="lg:col-span-7 xl:col-span-8">
                @if ($equipo->isEmpty())
                    <x-empty-state
                        icon="users"
                        title="Aún no hay trabajadores registrados"
                        description="Crea la primera cuenta de médico o enfermero con el formulario de la izquierda. Después podrás generarle su horario mensual."
                    />
                @else
                    <div class="space-y-4">
                        @foreach ($equipo as $empleado)
                            <article class="card p-5" data-reveal style="--reveal-delay: {{ $loop->index * 40 }}ms">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="flex items-start gap-3">
                                        @if ($empleado->foto_url)
                                            <img src="{{ $empleado->foto_url }}" alt="Foto de {{ $empleado->name }}" class="h-12 w-12 shrink-0 rounded-2xl object-cover ring-2 ring-brand-50">
                                        @else
                                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 text-sm font-bold text-white">
                                                {{ $empleado->iniciales() }}
                                            </span>
                                        @endif

                                        <div class="min-w-0">
                                            <h3 class="text-sm font-bold text-ink-900">{{ $empleado->name }}</h3>
                                            <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                                <x-badge color="teal">{{ $empleado->rol_label }}</x-badge>
                                                <x-badge :color="$empleado->activo ? 'sky' : 'red'">{{ $empleado->activo ? 'Activo' : 'Inactivo' }}</x-badge>
                                            </div>
                                            <p class="mt-1.5 text-xs text-ink-500">
                                                {{ $empleado->especialidad ?: 'Especialidad por completar' }}
                                            </p>
                                        </div>
                                    </div>

                                    <a href="{{ route('admin.horarios.index', ['user_id' => $empleado->id]) }}" class="btn btn-secondary btn-sm shrink-0">
                                        <x-icon name="calendar" class="h-4 w-4 text-brand-600" />
                                        Horario
                                    </a>
                                </div>

                                <dl class="mt-4 grid gap-x-6 gap-y-2 border-t border-ink-100 pt-4 text-xs sm:grid-cols-2">
                                    <div class="flex items-center gap-2">
                                        <dt class="font-semibold text-ink-400">DNI</dt>
                                        <dd class="font-bold tracking-wider text-ink-700">{{ $empleado->dni }}</dd>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <dt class="font-semibold text-ink-400">Correo</dt>
                                        <dd class="truncate text-ink-700">{{ $empleado->email }}</dd>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <dt class="font-semibold text-ink-400">Teléfono</dt>
                                        <dd class="text-ink-700">{{ $empleado->telefono ?: 'No registrado' }}</dd>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <dt class="font-semibold text-ink-400">Establecimiento</dt>
                                        <dd class="truncate text-ink-700">{{ $empleado->establecimiento }}</dd>
                                    </div>
                                </dl>

                                <details class="group mt-4 border-t border-ink-100 pt-4">
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 rounded-xl px-1 py-1 text-xs font-bold text-brand-700 transition-colors hover:text-brand-800">
                                        <span class="inline-flex items-center gap-2">
                                            <x-icon name="settings" class="h-4 w-4" />
                                            Editar datos de la cuenta
                                        </span>
                                        <x-icon name="chevron-down" class="h-4 w-4 transition-transform group-open:rotate-180" />
                                    </summary>

                                    <form action="{{ route('admin.equipo.update', $empleado) }}" method="POST" class="mt-4 space-y-4 rounded-2xl border border-ink-100 bg-ink-50/40 p-4">
                                        @csrf

                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div>
                                                <label for="name-{{ $empleado->id }}" class="label">Nombre completo</label>
                                                <input type="text" name="name" id="name-{{ $empleado->id }}" required value="{{ $empleado->name }}" class="input">
                                            </div>
                                            <div>
                                                <label for="dni-{{ $empleado->id }}" class="label">DNI</label>
                                                <input type="text" name="dni" id="dni-{{ $empleado->id }}" required value="{{ $empleado->dni }}" inputmode="numeric" maxlength="8" pattern="[0-9]{8}" class="input tracking-[0.15em]">
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
                                                <label for="password-{{ $empleado->id }}" class="label">Nueva contraseña <span class="font-normal text-ink-400">(opcional)</span></label>
                                                <input type="password" name="password" id="password-{{ $empleado->id }}" autocomplete="new-password" placeholder="Dejar vacío para mantener" class="input">
                                            </div>
                                            <div>
                                                <label for="password-confirmation-{{ $empleado->id }}" class="label">Confirmar contraseña</label>
                                                <input type="password" name="password_confirmation" id="password-confirmation-{{ $empleado->id }}" autocomplete="new-password" placeholder="Repite la nueva contraseña" class="input">
                                            </div>
                                        </div>

                                        <label class="flex items-center gap-2.5 text-xs font-semibold text-ink-600">
                                            <input type="checkbox" name="activo" value="1" class="h-4 w-4 rounded border-ink-300 text-brand-600 focus:ring-brand-500" @checked($empleado->activo)>
                                            Cuenta activa (puede ingresar al panel)
                                        </label>

                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <x-icon name="check" class="h-4 w-4" />
                                                Guardar cambios
                                            </button>
                                        </div>
                                    </form>

                                    <form action="{{ route('admin.equipo.destroy', $empleado) }}" method="POST" class="mt-3" onsubmit="return confirm('¿Eliminar la cuenta de {{ $empleado->name }}? También se borrarán sus horarios. Esta acción no se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-2 rounded-full px-2 py-1 text-xs font-semibold text-rose-600 transition-colors hover:bg-rose-50 hover:text-rose-700">
                                            <x-icon name="trash-2" class="h-3.5 w-3.5" />
                                            Eliminar cuenta
                                        </button>
                                    </form>
                                </details>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
