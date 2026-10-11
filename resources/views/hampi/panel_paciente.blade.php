@extends('hampi.layout')

@section('content')
<div class="space-y-4">
    <!-- Tarjeta de Bienvenida -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm flex items-center justify-between">
        <div>
            <h2 class="text-sm font-bold text-slate-900">Hola, {{ $ciudadano->nombres }}</h2>
            <p class="text-xs text-slate-500">DNI: {{ $ciudadano->dni }} · Socabaya</p>
        </div>
        <div class="w-10 h-10 bg-teal-50 rounded-full flex items-center justify-center text-turquesa">
            <i data-lucide="user-check" class="w-5 h-5"></i>
        </div>
    </div>

    <!-- Mi Próxima Cita -->
    <div class="bg-celeste-light border border-celeste/20 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold text-celeste uppercase tracking-wider">Próxima Atención</span>
            <span class="text-[10px] bg-teal-100 text-teal-800 font-semibold px-2 py-0.5 rounded-full">Confirmada</span>
        </div>
        <h3 class="text-base font-bold text-slate-900 mt-2">Medicina General</h3>
        <p class="text-xs text-slate-600 mt-0.5">C.S. San Martín de Socabaya · Consultorio 2</p>
        
        <div class="mt-3 pt-3 border-t border-celeste/20 flex items-center justify-between text-xs text-slate-700">
            <span class="flex items-center"><i data-lucide="calendar" class="w-3.5 h-3.5 mr-1 text-slate-500"></i> Miércoles 16 Oct</span>
            <span class="flex items-center"><i data-lucide="clock" class="w-3.5 h-3.5 mr-1 text-slate-500"></i> 08:30 a. m.</span>
        </div>
    </div>

    <!-- Historial de Vacunación -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm space-y-2">
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Mi Registro de Inmunizaciones</h3>
        <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-100">
            <span class="text-slate-700">Vacuna Influenza (Dosis Anual)</span>
            <span class="text-teal-600 font-semibold flex items-center"><i data-lucide="check" class="w-3.5 h-3.5 mr-0.5"></i> Aplicada</span>
        </div>
        <div class="flex items-center justify-between text-xs py-1.5">
            <span class="text-slate-700">Vacuna Hepatitis B (Esquema Completo)</span>
            <span class="text-teal-600 font-semibold flex items-center"><i data-lucide="check" class="w-3.5 h-3.5 mr-0.5"></i> Al día</span>
        </div>
    </div>
</div>
@endsection