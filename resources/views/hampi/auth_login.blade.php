@extends('hampi.layout')

@section('title', 'Acceso administrativo · Hampi')
@section('meta_description', 'Acceso para administración y admisión de la Microred Socabaya.')

@section('content')
    <x-auth-shell
        form-eyebrow="Área restringida / Administración"
        form-title="Acceso administrativo"
        form-lead="Ingresa con tu correo y contraseña para gestionar el equipo, los horarios y las campañas de la microred."
        :bullets="[
            ['icon' => 'users', 'text' => 'Altas de médicos y enfermeros con acceso propio.'],
            ['icon' => 'calendar', 'text' => 'Horarios mensuales de 150 horas por trabajador.'],
            ['icon' => 'shield-check', 'text' => 'Control de intentos y sesiones protegidas.'],
        ]"
    >
        <x-slot:heading>
            La gestión de la microred,<br>
            <span class="text-signal-300">en una sola hoja.</span>
        </x-slot:heading>

        <x-slot:lead>
            Publica el pizarrín, actualiza el rol de médicos y organiza las campañas preventivas de Socabaya.
        </x-slot:lead>

        <form action="{{ route('login.post') }}" method="POST" class="mt-7 space-y-5">
            @csrf

            @if ($errors->any())
                <div role="alert" class="flex items-start gap-2.5 border border-stamp-300 bg-stamp-50 p-3.5 text-xs leading-relaxed text-stamp-800">
                    <x-icon name="alert-circle" class="mt-0.5 h-4 w-4 shrink-0" />
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <div>
                <label for="email" class="label">Correo de acceso</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-ink-400" aria-hidden="true">
                        <x-icon name="mail" class="h-4 w-4" />
                    </span>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        required
                        value="{{ old('email') }}"
                        placeholder="nombre@hampi.pe"
                        autocomplete="username"
                        class="input pl-11"
                    >
                </div>
            </div>

            <div>
                <label for="password" class="label">Contraseña</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-ink-400" aria-hidden="true">
                        <x-icon name="key-round" class="h-4 w-4" />
                    </span>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        placeholder="••••••••"
                        autocomplete="current-password"
                        class="input pl-11 pr-24"
                    >
                    <button
                        type="button"
                        data-password-toggle="password"
                        aria-pressed="false"
                        class="absolute inset-y-0 right-0 my-auto inline-flex min-h-11 items-center px-3 font-mono text-[10px] font-semibold uppercase tracking-[0.04em] text-ink-500 transition-colors hover:text-brand-700"
                    >
                        <span data-password-label>Mostrar</span>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-full">
                <span>Ingresar al panel</span>
                <x-icon name="arrow-right" class="h-4 w-4" />
            </button>
        </form>

        <x-slot:footer>
            <div class="mt-6 flex items-center justify-between gap-3 border-t border-ink-200 pt-4">
                <a href="/" class="inline-flex min-h-10 items-center gap-1.5 text-xs font-semibold text-ink-600 transition-colors hover:text-brand-700">
                    <x-icon name="arrow-left" class="h-3.5 w-3.5" />
                    Portal ciudadano
                </a>
                <span class="flex items-center gap-1.5 font-mono text-[9px] uppercase tracking-[0.04em] text-ink-500">
                    <x-icon name="shield" class="h-3.5 w-3.5" />
                    Control de intentos
                </span>
            </div>
        </x-slot:footer>

    </x-auth-shell>
@endsection
