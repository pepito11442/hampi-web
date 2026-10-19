@extends('hampi.layout')

@section('title', 'Acceso del personal de salud · Hampi')
@section('meta_description', 'Acceso exclusivo para el personal de enfermería y administración de la Microred Socabaya.')

@section('content')
    <div class="container-page py-10 sm:py-16">
        <div class="mx-auto grid max-w-5xl overflow-hidden rounded-[2rem] border border-ink-100 bg-white shadow-lift lg:grid-cols-2">
            {{-- Panel institucional --}}
            <div class="relative hidden overflow-hidden bg-ink-950 p-10 text-white lg:flex lg:flex-col lg:justify-between">
                <div class="pointer-events-none absolute inset-0 bg-dot-grid opacity-30" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -left-20 -top-20 h-64 w-64 rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -bottom-24 -right-16 h-72 w-72 rounded-full bg-sky-500/15 blur-3xl" aria-hidden="true"></div>

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
                        Gestión de la atención,<br>
                        <span class="text-brand-300">en tiempo real.</span>
                    </h1>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-white/70">
                        Publica el pizarrín de vacunas y actualiza el rol de médicos para mantener informada a tu comunidad.
                    </p>

                    <ul class="mt-8 space-y-4 text-sm">
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-brand-500/20 text-brand-300">
                                <x-icon name="stethoscope" class="h-4 w-4" />
                            </span>
                            <span class="text-white/80">Rol de médicos y estados de atención del día.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-brand-500/20 text-brand-300">
                                <x-icon name="syringe" class="h-4 w-4" />
                            </span>
                            <span class="text-white/80">Avisos de vacunas con stock y requisitos.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-brand-500/20 text-brand-300">
                                <x-icon name="shield-check" class="h-4 w-4" />
                            </span>
                            <span class="text-white/80">Acceso protegido con control de intentos.</span>
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
                        <span class="eyebrow">Área restringida</span>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-ink-900">Personal de salud</h2>
                        <p class="mt-2 text-sm leading-relaxed text-ink-500">
                            Acceso exclusivo para enfermería y administración de la Microred Socabaya.
                        </p>
                    </div>

                    <form action="/personal/login" method="POST" class="mt-7 space-y-5">
                        @csrf

                        @if ($errors->any())
                            <div role="alert" class="flex items-start gap-2.5 rounded-2xl border border-rose-200 bg-rose-50 p-3.5 text-xs leading-relaxed text-rose-700">
                                <x-icon name="alert-circle" class="mt-0.5 h-4 w-4 shrink-0" />
                                <span>{{ $errors->first() }}</span>
                            </div>
                        @endif

                        <div>
                            <label for="email" class="label">Correo institucional</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-ink-400" aria-hidden="true">
                                    <x-icon name="mail" class="h-4 w-4" />
                                </span>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    required
                                    value="{{ old('email') }}"
                                    placeholder="ejemplo@minsa.gob.pe"
                                    autocomplete="username"
                                    class="input pl-11"
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

                        <button type="submit" class="btn btn-primary w-full">
                            <span>Ingresar al sistema</span>
                            <x-icon name="arrow-right" class="h-4 w-4" />
                        </button>
                    </form>

                    <div class="mt-6 flex items-center justify-between gap-3 border-t border-ink-100 pt-5">
                        <a href="/" class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink-500 transition-colors hover:text-brand-700">
                            <x-icon name="arrow-left" class="h-3.5 w-3.5" />
                            Portal ciudadano
                        </a>
                        <span class="flex items-center gap-1.5 text-[11px] text-ink-400">
                            <x-icon name="shield" class="h-3.5 w-3.5" />
                            Rate limiting activo
                        </span>
                    </div>

                    <p class="mt-6 text-center text-[11px] leading-relaxed text-ink-400">
                        El acceso está protegido contra intentos de fuerza bruta. Tras varios intentos fallidos, el sistema bloquea temporalmente el ingreso.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
