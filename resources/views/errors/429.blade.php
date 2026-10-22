@extends('hampi.layout')

@section('title', 'Acceso bloqueado temporalmente · Hampi')

@section('content')
    <div class="container-page py-16 sm:py-24">
        <div class="mx-auto max-w-lg border border-ink-300 bg-paper-50 p-6 text-center shadow-lift sm:p-9" data-reveal>
            <span class="icon-seal mx-auto border-stamp-300 bg-stamp-50 text-stamp-700">
                <x-icon name="shield-alert" class="h-6 w-6" />
            </span>

            <span class="stamp mt-6">Acceso pausado</span>
            <h1 class="mt-3 font-display text-3xl font-bold tracking-[-0.035em] text-ink-950">Acceso bloqueado temporalmente</h1>
            <p class="mt-4 text-sm leading-relaxed text-ink-600">
                {{ $mensaje ?? 'Se detectaron demasiados intentos fallidos consecutivos. Por seguridad, el acceso está pausado durante un minuto.' }}
            </p>

            <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="/personal/login" class="btn btn-primary">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Volver a intentar
                </a>
                <a href="/" class="btn btn-secondary">
                    <x-icon name="home" class="h-4 w-4" />
                    Ir al portal
                </a>
            </div>

            <p class="mt-7 flex items-center justify-center gap-2 border-t border-ink-200 pt-4 font-mono text-[9px] uppercase tracking-[0.05em] text-ink-500">
                <x-icon name="lock" class="h-3.5 w-3.5" />
                Protección contra intentos de fuerza bruta
            </p>
        </div>
    </div>
@endsection
