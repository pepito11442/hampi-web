@extends('hampi.layout')

@section('content')
<div class="space-y-4">
    <!-- Encabezado del Pizarrín -->
    <div class="bg-celeste-light border border-celeste/20 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold text-celeste uppercase tracking-wider">Tablón Comunitario</span>
            <span class="text-[10px] text-slate-500 flex items-center">
                <i data-lucide="clock" class="w-3 h-3 mr-1"></i> Actualizado hoy 08:30 a. m.
            </span>
        </div>
        <h2 class="text-base font-bold text-slate-900 mt-1">El Pizarrín Digital de Vacunas</h2>
        <p class="text-xs text-slate-600 mt-0.5">Centro de Salud San Martín de Socabaya</p>
    </div>

    <!-- Lista de Vacunas y Avisos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($vacunas as $vacuna)
            <div class="bg-white border {{ $vacuna->es_urgente ? 'border-amber-300 ring-1 ring-amber-100' : 'border-slate-200' }} rounded-xl p-4 shadow-sm
            hover:-translate-y-1 hover:shadow-md transition-all duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        @if($vacuna->es_urgente)
                            <span class="inline-block bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-full mb-1">
                                Campaña Especial
                            </span>
                        @endif
                        <h3 class="text-sm font-bold text-slate-900">{{ $vacuna->titulo }}</h3>
                    </div>
                    <span class="text-[11px] font-medium px-2 py-0.5 rounded-full {{ $vacuna->stock_estado == 'Disponible' ? 'bg-teal-50 text-teal-700' : 'bg-red-50 text-red-700' }}">
                        {{ $vacuna->stock_estado }}
                    </span>
                </div>
                
                <p class="text-xs text-slate-600 mt-2">
                    <strong class="text-slate-700">Grupo:</strong> {{ $vacuna->grupo_objetivo }}
                </p>
                <p class="text-xs text-slate-500 mt-1">
                    <strong class="text-slate-700">Requisito:</strong> {{ $vacuna->requisitos }}
                </p>
            </div>
        @empty
            <p class="text-center text-xs text-slate-400 py-6">No hay avisos publicados en este momento.</p>
        @endforelse
    </div>
</div>
@endsection