@extends('hampi.layout')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-base font-bold text-slate-900">Médicos de Turno Hoy</h2>
            <p class="text-xs text-slate-500">C.S. San Martín de Socabaya</p>
        </div>
        <span class="text-[11px] bg-slate-100 text-slate-600 px-2 py-1 rounded">Hoy</span>
    </div>

    <!-- Turno Mañana -->
    <div class="space-y-2">
        <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wider flex items-center">
            <i data-lucide="sun" class="w-3.5 h-3.5 mr-1 text-amber-500"></i> Turno Mañana (07:30 - 13:30)
        </h3>
        @foreach($turnosManana as $turno)
            <div class="bg-white border border-slate-200 rounded-xl p-3 flex items-center justify-between shadow-sm">
                <div>
                    <h4 class="text-sm font-semibold text-slate-900">{{ $turno->medico_nombre }}</h4>
                    <p class="text-xs text-slate-500">{{ $turno->especialidad }} · {{ $turno->consultorio }}</p>
                </div>
                <span class="text-[11px] font-medium px-2 py-0.5 rounded-full {{ $turno->estado == 'Atendiendo' ? 'bg-teal-50 text-teal-700' : 'bg-slate-100 text-slate-600' }}">
                    {{ $turno->estado }}
                </span>
            </div>
        @endforeach
    </div>

    <!-- Turno Tarde -->
    <div class="space-y-2 pt-2">
        <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wider flex items-center">
            <i data-lucide="sunset" class="w-3.5 h-3.5 mr-1 text-orange-500"></i> Turno Tarde (13:30 - 19:30)
        </h3>
        @foreach($turnosTarde as $turno)
            <div class="bg-white border border-slate-200 rounded-xl p-3 flex items-center justify-between shadow-sm">
                <div>
                    <h4 class="text-sm font-semibold text-slate-900">{{ $turno->medico_nombre }}</h4>
                    <p class="text-xs text-slate-500">{{ $turno->especialidad }} · {{ $turno->consultorio }}</p>
                </div>
                <span class="text-[11px] font-medium px-2 py-0.5 rounded-full {{ $turno->estado == 'Atendiendo' ? 'bg-teal-50 text-teal-700' : 'bg-slate-100 text-slate-600' }}">
                    {{ $turno->estado }}
                </span>
            </div>
        @endforeach
    </div>

    <!-- Aviso de Urgencias -->
    <div class="p-3 bg-slate-100 border border-slate-200 rounded-lg text-[11px] text-slate-600 flex items-start space-x-2">
        <i data-lucide="info" class="w-4 h-4 shrink-0 text-slate-500 mt-0.5"></i>
        <span>Urgencias médicas y triaje atienden de forma continua. Pacientes graves son derivados al Hospital Honorio Delgado o Goyeneche.</span>
    </div>
</div>
@endsection