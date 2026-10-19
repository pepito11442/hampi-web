@extends('hampi.layout')

@section('title', 'Acceso bloqueado temporalmente · Hampi')

@section('content')
    <div class="container-page py-16 sm:py-24">
        <div class="mx-auto max-w-lg text-center" data-reveal>
            <span class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-rose-50 text-rose-600 ring-1 ring-rose-100">
                <x-icon name="shield-alert" class="h-8 w-8" />
            </span>

            <span class="eyebrow mt-6 block text-rose-600">Seguridad institucional</span>
            <h1 class="mt-3 text-3xl font-bold tracking-tight text-ink-900">Acceso bloqueado temporalmente</h1>
            <p class="mt-4 text-sm leading-relaxed text-ink-500">
                {{ $mensaje ?? 'Se detectaron demasiados intentos fallidos consecutivos. Por seguridad, el acceso está pausado durante un minuto.' }}
            </p>

            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="/personal/login" class="btn btn-primary">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Volver a intentar
                </a>
                <a href="/" class="btn btn-secondary">
                    <x-icon name="home" class="h-4 w-4 text-brand-600" />
                    Ir al portal
                </a>
            </div>

            <p class="mt-8 flex items-center justify-center gap-2 text-[11px] text-ink-400">
                <x-icon name="lock" class="h-3.5 w-3.5" />
                Protección contra intentos de fuerza bruta
            </p>
        </div>
    </div>
@endsection
