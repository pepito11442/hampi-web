@extends('hampi.layout')

@section('title', 'Acceso del personal de salud · Hampi')
@section('meta_description', 'Acceso para médicos y enfermeros de la Microred Socabaya con DNI y contraseña.')

@section('content')
    <x-auth-shell
        form-eyebrow="Personal de salud"
        form-title="Ingreso con DNI"
        form-lead="Usa el DNI y la contraseña asignados por administración para ver tu horario y editar tu perfil."
        :bullets="[
            ['icon' => 'calendar-check', 'text' => 'Horario mensual con turnos de mañana, tarde y sábados.'],
            ['icon' => 'user-cog', 'text' => 'Foto, biografía, títulos y experiencia profesional.'],
            ['icon' => 'shield-check', 'text' => 'Cuenta creada y asignada por la administración.'],
        ]"
    >
        <x-slot:heading>
            Tu horario y tu perfil,<br>
            <span class="text-brand-300">en un solo lugar.</span>
        </x-slot:heading>

        <x-slot:lead>
            Panel exclusivo para médicos y enfermeros de la Microred Socabaya. Consulta tu calendario mensual y mantén tu perfil profesional actualizado.
        </x-slot:lead>

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

        <x-slot:footer>
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
        </x-slot:footer>

        <x-slot:note>
            <p class="mt-6 text-center text-[11px] leading-relaxed text-ink-400">
                ¿Aún no tienes cuenta? La administración de la Microred debe crearla y asignarte un DNI y una contraseña.
            </p>
        </x-slot:note>
    </x-auth-shell>
@endsection
