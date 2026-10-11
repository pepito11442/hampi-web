@extends('hampi.layout')

@section('content')
<div class="min-h-[70vh] flex flex-col items-center justify-center py-6 px-4">
    <div class="w-full max-w-md mx-auto">
        
        <!-- Ícono y Título -->
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-teal-50 border border-turquesa/20 text-turquesa rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
                <i data-lucide="shield-check" class="w-8 h-8"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Acceso Ciudadano</h2>
            <p class="text-sm text-slate-500 mt-1.5">
                Ingresa tu DNI para consultar tus citas y postas en Socabaya.
            </p>
        </div>

        <!-- Tarjeta de Login -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 sm:p-8">
            <form action="/ingreso-dni" method="POST" class="space-y-4">
                @csrf
                
                <!-- DNI -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="dni" class="block text-xs font-bold text-slate-600 uppercase tracking-wider">
                            Documento Nacional de Identidad
                        </label>
                        <span id="estado-reniec" class="text-[11px] text-slate-400 font-medium"></span>
                    </div>

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i data-lucide="id-card" class="w-5 h-5"></i>
                        </span>
                        <input type="text" name="dni" id="dni" maxlength="8" required 
                            placeholder="Ej. 72345678"
                            autocomplete="off"
                            class="w-full pl-11 pr-10 py-3 border border-slate-300 rounded-xl text-base tracking-widest font-mono text-slate-800 placeholder:text-slate-400 placeholder:font-sans placeholder:tracking-normal focus:outline-none focus:ring-2 focus:ring-turquesa focus:border-transparent transition-all shadow-inner bg-slate-50/50 focus:bg-white">
                        
                        <div id="spinner" class="hidden absolute inset-y-0 right-0 pr-3.5 flex items-center">
                            <i data-lucide="loader-2" class="w-4 h-4 text-turquesa animate-spin"></i>
                        </div>
                    </div>
                </div>

                <!-- Campos de Nombres y Apellidos Editables (Se muestran automáticamente) -->
                <div id="caja-identidad" class="hidden space-y-3 pt-2 pb-1 border-t border-slate-100 animate-fade-in">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-teal-800 flex items-center gap-1">
                            <i data-lucide="user" class="w-3.5 h-3.5 text-turquesa"></i>
                            Datos del Ciudadano (Puedes editarlos):
                        </span>
                        <span class="text-[10px] text-slate-400">Editable</span>
                    </div>

                    <div>
                        <label class="block text-[11px] text-slate-500 font-medium mb-1">Nombres:</label>
                        <input type="text" name="nombres_reniec" id="nombres_reniec" required
                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-turquesa bg-slate-50 focus:bg-white">
                    </div>

                    <div>
                        <label class="block text-[11px] text-slate-500 font-medium mb-1">Apellidos:</label>
                        <input type="text" name="apellidos_reniec" id="apellidos_reniec" required
                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-turquesa bg-slate-50 focus:bg-white">
                    </div>
                </div>

                <button type="submit" id="btn-ingresar"
                    class="w-full py-3 px-4 bg-turquesa hover:bg-turquesa-hover text-white font-semibold rounded-xl text-sm transition-all duration-150 flex items-center justify-center space-x-2 shadow-sm">
                    <span>Ingresar a mis servicios</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>

            <div class="mt-5 pt-5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>¿Solo deseas consultar?</span>
                <a href="/pizarrin-vacunas" class="text-turquesa hover:underline font-semibold flex items-center gap-1">
                    <span>Ver vacunas libres</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-center space-x-2 text-slate-400 text-xs">
            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
            <span>Consultas protegidas por la <strong>Ley N.° 29733</strong></span>
        </div>
    </div>
</div>

<script>
    const inputDni = document.getElementById('dni');
    const spinner = document.getElementById('spinner');
    const cajaIdentidad = document.getElementById('caja-identidad');
    const estadoReniec = document.getElementById('estado-reniec');
    const inputNombres = document.getElementById('nombres_reniec');
    const inputApellidos = document.getElementById('apellidos_reniec');

    inputDni.addEventListener('input', function() {
        const dni = this.value.trim();
        
        if (dni.length === 8) {
            spinner.classList.remove('hidden');
            estadoReniec.textContent = 'Buscando...';
            estadoReniec.className = 'text-[11px] text-turquesa font-medium';

            fetch(`/api/consultar-dni/${dni}`)
                .then(res => res.json())
                .then(data => {
                    spinner.classList.add('hidden');
                    if (data.success) {
                        inputNombres.value = data.nombres;
                        inputApellidos.value = data.apellidos;
                        
                        cajaIdentidad.classList.remove('hidden');
                        estadoReniec.textContent = 'Autocompletado ✓';
                        estadoReniec.className = 'text-[11px] text-teal-600 font-bold';
                        lucide.createIcons();
                    }
                })
                .catch(() => {
                    spinner.classList.add('hidden');
                    // Si falla el internet, igual mostramos las casillas vacías para que escriba
                    cajaIdentidad.classList.remove('hidden');
                });
        } else {
            cajaIdentidad.classList.add('hidden');
            spinner.classList.add('hidden');
            estadoReniec.textContent = '';
        }
    });
</script>
@endsection