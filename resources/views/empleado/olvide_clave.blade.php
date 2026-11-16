@extends('hampi.layout')

@section('title', 'Recuperar contraseña · Hampi')
@section('meta_description', 'Recupera el acceso del personal de la Microred Socabaya.')

@section('content')
    <x-auth-shell
        form-eyebrow="Personal / Recuperación"
        form-title="Recuperar contraseña"
        form-lead="Escribe tu DNI o tu correo. Te enviaremos un enlace para crear una contraseña nueva."
        :bullets="[
            ['icon' => 'mail', 'text' => 'El enlace llega al correo registrado en tu perfil.'],
            ['icon' => 'clock', 'text' => 'Vence en 60 minutos y sirve una sola vez.'],
            ['icon' => 'user-cog', 'text' => 'Sin correo registrado, administración puede asignarte una clave nueva.'],
        ]"
    >
        <x-slot:heading>
            ¿Olvidaste tu clave?<br>
            <span class="text-signal-300">Pasa a todos.</span>
        </x-slot:heading>

        <x-slot:lead>
            Recupera el acceso a tu horario y tu agenda en un par de minutos.
        </x-slot:lead>

        <form action="{{ route('password.email') }}" method="POST" class="mt-7 space-y-5">
            @csrf

            @if ($errors->any())
                <div role="alert" class="flex items-start gap-2.5 border border-stamp-300 bg-stamp-50 p-3.5 text-sm leading-relaxed text-stamp-800">
                    <x-icon name="alert-circle" class="mt-0.5 h-4 w-4 shrink-0" />
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <div>
                <label for="identificador" class="label">DNI o correo</label>
                <input type="text" name="identificador" id="identificador" value="{{ old('identificador') }}" required maxlength="150" autocomplete="username" class="input" placeholder="12345678 o nombre@hampi.pe">
            </div>

            <button type="submit" class="btn btn-primary w-full">
                <x-icon name="mail" class="h-4 w-4" />
                Enviar enlace
            </button>
        </form>

        <x-slot:footer>
            <div class="mt-6 flex items-center justify-between gap-3 border-t border-ink-200 pt-4">
                <a href="{{ route('empleado.login') }}" class="inline-flex min-h-10 items-center gap-1.5 text-sm font-semibold text-ink-600 transition-colors hover:text-brand-700">
                    <x-icon name="arrow-left" class="h-3.5 w-3.5" />
                    Volver al ingreso
                </a>
            </div>
        </x-slot:footer>
    </x-auth-shell>
@endsection
