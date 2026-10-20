@extends('hampi.layout')

@section('title', 'Acceso del personal de salud · Hampi')
@section('meta_description', 'Acceso para médicos y enfermeros de la Microred Socabaya con DNI y contraseña.')

@section('content')
    <div class="container-page py-10 sm:py-16">
        <div class="mx-auto grid max-w-5xl overflow-hidden rounded-[2rem] border border-ink-100 bg-white shadow-lift lg:grid-cols-2">
            {{-- Panel institucional --}}
            <div class="relative hidden overflow-hidden bg-ink-950 p-10 text-white lg:flex lg:flex-col lg:justify-between">
                <div class="pointer-events-none absolute inset-0 bg-dot-grid opacity-30" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -left-20 -top-20 h-64 w-64 rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -bottom-24 -right-16 h-72 w-72 rounded-full bg-sky-500/20 blur-3xl" aria-hidden="true"></div>

                <div class="relative">
                    <a href="/" class="inline-flex items-center gap-3" aria-label="Hampi, volver al inicio">
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-white/10 text-white ring-1 ring-white/20">
                            <x-icon name="heart-pulse" class="h-6 w-6" stroke-width="2" />
                        </span>
                        <span class="leading-tight">
                            <span class="block text-lg font-bold">Hampi</span>
                            <span class="block text-[11px] text-white/60">Salud comunitaria · Socabaya</span>
                        </span>
                    </a>

                    <h1 class="mt-12 text-3xl font-bold leading-tight tracking-tight">
                        Tu horario y tu perfil,<br>
                        <span class="text-brand-300">en un solo lugar.</span>
                    </h1>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-white/70">
                        Panel exclusivo para médicos y enfermeros de la Microred Socabaya. Consulta tu calendario mensual y mantén tu perfil profesional actualizado.
                    </p>

                    <ul class="mt-8 space-y-4 text-sm">
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-brand-500/20 text-brand-300">
                                <x-icon name="calendar-check" class="h-4 w-4" />
                            </span>
                            <span class="text-white/80">Horario mensual con turnos de mañana, tarde y sábados.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-brand-500/20 text-brand-300">
                                <x-icon name="user-cog" class="h-4 w-4" />
                            </span>
                            <span class="text-white/80">Foto, biografía, títulos y experiencia profesional.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-brand-500/20 text-brand-300">
                                <x-icon name="shield-check" class="h-4 w-4" />
                            </span>
                            <span class="text-white/80">Cuenta creada y asignada por la administración.</span>
                        </li>
                    </ul>
                </div>

                <p class="relative mt-10 text-[11px] text-white/40">
                    Microred Socabaya · GERESA Arequipa
                </p>
            </div>

            {{-- Formulario --}}
            <div class="p-6 sm:p-10">
                <div class="mx-auto max-w-sm">
                    <div class="flex items-center gap-3 lg:hidden">
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-glow">
                            <x-icon name="heart-pulse" class="h-6 w-6" stroke-width="2" />
                        </span>
                        <span class="leading-tight">
                            <span class="block text-base font-bold text-ink-900">Hampi</span>
                            <span class="block text-[11px] text-ink-400">Salud comunitaria · Socabaya</span>
                        </span>
                    </div>

                    <div class="mt-8 lg:mt-0">
                        <span class="eyebrow">Personal de salud</span>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-ink-900">Ingreso con DNI</h2>
                        <p class="mt-2 text-sm leading-relaxed text-ink-500">
                            Usa el DNI y la contraseña asignados por administración para ver tu horario y editar tu perfil.
                        </p>
                    </div>

                    <form action="{{ route('empleado.login.post') }}" method="POST" class="mt-7 space-y-5">
                        @csrf

                        @if ($errors->any())
                            <div role="alert" class="flex items-start gap-2.5 rounded-2xl border border-rose-200 bg-rose-50 p-3.5 text-xs leading-relaxed text-rose-700">
                                <x-icon name="alert-circle" class="mt-0.5 h-4 w-4 shrink-0" />
                                <span>{{ $errors->first() }}</span>
                            </div>
                        @endif

                        <div>
                            <label for="dni" class="label">DNI</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-ink-400" aria-hidden="true">
                                    <x-icon name="id-card" class="h-4 w-4" />
                                </span>
                                <input
                                    type="text"
                                    name="dni"
                                    id="dni"
                                    required
                                    value="{{ old('dni') }}"
                                    placeholder="8 dígitos"
                                    inputmode="numeric"
                                    maxlength="8"
                                    pattern="[0-9]{8}"
                                    autocomplete="username"
                                    class="input pl-11 tracking-[0.2em]"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="password" class="label">Contraseña</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-ink-400" aria-hidden="true">
                                    <x-icon name="key-round" class="h-4 w-4" />
                                </span>
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    required
                                    placeholder="••••••••"
                                    autocomplete="current-password"
                                    class="input pl-11 pr-20"
                                >
                                <button
                                    type="button"
                                    data-password-toggle="password"
                                    aria-pressed="false"
                                    class="absolute inset-y-0 right-2 my-auto h-8 rounded-full px-2.5 text-[11px] font-semibold text-ink-500 transition-colors hover:bg-ink-50 hover:text-brand-700"
                                >
                                    <span data-password-label>Mostrar</span>
                                    <span class="sr-only">contraseña</span>
                                </button>
                            </div>
                        </div>

                        <label class="flex items-center gap-2.5 text-xs font-medium text-ink-600">
                            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                            Mantener mi sesión iniciada
                        </label>

                        <button type="submit" class="btn btn-primary w-full">
                            <span>Ingresar a mi panel</span>
                            <x-icon name="arrow-right" class="h-4 w-4" />
                        </button>
                    </form>

                    <div class="mt-6 flex items-center justify-between gap-3 border-t border-ink-100 pt-5">
                        <a href="/" class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink-500 transition-colors hover:text-brand-700">
                            <x-icon name="arrow-left" class="h-3.5 w-3.5" />
                            Portal ciudadano
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink-500 transition-colors hover:text-brand-700">
                            <x-icon name="settings" class="h-3.5 w-3.5" />
                            Administración
                        </a>
                    </div>

                    <p class="mt-6 text-center text-[11px] leading-relaxed text-ink-400">
                        ¿Aún no tienes cuenta? La administración de la Microred debe crearla y asignarte un DNI y una contraseña.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
