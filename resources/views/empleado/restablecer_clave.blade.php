@extends('hampi.layout')

@section('title', 'Nueva contraseña · Hampi')
@section('meta_description', 'Crea una contraseña nueva para tu cuenta del personal.')

@section('content')
    <x-auth-shell
        form-eyebrow="Personal / Recuperación"
        form-title="Crea tu contraseña nueva"
        form-lead="Mínimo 8 caracteres, con letras y números."
        :bullets="[
            ['icon' => 'lock', 'text' => 'No reutilices la contraseña de tu correo.'],
            ['icon' => 'shield-check', 'text' => 'Al guardar, se cierran las sesiones recordadas.'],
        ]"
    >
        <x-slot:heading>
            Una clave nueva,<br>
            <span class="text-signal-300">y listo.</span>
        </x-slot:heading>

        <form action="{{ route('password.update') }}" method="POST" class="mt-7 space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            @if ($errors->any())
                <div role="alert" class="flex items-start gap-2.5 border border-stamp-300 bg-stamp-50 p-3.5 text-sm leading-relaxed text-stamp-800">
                    <x-icon name="alert-circle" class="mt-0.5 h-4 w-4 shrink-0" />
                    <ul class="space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label for="email" class="label">Correo</label>
                <input type="email" name="email" id="email" value="{{ old('email', $email) }}" required autocomplete="username" class="input">
            </div>

            <div>
                <label for="password" class="label">Contraseña nueva</label>
                <input type="password" name="password" id="password" required minlength="8" autocomplete="new-password" class="input">
            </div>

            <div>
                <label for="password_confirmation" class="label">Repite la contraseña</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8" autocomplete="new-password" class="input">
            </div>

            <button type="submit" class="btn btn-primary w-full">
                <x-icon name="check" class="h-4 w-4" />
                Guardar contraseña
            </button>
        </form>
    </x-auth-shell>
@endsection
