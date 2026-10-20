@extends('hampi.layout')

@section('title', 'Acceso administrativo · Hampi')
@section('meta_description', 'Acceso exclusivo para el personal de enfermería y administración de la Microred Socabaya.')

@section('content')
    <x-auth-shell
        form-eyebrow="Área restringida"
        form-title="Personal de salud"
        form-lead="Acceso exclusivo para enfermería y administración de la Microred Socabaya."
        :bullets="[
            ['icon' => 'stethoscope', 'text' => 'Rol de médicos y estados de atención del día.'],
            ['icon' => 'syringe', 'text' => 'Avisos de vacunas con stock y requisitos.'],
            ['icon' => 'shield-check', 'text' => 'Acceso protegido con control de intentos.'],
        ]"
    >
        <x-slot:heading>
            Gestión de la atención,<br>
            <span class="text-brand-300">en tiempo real.</span>
        </x-slot:heading>

        <x-slot:lead>
            Publica el pizarrín de vacunas y actualiza el rol de médicos para mantener informada a tu comunidad.
        </x-slot:lead>

        <form action="{{ route('login.post') }}" method="POST" class="mt-7 space-y-5">
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

        <x-slot:footer>
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
        </x-slot:footer>

        <x-slot:note>
            <p class="mt-6 text-center text-[11px] leading-relaxed text-ink-400">
                El acceso está protegido contra intentos de fuerza bruta. Tras varios intentos fallidos, el sistema bloquea temporalmente el ingreso.
            </p>
        </x-slot:note>
    </x-auth-shell>
@endsection
