@extends('hampi.layout')

@section('content')
<div class="min-h-[70vh] flex flex-col items-center justify-center py-6 px-4">
    <div class="w-full max-w-md mx-auto">
        
        <!-- Encabezado Institucional -->
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-teal-50 border border-turquesa/20 text-turquesa rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
                <i data-lucide="user-cog" class="w-8 h-8"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Personal de Salud</h2>
            <p class="text-sm text-slate-500 mt-1">
                Acceso exclusivo para enfermería y administración
            </p>
        </div>

        <!-- Tarjeta de Login Limpia -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 sm:p-8">
            <form action="/personal/login" method="POST" class="space-y-4">
                @csrf

                @if ($errors->any())
                    <div class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <!-- Correo (Vacío) -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Correo Institucional</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </span>
                        <input type="email" name="email" required value="{{ old('email') }}"
                            placeholder="ejemplo@minsa.gob.pe"
                            autocomplete="off"
                            class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-turquesa focus:border-transparent focus:outline-none transition-all">
                    </div>
                </div>

                <!-- Contraseña (Vacía) -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Contraseña</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i data-lucide="key-round" class="w-4 h-4"></i>
                        </span>
                        <input type="password" name="password" required
                            placeholder="••••••••"
                            autocomplete="current-password"
                            class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-turquesa focus:border-transparent focus:outline-none transition-all">
                    </div>
                </div>

                <!-- Botón de Ingreso -->
                <button type="submit" 
                    class="w-full py-3 bg-turquesa hover:bg-turquesa-hover text-white font-semibold rounded-xl text-sm transition-all shadow-sm flex items-center justify-center gap-2 mt-2">
                    <span>Ingresar al Sistema</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>

            <div class="mt-5 pt-4 border-t border-slate-100 text-center">
                <a href="/" class="text-xs text-slate-500 hover:text-turquesa font-medium flex items-center justify-center gap-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Volver al portal ciudadano</span>
                </a>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-center space-x-2 text-slate-400 text-xs">
            <i data-lucide="shield" class="w-3.5 h-3.5"></i>
            <span>Acceso protegido contra intentos de fuerza bruta (Rate Limiting)</span>
        </div>

    </div>
</div>
@endsection