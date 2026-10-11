@extends('hampi.layout')

@section('content')
<div class="space-y-4">
    <div>
        <h2 class="text-base font-bold text-slate-900">Directorio de Establecimientos</h2>
        <p class="text-xs text-slate-500">Microred Socabaya · Red Arequipa-Caylloma</p>
    </div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">        @foreach($postas as $p)
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-turquesa bg-teal-50 px-2 py-0.5 rounded-full">
                            Categoría {{ $p->categoria }}
                        </span>
                        <h3 class="text-sm font-bold text-slate-900 mt-1">{{ $p->nombre }}</h3>
                    </div>
                </div>
                <div class="mt-3 space-y-1 text-xs text-slate-600">
                    <p class="flex items-center"><i data-lucide="map-pin" class="w-3.5 h-3.5 mr-1.5 text-slate-400"></i> {{ $p->direccion }}</p>
                    <p class="flex items-center"><i data-lucide="clock" class="w-3.5 h-3.5 mr-1.5 text-slate-400"></i> {{ $p->horario_atencion }}</p>
                    <p class="flex items-center"><i data-lucide="phone" class="w-3.5 h-3.5 mr-1.5 text-slate-400"></i> {{ $p->telefono ?? 'Sin teléfono registrado' }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection