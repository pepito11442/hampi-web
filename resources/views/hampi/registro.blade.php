@extends('hampi.layout')

@section('title', 'Crear mi código de acceso · Hampi')
@section('meta_description', 'Crea tu código de acceso para ver y pedir citas en la Microred Socabaya.')

@section('content')
    <x-auth-shell
        form-eyebrow="Portal ciudadano / Primera vez"
        form-title="Crea tu código de acceso"
        form-lead="Con tu DNI y un código de 6 dígitos que solo tú conoces podrás ver y pedir tus citas."
        :bullets="[
            ['icon' => 'lock', 'text' => 'Nadie verá tus citas aunque conozca tu DNI.'],
            ['icon' => 'calendar-check', 'text' => 'Pide citas en línea con los médicos de turno.'],
            ['icon' => 'id-card', 'text' => '¿Ya te atendieron en la posta? Pide ahí tu código.'],
        ]"
    >
        <x-slot:heading>
            Tus citas,<br>
            <span class="text-signal-300">solo para ti.</span>
        </x-slot:heading>

        <x-slot:lead>
            El código protege tu información de salud. Guárdalo bien y no lo compartas.
        </x-slot:lead>

        <form action="{{ route('ciudadano.registro.store') }}" method="POST" class="mt-7 space-y-5" data-reniec-form>
            @csrf

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
                <div class="flex items-end justify-between gap-2">
                    <label for="dni" class="label mb-0">DNI</label>
                    <span data-reniec-estado aria-live="polite" class="font-mono text-[11px] text-ink-500"></span>
                </div>
                <input
                    type="text"
                    name="dni"
                    id="dni"
                    value="{{ old('dni') }}"
                    maxlength="8"
                    inputmode="numeric"
                    pattern="[0-9]{8}"
                    autocomplete="username"
                    required
                    placeholder="8 dígitos"
                    data-solo-digitos
                    data-reniec-dni
                    class="input mt-1.5 font-mono text-lg tracking-[0.18em]"
                >
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="nombres" class="label">Nombres</label>
                    <input type="text" name="nombres" id="nombres" value="{{ old('nombres') }}" required maxlength="120" autocomplete="given-name" class="input" data-reniec-nombres>
                </div>
                <div>
                    <label for="apellidos" class="label">Apellidos</label>
                    <input type="text" name="apellidos" id="apellidos" value="{{ old('apellidos') }}" required maxlength="120" autocomplete="family-name" class="input" data-reniec-apellidos>
                </div>
            </div>

            <div>
                <label for="telefono" class="label">Celular <span class="normal-case tracking-normal text-ink-500">(opcional)</span></label>
                <input type="tel" name="telefono" id="telefono" value="{{ old('telefono') }}" maxlength="20" inputmode="tel" autocomplete="tel" placeholder="Ej. 987 654 321" class="input">
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="codigo" class="label">Elige tu código</label>
                    <input type="password" name="codigo" id="codigo" required maxlength="6" inputmode="numeric" pattern="[0-9]{6}" autocomplete="new-password" placeholder="6 dígitos" data-solo-digitos class="input font-mono text-lg tracking-[0.3em]">
                </div>
                <div>
                    <label for="codigo_confirmation" class="label">Repite el código</label>
                    <input type="password" name="codigo_confirmation" id="codigo_confirmation" required maxlength="6" inputmode="numeric" pattern="[0-9]{6}" autocomplete="new-password" placeholder="6 dígitos" data-solo-digitos class="input font-mono text-lg tracking-[0.3em]">
                </div>
            </div>
            <p class="-mt-2 text-xs leading-relaxed text-ink-500">No uses 123456, números repetidos ni partes de tu DNI.</p>

            <button type="submit" class="btn btn-primary w-full min-h-12 text-base">
                <span>Crear código y entrar</span>
                <x-icon name="arrow-right" class="h-4 w-4" />
            </button>
        </form>

        <x-slot:footer>
            <div class="mt-6 flex items-center justify-between gap-3 border-t border-ink-200 pt-4">
                <a href="{{ route('inicio') }}#acceso" class="inline-flex min-h-10 items-center gap-1.5 text-sm font-semibold text-ink-600 transition-colors hover:text-brand-700">
                    <x-icon name="arrow-left" class="h-3.5 w-3.5" />
                    Ya tengo código
                </a>
            </div>
        </x-slot:footer>
    </x-auth-shell>
@endsection
