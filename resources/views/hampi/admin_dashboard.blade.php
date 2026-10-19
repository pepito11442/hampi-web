@extends('hampi.layout')

@section('content')
<div class="space-y-6">
    <!-- Barra Superior del Administrador -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-4 border-b border-slate-200 gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                <span>Panel de Gestión Hospitalaria</span>
                <span class="text-xs bg-teal-100 text-teal-800 font-semibold px-2 py-0.5 rounded-full">Enfermería / Admisión</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Centro de Salud San Martín de Socabaya · Responsable: {{ Auth::user()->name ?? 'Lic. Carmen Alfaro' }}</p>
        </div>
        <form action="/personal/logout" method="POST">
            @csrf
            <button type="submit" class="text-xs text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 px-3 py-1.5 rounded-lg font-medium flex items-center gap-1.5 transition-colors">
                <i data-lucide="log-out" class="w-4 h-4"></i>
                <span>Cerrar Sesión</span>
            </button>
        </form>
    </div>

    <!-- SECCIÓN DE MÉDICOS Y ESPECIALIDADES -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="stethoscope" class="w-5 h-5 text-turquesa"></i>
                    <span>Gestión de Médicos y Turnos del Día</span>
                </h3>
                <p class="text-xs text-slate-500">Agrega especialistas o actualiza su disponibilidad en tiempo real</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Formulario para Agregar Médico (Columna 1) -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                    <i data-lucide="user-plus" class="w-4 h-4 text-turquesa"></i>
                    <span>+ Registrar Nuevo Médico</span>
                </h4>

                <form action="/admin/turnos/guardar" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Nombre del Médico:</label>
                        <input type="text" name="medico_nombre" required placeholder="Ej. Dr. Carlos Valdivia"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-turquesa focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Especialidad:</label>
                        <select name="especialidad" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-turquesa">
                            <option value="Medicina General">Medicina General</option>
                            <option value="Pediatría">Pediatría</option>
                            <option value="Obstetricia / Ginecología">Obstetricia / Ginecología</option>
                            <option value="Odontología">Odontología</option>
                            <option value="Control CRED y Vacunación">Control CRED y Vacunación</option>
                            <option value="Psicología">Psicología</option>
                            <option value="Laboratorio y Toma de Muestras">Laboratorio y Toma de Muestras</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Turno:</label>
                            <select name="turno" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                                <option value="Mañana">Mañana</option>
                                <option value="Tarde">Tarde</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Consultorio:</label>
                            <input type="text" name="consultorio" required placeholder="Ej. Cons. 3"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                        </div>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-turquesa hover:bg-turquesa-hover text-white text-xs font-semibold rounded-lg shadow-sm transition-all flex items-center justify-center gap-1.5 mt-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Agregar al Rol de Hoy</span>
                    </button>
                </form>
            </div>

            <!-- Lista de Médicos Activos (Columna 2 y 3) -->
            <div class="lg:col-span-2 space-y-3">
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Médicos en Rol Hoy ({{ count($turnos) }})
                </h4>

                <div class="space-y-2">
                    @forelse($turnos as $t)
                        <div class="p-3 bg-white border border-slate-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs hover:border-slate-300 transition-all">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h5 class="text-sm font-bold text-slate-900">{{ $t->medico_nombre }}</h5>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $t->turno == 'Mañana' ? 'bg-amber-100 text-amber-800' : 'bg-orange-100 text-orange-800' }}">
                                        {{ $t->turno }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $t->especialidad }} · <span class="text-slate-700 font-medium">{{ $t->consultorio }}</span></p>
                            </div>

                            <div class="flex items-center gap-2">
                                <!-- Cambiar Estado del Turno -->
                                <form action="/admin/turnos/{{ $t->id }}/estado" method="POST">
                                    @csrf
                                    <select name="estado" onchange="this.form.submit()" 
                                        class="text-xs py-1.5 px-2.5 border rounded-lg font-medium cursor-pointer {{ $t->estado == 'Atendiendo' ? 'bg-teal-50 text-teal-800 border-teal-200' : ($t->estado == 'Turno Completo' ? 'bg-slate-100 text-slate-700 border-slate-300' : 'bg-red-50 text-red-700 border-red-200') }}">
                                        <option value="Atendiendo" {{ $t->estado == 'Atendiendo' ? 'selected' : '' }}>Atendiendo</option>
                                        <option value="Turno Completo" {{ $t->estado == 'Turno Completo' ? 'selected' : '' }}>Turno Completo</option>
                                        <option value="Reprogramado" {{ $t->estado == 'Reprogramado' ? 'selected' : '' }}>Reprogramado</option>
                                    </select>
                                </form>

                                <!-- Eliminar Médico -->
                                <form action="/admin/turnos/{{ $t->id }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar a este médico del rol?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar médico">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic py-4">No hay médicos registrados en el rol de hoy.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

        <!-- SECCIÓN DEL PIZARRÍN DE VACUNAS -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-6">
        <div class="border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="clipboard-list" class="w-5 h-5 text-turquesa"></i>
                <span>Gestión del Pizarrín Digital de Vacunas</span>
            </h3>
            <p class="text-xs text-slate-500">Publica nuevos lotes de vacunas o retira avisos cuando el stock se agote</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Formulario Publicar (Columna 1) -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">
                    + Publicar Nuevo Aviso
                </h4>
                <form action="/admin/pizarrin/guardar" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Vacuna o Campaña:</label>
                        <input type="text" name="titulo" required placeholder="Ej. Vacuna VPH Escolar"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-turquesa">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Grupo Objetivo:</label>
                        <input type="text" name="grupo_objetivo" required placeholder="Ej. Niñas de 9 a 13 años"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-turquesa">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Stock:</label>
                        <select name="stock_estado" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                            <option value="Disponible">Disponible</option>
                            <option value="Por agotarse">Por agotarse</option>
                            <option value="Agotado">Agotado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Requisitos:</label>
                        <input type="text" name="requisitos" required placeholder="Ej. DNI físico y carné"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white">
                    </div>
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="es_urgente" id="es_urgente" class="rounded text-turquesa">
                        <label for="es_urgente" class="text-xs text-slate-600">Marcar como urgente / campaña</label>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-turquesa hover:bg-turquesa-hover text-white text-xs font-semibold rounded-lg shadow-sm mt-2">
                        Publicar en el Pizarrín
                    </button>
                </form>
            </div>

            <!-- Lista de Vacunas Activas con Botón de Eliminar (Columna 2 y 3) -->
            <div class="lg:col-span-2 space-y-3">
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Avisos Publicados en el Pizarrín ({{ count($vacunas) }})
                </h4>

                <div class="space-y-2">
                    @forelse($vacunas as $v)
                        <div class="p-3 bg-white border border-slate-200 rounded-xl flex items-center justify-between gap-3 shadow-xs hover:border-slate-300 transition-all">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h5 class="text-sm font-bold text-slate-900">{{ $v->titulo }}</h5>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $v->stock_estado == 'Disponible' ? 'bg-teal-50 text-teal-800' : 'bg-red-50 text-red-800' }}">
                                        {{ $v->stock_estado }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">Grupo: {{ $v->grupo_objetivo }} · Requisito: {{ $v->requisitos }}</p>
                            </div>

                            <!-- Botón para Eliminar la Vacuna -->
                            <form action="/admin/pizarrin/{{ $v->id }}" method="POST" onsubmit="return confirm('¿Seguro que deseas retirar este aviso de vacuna?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Retirar aviso">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic py-4">No hay avisos de vacunas registrados.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection