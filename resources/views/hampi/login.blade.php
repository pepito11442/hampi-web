@extends('hampi.layout')

@section('title', 'Hampi · Tu salud, más cerca | Socabaya, Arequipa')
@section('meta_description', 'Consulta médicos de turno, disponibilidad de vacunas y el directorio de postas de Socabaya. Ingresa con tu DNI y revisa tus citas en un solo lugar.')

@section('content')
    @php
        $turnosHoy = $stats['turnos'] ?? 0;
        $vacunasDisponibles = $stats['vacunas'] ?? 0;
    @endphp

    {{-- ============================== PORTADA ============================== --}}
    <section class="relative overflow-hidden bg-white">
        <div class="pointer-events-none absolute inset-0 bg-hero-glow" aria-hidden="true"></div>

        <div class="container-page relative grid items-center gap-12 py-14 lg:grid-cols-12 lg:py-24">
            <div class="lg:col-span-6" data-reveal>
                <span class="inline-flex items-center gap-2 rounded-full border border-brand-200/70 bg-white/80 px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-[0.14em] text-brand-800 shadow-sm">
                    <span class="h-1.5 w-1.5 rounded-full bg-brand-500" aria-hidden="true"></span>
                    Socabaya · Arequipa
                </span>

                <h1 class="mt-6 text-balance text-4xl font-bold leading-[1.08] tracking-tight text-ink-900 sm:text-5xl lg:text-6xl">
                    Tu salud, <span class="text-brand-600">más cerca</span> de casa.
                </h1>

                <p class="mt-5 max-w-xl text-base leading-relaxed text-ink-500 sm:text-lg">
                    Consulta los médicos de turno, la disponibilidad de vacunas y el directorio de postas de tu comunidad. Ingresa con tu DNI y revisa tus citas en un solo lugar.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="#acceso" class="btn btn-primary btn-lg">
                        <x-icon name="id-card" class="h-5 w-5" />
                        Ingresar con mi DNI
                    </a>
                    <a href="/medicos-hoy" class="btn btn-secondary btn-lg">
                        <x-icon name="stethoscope" class="h-5 w-5 text-brand-600" />
                        Ver médicos de hoy
                    </a>
                </div>

                <ul class="mt-8 grid gap-3 text-sm text-ink-600 sm:grid-cols-2">
                    <li class="flex items-center gap-2.5">
                        <x-icon name="shield-check" class="h-5 w-5 shrink-0 text-brand-600" />
                        Consultas protegidas por la Ley&nbsp;N.°&nbsp;29733
                    </li>
                    <li class="flex items-center gap-2.5">
                        <x-icon name="badge-check" class="h-5 w-5 shrink-0 text-brand-600" />
                        Información publicada por el personal de salud
                    </li>
                </ul>
            </div>

            <div class="lg:col-span-6" data-reveal style="--reveal-delay: 120ms">
                <div class="relative mx-auto max-w-md lg:max-w-lg">
                    <div class="absolute -inset-3 rotate-2 rounded-[2.5rem] bg-gradient-to-br from-brand-100 via-mist-100 to-sky-100" aria-hidden="true"></div>

                    <picture>
                        <source srcset="/images/hero-atencion.webp" type="image/webp">
                        <img
                            src="/images/hero-atencion.jpg"
                            alt="Profesional de salud con bata blanca sonriendo en un entorno comunitario"
                            width="1400"
                            height="1050"
                            fetchpriority="high"
                            decoding="async"
                            class="relative aspect-[4/5] w-full rounded-[2rem] object-cover object-center shadow-lift"
                        >
                    </picture>

                    <div class="absolute -left-3 bottom-10 flex items-center gap-3 rounded-2xl border border-ink-100 bg-white/95 p-3.5 shadow-lift backdrop-blur sm:-left-6 animate-float-soft">
                        <span class="grid h-11 w-11 place-items-center rounded-xl bg-brand-50 text-brand-700">
                            <x-icon name="stethoscope" class="h-5 w-5" />
                        </span>
                        <span>
                            <span class="block text-lg font-bold leading-none text-ink-900">{{ $turnosHoy }}</span>
                            <span class="mt-1 block text-[11px] font-semibold uppercase tracking-wide text-ink-500">{{ $turnosHoy === 1 ? 'Médico en turno' : 'Médicos en turno' }}</span>
                        </span>
                    </div>

                    <div class="absolute -right-2 top-8 flex items-center gap-3 rounded-2xl border border-ink-100 bg-white/95 p-3.5 shadow-lift backdrop-blur sm:-right-5">
                        <span class="grid h-11 w-11 place-items-center rounded-xl bg-sky-50 text-sky-700">
                            <x-icon name="syringe" class="h-5 w-5" />
                        </span>
                        <span>
                            <span class="block text-lg font-bold leading-none text-ink-900">{{ $vacunasDisponibles }}</span>
                            <span class="mt-1 block text-[11px] font-semibold uppercase tracking-wide text-ink-500">{{ $vacunasDisponibles === 1 ? 'Vacuna disponible' : 'Vacunas disponibles' }}</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </section>

    {{-- ========================== ACCESO CIUDADANO ========================== --}}
    <section id="acceso" class="scroll-mt-24 py-16 sm:py-24">
        <div class="container-page grid items-start gap-12 lg:grid-cols-2">
            <div data-reveal>
                <span class="eyebrow">Acceso ciudadano</span>
                <h2 class="section-title mt-3">Tus servicios de salud, en un solo lugar</h2>
                <p class="section-lead mt-4 max-w-xl">
                    No necesitas crear una cuenta ni recordar contraseñas. Ingresa tu DNI para consultar tus citas y moverte por el portal con tus datos ya cargados.
                </p>

                <ul class="mt-8 space-y-5">
                    <li class="flex gap-4">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-700">
                            <x-icon name="calendar-check" class="h-5 w-5" />
                        </span>
                        <span>
                            <span class="block text-sm font-bold text-ink-900">Revisa tus próximas citas</span>
                            <span class="mt-1 block text-sm leading-relaxed text-ink-500">Consulta fecha, especialidad y establecimiento de tu atención programada.</span>
                        </span>
                    </li>
                    <li class="flex gap-4">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-sky-50 text-sky-700">
                            <x-icon name="stethoscope" class="h-5 w-5" />
                        </span>
                        <span>
                            <span class="block text-sm font-bold text-ink-900">Conoce los turnos del día</span>
                            <span class="mt-1 block text-sm leading-relaxed text-ink-500">Especialidades, consultorios y estado de atención de cada médico.</span>
                        </span>
                    </li>
                    <li class="flex gap-4">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-amber-50 text-amber-700">
                            <x-icon name="syringe" class="h-5 w-5" />
                        </span>
                        <span>
                            <span class="block text-sm font-bold text-ink-900">Entérate de las vacunas disponibles</span>
                            <span class="mt-1 block text-sm leading-relaxed text-ink-500">El pizarrín digital se actualiza con la información del personal de salud.</span>
                        </span>
                    </li>
                </ul>
            </div>

            <div class="card p-6 sm:p-8" data-reveal style="--reveal-delay: 120ms">
                <div class="flex items-center gap-4">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-glow">
                        <x-icon name="shield-check" class="h-6 w-6" />
                    </span>
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-ink-900">Acceso con tu DNI</h3>
                        <p class="text-xs text-ink-500">Validación automática con el padrón RENIEC</p>
                    </div>
                </div>

                <form action="/ingreso-dni" method="POST" class="mt-6 space-y-5">
                    @csrf

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label for="dni" class="label mb-0">Documento Nacional de Identidad</label>
                            <span id="estado-reniec" aria-live="polite" class="text-[11px] font-semibold text-ink-400"></span>
                        </div>

                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-ink-400" aria-hidden="true">
                                <x-icon name="id-card" class="h-5 w-5" />
                            </span>
                            <input
                                type="text"
                                name="dni"
                                id="dni"
                                maxlength="8"
                                inputmode="numeric"
                                pattern="[0-9]{8}"
                                autocomplete="off"
                                required
                                placeholder="Ej. 72345678"
                                aria-describedby="dni-ayuda"
                                class="input pl-12 pr-12 font-mono text-base tracking-[0.2em]"
                            >
                            <span id="spinner" class="absolute inset-y-0 right-0 hidden items-center pr-4 text-brand-600">
                                <x-icon name="loader" class="h-5 w-5 animate-spin" />
                            </span>
                        </div>
                        <p id="dni-ayuda" class="mt-2 text-[11px] text-ink-400">8 dígitos, sin espacios ni guiones.</p>
                    </div>

                    <div id="caja-identidad" class="hidden space-y-4 rounded-2xl border border-brand-100 bg-brand-50/60 p-4">
                        <p class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-brand-800">
                            <x-icon name="user" class="h-3.5 w-3.5" />
                            Datos del ciudadano (editables)
                        </p>

                        <div>
                            <label for="nombres_reniec" class="label">Nombres</label>
                            <input type="text" name="nombres_reniec" id="nombres_reniec" class="input bg-white">
                        </div>

                        <div>
                            <label for="apellidos_reniec" class="label">Apellidos</label>
                            <input type="text" name="apellidos_reniec" id="apellidos_reniec" class="input bg-white">
                        </div>
                    </div>

                    <div id="error-reniec" class="hidden items-start gap-2 rounded-2xl border border-amber-200 bg-amber-50 p-3.5 text-xs leading-relaxed text-amber-800">
                        <x-icon name="alert-circle" class="mt-0.5 h-4 w-4 shrink-0" />
                        <span>No pudimos validar tu DNI en este momento. Puedes escribir tus nombres y apellidos manualmente para continuar.</span>
                    </div>

                    <button type="submit" id="btn-ingresar" class="btn btn-primary w-full">
                        <span>Ingresar a mis servicios</span>
                        <x-icon name="arrow-right" class="h-4 w-4" />
                    </button>

                    <p class="flex items-center justify-center gap-2 text-center text-[11px] text-ink-400">
                        <x-icon name="lock" class="h-3.5 w-3.5" />
                        Tus datos se usan únicamente para identificarte en el portal.
                    </p>
                </form>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-ink-100 pt-5 text-xs text-ink-500">
                    <span>¿Solo deseas consultar?</span>
                    <a href="/pizarrin-vacunas" class="inline-flex items-center gap-1 font-semibold text-brand-700 transition-colors hover:text-brand-800">
                        Ver campañas de vacunación
                        <x-icon name="chevron-right" class="h-3.5 w-3.5" />
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================= SERVICIOS ============================= --}}
    <section class="border-y border-ink-100 bg-white/65 py-16 backdrop-blur-sm sm:py-24">
        <div class="container-page">
            <div class="mx-auto max-w-2xl text-center" data-reveal>
                <span class="eyebrow">Servicios del portal</span>
                <h2 class="section-title mt-3">Todo lo que necesitas, sin filas ni llamadas</h2>
                <p class="section-lead mt-4">
                    Información actualizada por el personal de la Microred Socabaya para que llegues preparado a tu atención.
                </p>
            </div>

            <div class="mt-12 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                <a href="/medicos-hoy" class="card card-hover group flex flex-col p-6" data-reveal>
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-brand-50 text-brand-700 transition-colors group-hover:bg-brand-600 group-hover:text-white">
                        <x-icon name="stethoscope" class="h-6 w-6" />
                    </span>
                    <h3 class="mt-5 text-lg font-bold tracking-tight text-ink-900">Médicos de turno hoy</h3>
                    <p class="mt-2 flex-1 text-sm leading-relaxed text-ink-500">
                        Consulta las especialidades, los consultorios y el estado de atención del turno de mañana y tarde.
                    </p>
                    <span class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700">
                        Ver el rol de hoy
                        <x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                    </span>
                </a>

                <a href="/pizarrin-vacunas" class="card card-hover group flex flex-col p-6" data-reveal style="--reveal-delay: 90ms">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-sky-50 text-sky-700 transition-colors group-hover:bg-sky-600 group-hover:text-white">
                        <x-icon name="syringe" class="h-6 w-6" />
                    </span>
                    <h3 class="mt-5 text-lg font-bold tracking-tight text-ink-900">Campañas de vacunación</h3>
                    <p class="mt-2 flex-1 text-sm leading-relaxed text-ink-500">
                        Revisa qué vacunas están disponibles, para qué grupo están dirigidas y qué documentos debes llevar.
                    </p>
                    <span class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-sky-700">
                        Ver disponibilidad
                        <x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                    </span>
                </a>

                <a href="/campanas-preventivas" class="card card-hover group flex flex-col p-6" data-reveal style="--reveal-delay: 180ms">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-violet-50 text-violet-700 transition-colors group-hover:bg-violet-600 group-hover:text-white">
                        <x-icon name="shield-check" class="h-6 w-6" />
                    </span>
                    <h3 class="mt-5 text-lg font-bold tracking-tight text-ink-900">Campañas preventivas</h3>
                    <p class="mt-2 flex-1 text-sm leading-relaxed text-ink-500">
                        Control de presión, diabetes, anemia y otras actividades gratuitas de prevención en tu comunidad.
                    </p>
                    <span class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-violet-700">
                        Ver campañas
                        <x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                    </span>
                </a>

                <a href="/directorio-postas" class="card card-hover group flex flex-col p-6" data-reveal style="--reveal-delay: 270ms">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-amber-50 text-amber-700 transition-colors group-hover:bg-amber-500 group-hover:text-white">
                        <x-icon name="map-pin" class="h-6 w-6" />
                    </span>
                    <h3 class="mt-5 text-lg font-bold tracking-tight text-ink-900">Directorio de postas</h3>
                    <p class="mt-2 flex-1 text-sm leading-relaxed text-ink-500">
                        Ubica los establecimientos de la microred, con su dirección, teléfono y horario de atención.
                    </p>
                    <span class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-amber-700">
                        Ver establecimientos
                        <x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                    </span>
                </a>
            </div>
        </div>
    </section>

    {{-- ================================= FAQ ================================= --}}
    <section class="py-16 sm:py-24">
        <div class="container-page grid gap-12 lg:grid-cols-12">
            <div class="lg:col-span-4" data-reveal>
                <span class="eyebrow">Preguntas frecuentes</span>
                <h2 class="section-title mt-3">Resolvemos tus dudas</h2>
                <p class="section-lead mt-4">
                    Si tu consulta no aparece aquí, acércate a tu establecimiento de salud más cercano.
                </p>
                <a href="/directorio-postas" class="btn btn-secondary mt-6">
                    <x-icon name="map-pin" class="h-4 w-4 text-brand-600" />
                    Ver postas y horarios
                </a>
            </div>

            <div class="space-y-3 lg:col-span-8" data-reveal style="--reveal-delay: 120ms">
                <details class="faq-item card group overflow-hidden">
                    <summary class="flex cursor-pointer items-center justify-between gap-4 p-5 text-sm font-bold text-ink-900 transition-colors hover:text-brand-700">
                        ¿Necesito crear una cuenta para usar el portal?
                        <x-icon name="chevron-down" class="h-5 w-5 shrink-0 text-ink-400 transition-transform duration-300 group-open:rotate-180" />
                    </summary>
                    <p class="border-t border-ink-100 px-5 py-4 text-sm leading-relaxed text-ink-500">
                        No. Solo necesitas tu número de DNI. El portal valida tus datos con RENIEC y, si el servicio no está disponible, puedes escribir tus nombres y apellidos manualmente.
                    </p>
                </details>

                <details class="faq-item card group overflow-hidden">
                    <summary class="flex cursor-pointer items-center justify-between gap-4 p-5 text-sm font-bold text-ink-900 transition-colors hover:text-brand-700">
                        ¿Qué información puedo consultar?
                        <x-icon name="chevron-down" class="h-5 w-5 shrink-0 text-ink-400 transition-transform duration-300 group-open:rotate-180" />
                    </summary>
                    <p class="border-t border-ink-100 px-5 py-4 text-sm leading-relaxed text-ink-500">
                        Tus citas programadas, los médicos de turno del día con su especialidad y consultorio, la disponibilidad de vacunas del pizarrín digital y el directorio de postas de la microred.
                    </p>
                </details>

                <details class="faq-item card group overflow-hidden">
                    <summary class="flex cursor-pointer items-center justify-between gap-4 p-5 text-sm font-bold text-ink-900 transition-colors hover:text-brand-700">
                        ¿Mis datos personales están protegidos?
                        <x-icon name="chevron-down" class="h-5 w-5 shrink-0 text-ink-400 transition-transform duration-300 group-open:rotate-180" />
                    </summary>
                    <p class="border-t border-ink-100 px-5 py-4 text-sm leading-relaxed text-ink-500">
                        Sí. El tratamiento de tus datos se rige por la Ley N.° 29733 de Protección de Datos Personales. La sesión ciudadana se mantiene solo durante tu visita y puedes cerrarla en cualquier momento.
                    </p>
                </details>

            </div>
        </div>
    </section>

    {{-- ================================= CTA ================================= --}}
    <section class="pb-20">
        <div class="container-page">
            <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-brand-600 via-brand-700 to-ink-900 px-6 py-14 text-white shadow-lift sm:px-12" data-reveal>
                <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -bottom-20 left-1/3 h-56 w-56 rounded-full bg-brand-400/20 blur-3xl" aria-hidden="true"></div>

                <div class="relative flex flex-col items-start gap-8 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-xl">
                        <h2 class="text-3xl font-bold leading-tight tracking-tight sm:text-4xl">Empieza tu consulta ahora</h2>
                        <p class="mt-4 text-base leading-relaxed text-white/80">
                            Ingresa con tu DNI y revisa tus citas y los servicios disponibles en la Microred Socabaya.
                        </p>
                    </div>

                    <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                        <a href="#acceso" class="btn bg-white text-brand-800 hover:bg-brand-50">
                            <x-icon name="id-card" class="h-4 w-4" />
                            Ingresar con mi DNI
                        </a>
                        <a href="/medicos-hoy" class="btn border border-white/30 text-white hover:bg-white/10">
                            Ver médicos de hoy
                        </a>
                    </div>
                </div>
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
                estadoReniec.className = 'text-[11px] font-semibold text-brand-700';

                try {
                    const respuesta = await fetch(`/api/consultar-dni/${encodeURIComponent(dni)}`);
                    const data = await respuesta.json();

                    if (data.success) {
                        inputNombres.value = data.nombres || '';
                        inputApellidos.value = data.apellidos || '';
                        mostrarIdentidad();
                        estadoReniec.textContent = 'Datos encontrados ✓';
                        estadoReniec.className = 'text-[11px] font-semibold text-brand-700';
                    } else {
                        mostrarIdentidad();
                        errorReniec.classList.remove('hidden');
                        errorReniec.classList.add('flex');
                        estadoReniec.textContent = 'Completa tus datos';
                        estadoReniec.className = 'text-[11px] font-semibold text-amber-700';
                    }
                } catch (error) {
                    mostrarIdentidad();
                    errorReniec.classList.remove('hidden');
                    errorReniec.classList.add('flex');
                    estadoReniec.textContent = 'Sin conexión';
                    estadoReniec.className = 'text-[11px] font-semibold text-amber-700';
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
