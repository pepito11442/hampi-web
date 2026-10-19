@extends('hampi.layout')

@section('content')
<div class="min-h-[50vh] flex flex-col items-center justify-center text-center p-6">
    <div class="w-14 h-14 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4">
        <i data-lucide="shield-alert" class="w-8 h-8"></i>
    </div>
    <h2 class="text-xl font-bold text-slate-900">Acceso Bloqueado Temporalmente</h2>
    <p class="text-sm text-slate-600 max-w-sm mt-2">
        Se detectaron demasiados intentos fallidos consecutivos. Por seguridad institucional, este acceso está pausado por 1 minuto.
    </p>
    <a href="/personal/login" class="mt-6 px-4 py-2 bg-turquesa text-white rounded-lg text-xs font-semibold">
        Volver a intentar
    </a>
</div>
@endsection