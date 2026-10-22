@extends('hampi.layout')

@section('title', 'Hampi · Microred Socabaya | Médicos, vacunas y campañas')
@section('meta_description', 'Consulta médicos de turno, disponibilidad de vacunas, campañas preventivas y establecimientos de la Microred Socabaya. Ingresa con tu DNI para revisar tus citas.')

@section('content')
    @php
        $turnosHoy = $stats['turnos'] ?? 0;
        $vacunasDisponibles = $stats['vacunas'] ?? 0;
        $postasActivas = $stats['postas'] ?? 0;
        $servicios = [
            [
                'href' => '/medicos-hoy',
                'titulo' => 'Médicos de turno hoy',
                'descripcion' => 'Especialidades, consultorios y estado de atención del turno de mañana y tarde.',
                'accion' => 'Ver el rol de hoy',
                'icono' => 'stethoscope',
            ],
            [
                'href' => '/pizarrin-vacunas',
                'titulo' => 'Pizarrín de vacunas',
                'descripcion' => 'Stock disponible, grupo objetivo y requisitos para acudir a vacunarte.',
                'accion' => 'Ver disponibilidad',
                'icono' => 'syringe',
            ],
            [
                'href' => '/campanas-preventivas',
                'titulo' => 'Campañas preventivas',
                'descripcion' => 'Controles gratuitos de presión, diabetes, anemia y salud bucal en la microred.',
                'accion' => 'Ver campañas',
                'icono' => 'shield-check',
            ],
            [
                'href' => '/directorio-postas',
                'titulo' => 'Directorio de postas',
                'descripcion' => 'Direcciones, teléfonos y horarios de los establecimientos de Socabaya.',
                'accion' => 'Ver establecimientos',
                'icono' => 'map-pin',
            ],
        ];
    @endphp

    {{-- PORTADA: el parte del día --}}
    <section class="container-page pt-4 pb-10 sm:pt-7">
        <article class="sheet" data-reveal>
            <div class="sheet-head">
                <span>Microred Socabaya / Parte del día</span>
                <span class="text-paper-100/65">{{ now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}</span>
                <span class="text-paper-100/65">Folio MR-SOC-{{ now()->format('ymd') }}</span>
            </div>

            <div class="grid gap-7 p-5 sm:p-8 lg:grid-cols-12 lg:gap-x-10 ">
                <div class="lg:col-span-8">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="mono-label">Atención primaria / Socabaya, Arequipa</p>
                    </div>

                    <h1 class="mt-4 max-w-3xl text-balance text-[2.7rem] font-bold leading-[0.96] tracking-[-0.05em] text-ink-950 sm:text-6xl lg:text-[4.5rem]">
                        La salud de tu barrio, <span class="text-brand-700">hoy.</span>
                    </h1>

                    <p class="mt-4 max-w-xl text-base leading-relaxed text-ink-600">
                        Médicos de turno, vacunas, campañas y establecimientos de la Microred Socabaya. Sin crear una cuenta: entra con tu DNI.
                    </p>

                    <form id="acceso" action="/ingreso-dni" method="POST" class="mt-6 border border-ink-300 bg-paper-100/70 p-4 sm:p-5">
                        @csrf

                        <div class="flex flex-wrap items-end justify-between gap-2">
                            <label for="dni" class="label mb-0">Ingresa tu DNI</label>
                            <span id="estado-reniec" aria-live="polite" class="font-mono text-[10px] text-ink-500"></span>
                        </div>

                        <div class="mt-2 flex flex-col gap-2 sm:flex-row">
                            <div class="relative flex-1">
                                <input
                                    type="text"
                                    name="dni"
                                    id="dni"
                                    maxlength="8"
                                    inputmode="numeric"
                                    pattern="[0-9]{8}"
                                    autocomplete="off"
                                    required
                                    placeholder="8 dígitos"
                                    aria-describedby="dni-ayuda"
                                    class="input font-mono tracking-[0.22em]"
                                >
                                <span id="spinner" class="absolute inset-y-0 right-3 hidden items-center text-brand-700">
                                    <x-icon name="loader" class="h-5 w-5 animate-spin" />
                                </span>
                            </div>

                            <button type="submit" id="btn-ingresar" class="btn btn-primary shrink-0">
                                <span>Entrar a mi panel</span>
                                <x-icon name="arrow-right" class="h-4 w-4" />
                            </button>
                        </div>

                        <p id="dni-ayuda" class="mt-2 text-xs leading-relaxed text-ink-500">
                            8 dígitos. Si la validación no responde, puedes escribir tus nombres y apellidos.
                        </p>

                        <div id="caja-identidad" class="mt-4 hidden border border-brand-200 bg-brand-50 p-4">
                            <p class="flex items-center gap-2 font-mono text-[10px] font-semibold uppercase tracking-[0.08em] text-brand-800">
                                <x-icon name="user" class="h-3.5 w-3.5" />
                                Revisa tus datos antes de entrar
                            </p>

                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label for="nombres_reniec" class="label">Nombres</label>
                                    <input type="text" name="nombres_reniec" id="nombres_reniec" class="input bg-paper-50">
                                </div>
                                <div>
                                    <label for="apellidos_reniec" class="label">Apellidos</label>
                                    <input type="text" name="apellidos_reniec" id="apellidos_reniec" class="input bg-paper-50">
                                </div>
                            </div>
                        </div>

                        <div id="error-reniec" class="notice notice-warning mt-4 hidden items-start gap-2 text-xs leading-relaxed">
                            <x-icon name="alert-circle" class="mt-0.5 h-4 w-4 shrink-0" />
                            <span>No pudimos validar tu DNI en este momento. Puedes escribir tus nombres y apellidos para continuar.</span>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-1 font-mono text-[10px] uppercase tracking-[0.05em] text-ink-500">
                            <span class="flex items-center gap-1.5"><x-icon name="lock" class="h-3.5 w-3.5" /> Acceso sin contraseña</span>
                            <span class="flex items-center gap-1.5"><x-icon name="badge-check" class="h-3.5 w-3.5" /> Datos de demostración</span>
                        </div>
                    </form>
                </div>

                <div class="lg:col-span-4 lg:row-span-2 lg:border-l lg:border-ink-200 lg:pl-8">
                    <figure class="taped-photo">
                        <picture>
                            <source srcset="/images/comunidad-cuidado.webp" type="image/webp">
                            <img
                                src="/images/comunidad-cuidado.jpg"
                                alt="Manos de una persona joven sosteniendo las manos de una persona mayor"
                                width="1200"
                                height="900"
                                fetchpriority="high"
                                decoding="async"
                                class="aspect-[4/3] w-full object-cover"
                            >
                        </picture>
                        <figcaption class="mt-2 flex items-start justify-between gap-3 font-mono text-[10px] uppercase leading-relaxed tracking-[0.05em] text-ink-500">
                            <span>Cuidado de la comunidad</span>
                            <span class="text-right">Foto: Kampus Production / Pexels</span>
                        </figcaption>
                    </figure>

                    <div class="mt-7 border-t border-ink-300 pt-4">
                        <p class="mono-label">En esta microred</p>
                        <p class="mt-2 font-display text-lg font-bold leading-snug text-ink-950">
                            {{ $postasActivas }} {{ $postasActivas === 1 ? 'establecimiento activo' : 'establecimientos activos' }}
                        </p>
                        <p class="mt-1 text-sm leading-relaxed text-ink-600">
                            Información publicada por el personal de salud para que llegues preparado.
                        </p>
                        <a href="/directorio-postas" class="link-arrow mt-2">
                            Ver direcciones y horarios
                            <x-icon name="arrow-right" class="h-4 w-4" />
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="grid border-t-2 border-ink-950 sm:grid-cols-3">
                        <a href="/medicos-hoy" class="group flex min-h-28 flex-col justify-between border-b border-ink-200 py-4 pr-4 transition-colors hover:bg-paper-100 sm:border-b-0 sm:border-r sm:px-4 sm:first:pl-0">
                            <span class="mono-label">Médicos en rol</span>
                            <span class="mt-3 flex items-end justify-between gap-2">
                                <span class="stat-number" data-count>{{ $turnosHoy }}</span>
                                <x-icon name="arrow-right" class="mb-1 h-4 w-4 text-ink-400 transition-transform group-hover:translate-x-1" />
                            </span>
                            <span class="mt-1 text-xs text-ink-500">Ver el rol de hoy</span>
                        </a>

                        <a href="/pizarrin-vacunas" class="group flex min-h-28 flex-col justify-between border-b border-ink-200 py-4 pr-4 transition-colors hover:bg-paper-100 sm:border-b-0 sm:border-r sm:px-4">
                            <span class="mono-label">Vacunas disponibles</span>
                            <span class="mt-3 flex items-end justify-between gap-2">
                                <span class="stat-number" data-count>{{ $vacunasDisponibles }}</span>
                                <x-icon name="arrow-right" class="mb-1 h-4 w-4 text-ink-400 transition-transform group-hover:translate-x-1" />
                            </span>
                            <span class="mt-1 text-xs text-ink-500">Abrir el pizarrín</span>
                        </a>

                        <a href="/campanas-preventivas" class="group flex min-h-28 flex-col justify-between py-4 pr-4 transition-colors hover:bg-paper-100 sm:px-4 sm:pr-0">
                            <span class="mono-label">Próxima campaña</span>
                            <span class="mt-3 line-clamp-2 font-display text-base font-bold leading-tight text-ink-950">
                                {{ $proximaCampana?->titulo ?? 'Sin campaña programada' }}
                            </span>
                            <span class="mt-1 text-xs text-ink-500">
                                {{ $proximaCampana?->periodo ?? 'Consulta el portal' }}
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </article>
    </section>

    {{-- SERVICIOS: índice de la hoja --}}
    <section class="container-page py-12 sm:py-16">
        <div class="sheet p-5 sm:p-8 grid gap-8 lg:grid-cols-12">
            <div class="lg:col-span-4 lg:pr-8">
                <h2 class="section-title text-3xl sm:text-4xl">Lo que puedes resolver aquí</h2>
                <p class="section-lead mt-3">
                    El portal reúne la información que cambia todos los días en la microred. Elige por dónde empezar.
                </p>
                <a href="/directorio-postas" class="link-arrow mt-4">
                    Ver todas las postas
                    <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>

            <div class="border-t-2 border-ink-950 lg:col-span-8">
                @foreach ($servicios as $servicio)
                    <a href="{{ $servicio['href'] }}" class="group grid grid-cols-12 items-start gap-3 border-b border-ink-200 py-5 transition-colors hover:bg-paper-50 sm:gap-5">
                        <span class="col-span-2 pt-1 font-mono text-[11px] text-ink-500 sm:col-span-1">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="col-span-10 flex items-start gap-4 sm:col-span-9">
                            <span class="icon-seal hidden sm:grid">
                                <x-icon :name="$servicio['icono']" class="h-5 w-5" />
                            </span>
                            <span>
                                <span class="block font-display text-xl font-bold tracking-[-0.03em] text-ink-950">{{ $servicio['titulo'] }}</span>
                                <span class="mt-1 block max-w-xl text-sm leading-relaxed text-ink-600">{{ $servicio['descripcion'] }}</span>
                            </span>
                        </span>
                        <span class="col-span-12 flex items-center gap-1.5 pl-[calc(16.666%+0.75rem)] text-xs font-semibold text-brand-700 sm:col-span-2 sm:justify-end sm:pl-0">
                            {{ $servicio['accion'] }}
                            <x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FAQ: preguntas al pie de la hoja --}}
    <section class="container-page py-12 sm:py-16">
        <div class="sheet p-5 sm:p-8 grid gap-8 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <h2 class="section-title text-3xl sm:text-4xl">Antes de venir</h2>
                <p class="section-lead mt-3">
                    Respuestas breves para llegar sin vueltas. Si tu caso es urgente, acude al establecimiento más cercano.
                </p>
                <a href="/directorio-postas" class="btn btn-secondary mt-5">
                    <x-icon name="map-pin" class="h-4 w-4" />
                    Ver postas y horarios
                </a>
            </div>

            <div class=" border-t-2 border-ink-950 lg:col-span-8">
                <details class="faq-item group border-b border-ink-200">
                    <summary class="flex min-h-14 cursor-pointer items-center justify-between gap-4 py-4 font-display text-base font-bold text-ink-950 transition-colors hover:text-brand-700">
                        ¿Necesito crear una cuenta para usar el portal?
                        <span class="grid h-8 w-8 shrink-0 place-items-center border border-ink-300 text-ink-600 group-open:border-brand-700 group-open:bg-brand-700 group-open:text-paper-50">
                            <x-icon name="plus" class="h-4 w-4 group-open:hidden" />
                            <x-icon name="minus" class="hidden h-4 w-4 group-open:block" />
                        </span>
                    </summary>
                    <p class="max-w-2xl pb-5 text-sm leading-relaxed text-ink-600">
                        No. Solo necesitas tu DNI. El portal intenta validar tus datos con RENIEC y, si el servicio no está disponible, puedes escribir tus nombres y apellidos para continuar.
                    </p>
                </details>

                <details class="faq-item group border-b border-ink-200">
                    <summary class="flex min-h-14 cursor-pointer items-center justify-between gap-4 py-4 font-display text-base font-bold text-ink-950 transition-colors hover:text-brand-700">
                        ¿Qué información puedo consultar?
                        <span class="grid h-8 w-8 shrink-0 place-items-center border border-ink-300 text-ink-600 group-open:border-brand-700 group-open:bg-brand-700 group-open:text-paper-50">
                            <x-icon name="plus" class="h-4 w-4 group-open:hidden" />
                            <x-icon name="minus" class="hidden h-4 w-4 group-open:block" />
                        </span>
                    </summary>
                    <p class="max-w-2xl pb-5 text-sm leading-relaxed text-ink-600">
                        Tus citas programadas, el rol de médicos del día, la disponibilidad de vacunas del pizarrín digital y el directorio de establecimientos de la microred.
                    </p>
                </details>

                <details class="faq-item group border-b border-ink-200">
                    <summary class="flex min-h-14 cursor-pointer items-center justify-between gap-4 py-4 font-display text-base font-bold text-ink-950 transition-colors hover:text-brand-700">
                        ¿Qué pasa con mis datos en esta demo?
                        <span class="grid h-8 w-8 shrink-0 place-items-center border border-ink-300 text-ink-600 group-open:border-brand-700 group-open:bg-brand-700 group-open:text-paper-50">
                            <x-icon name="plus" class="h-4 w-4 group-open:hidden" />
                            <x-icon name="minus" class="hidden h-4 w-4 group-open:block" />
                        </span>
                    </summary>
                    <p class="max-w-2xl pb-5 text-sm leading-relaxed text-ink-600">
                        Hampi es una demo académica con datos simulados. No ingreses información personal sensible. Un despliegue real de la microred definiría sus propias políticas de privacidad y tratamiento de datos.
                    </p>
                </details>

                <details class="faq-item group border-b border-ink-200">
                    <summary class="flex min-h-14 cursor-pointer items-center justify-between gap-4 py-4 font-display text-base font-bold text-ink-950 transition-colors hover:text-brand-700">
                        ¿Cómo sé si la información está actualizada?
                        <span class="grid h-8 w-8 shrink-0 place-items-center border border-ink-300 text-ink-600 group-open:border-brand-700 group-open:bg-brand-700 group-open:text-paper-50">
                            <x-icon name="plus" class="h-4 w-4 group-open:hidden" />
                            <x-icon name="minus" class="hidden h-4 w-4 group-open:block" />
                        </span>
                    </summary>
                    <p class="max-w-2xl pb-5 text-sm leading-relaxed text-ink-600">
                        Cada sección muestra la fecha de actualización que registra el personal de salud. Las campañas incluyen fechas y lugares; confirma siempre antes de acudir.
                    </p>
                </details>
            </div>
        </div>
    </section>

    {{-- CIERRE: ventanilla de atención --}}
    <section class="container-page pb-16 sm:pb-20">
        <div class="grid border-2 border-ink-950 bg-ink-950 text-paper-50 lg:grid-cols-2" data-reveal>
            <div class="p-6 sm:p-9">
                <span class="border-2 border-signal-400 px-2 py-1 font-mono text-[10px] uppercase tracking-[0.1em] text-signal-300">Ventanilla</span>
                <h2 class="mt-5 max-w-lg text-balance text-3xl font-bold leading-[1.02] tracking-[-0.04em] sm:text-4xl">
                    La información de la microred, sin filas para preguntar.
                </h2>
                <p class="mt-4 max-w-md text-sm leading-relaxed text-paper-100/75">
                    Consulta el rol de hoy, revisa el pizarrín o ubica tu establecimiento. Si necesitas atención, acércate con tu DNI.
                </p>
                <div class="mt-6 flex flex-wrap gap-2">
                    <a href="#acceso" class="btn bg-paper-50 text-ink-950 hover:bg-paper-200">
                        <x-icon name="id-card" class="h-4 w-4" />
                        Ingresar con DNI
                    </a>
                    <a href="/medicos-hoy" class="btn border-paper-50/30 text-paper-50 hover:bg-paper-50/10">
                        Ver médicos de hoy
                    </a>
                </div>
            </div>

            <div class="border-t border-paper-50/20 p-6 sm:p-9 lg:border-l lg:border-t-0">
                <p class="mono-label text-paper-100/60">Datos de atención</p>
                <dl class="mt-4 space-y-4 text-sm">
                    <div class="border-b border-paper-50/15 pb-3">
                        <dt class="flex items-center gap-2 font-semibold text-paper-50">
                            <x-icon name="building-2" class="h-4 w-4 shrink-0 text-signal-300" />
                            Establecimientos
                        </dt>
                        <dd class="mt-1 pl-6 text-paper-100/70">{{ $postasActivas }} {{ $postasActivas === 1 ? 'posta activa' : 'postas activas' }} en Socabaya.</dd>
                    </div>
                    <div class="border-b border-paper-50/15 pb-3">
                        <dt class="flex items-center gap-2 font-semibold text-paper-50">
                            <x-icon name="clock" class="h-4 w-4 shrink-0 text-signal-300" />
                            Horario de referencia
                        </dt>
                        <dd class="mt-1 pl-6 text-paper-100/70">Lunes a viernes, 07:00–19:30 · Sábados, 07:00–15:00.</dd>
                    </div>
                </dl>
                <a href="/directorio-postas" class="link-arrow mt-5 text-paper-50 hover:text-signal-300">
                    Ver el directorio completo
                    <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (function () {
            const inputDni = document.getElementById('dni');
            const spinner = document.getElementById('spinner');
            const cajaIdentidad = document.getElementById('caja-identidad');
            const estadoReniec = document.getElementById('estado-reniec');
            const inputNombres = document.getElementById('nombres_reniec');
            const inputApellidos = document.getElementById('apellidos_reniec');
            const errorReniec = document.getElementById('error-reniec');

            if (!inputDni) return;

            const ocultarIdentidad = () => {
                cajaIdentidad.classList.add('hidden');
                errorReniec.classList.add('hidden');
                errorReniec.classList.remove('flex');
            };

            const mostrarIdentidad = () => {
                cajaIdentidad.classList.remove('hidden');
                window.requestAnimationFrame(() => inputNombres.focus());
            };

            const consultarReniec = async (dni) => {
                spinner.classList.remove('hidden');
                spinner.classList.add('flex');
                estadoReniec.textContent = 'Buscando…';
                estadoReniec.className = 'font-mono text-[10px] text-brand-700';

                try {
                    const respuesta = await fetch(`/api/consultar-dni/${encodeURIComponent(dni)}`);
                    const data = await respuesta.json();

                    if (data.success) {
                        inputNombres.value = data.nombres || '';
                        inputApellidos.value = data.apellidos || '';
                        mostrarIdentidad();
                        estadoReniec.textContent = 'Datos encontrados';
                        estadoReniec.className = 'font-mono text-[10px] text-brand-700';
                    } else {
                        mostrarIdentidad();
                        errorReniec.classList.remove('hidden');
                        errorReniec.classList.add('flex');
                        estadoReniec.textContent = 'Completa tus datos';
                        estadoReniec.className = 'font-mono text-[10px] text-maize-700';
                    }
                } catch (error) {
                    mostrarIdentidad();
                    errorReniec.classList.remove('hidden');
                    errorReniec.classList.add('flex');
                    estadoReniec.textContent = 'Sin conexión';
                    estadoReniec.className = 'font-mono text-[10px] text-maize-700';
                } finally {
                    spinner.classList.add('hidden');
                    spinner.classList.remove('flex');
                }
            };

            inputDni.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '').slice(0, 8);

                if (this.value.length === 8) {
                    consultarReniec(this.value);
                } else {
                    ocultarIdentidad();
                    estadoReniec.textContent = '';
                    spinner.classList.add('hidden');
                }
            });
        })();
    </script>
@endpush
