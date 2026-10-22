@extends('hampi.layout')

@section('title', 'Mi panel · Hampi')
@section('meta_description', 'Consulta tus próximas citas y accesos rápidos en la Microred Socabaya.')

@section('content')
    @php
        $proxima = $citas->first(fn ($cita) => $cita->fecha_hora
            && $cita->fecha_hora->isFuture()
            && in_array($cita->estado, ['Confirmada', 'Pendiente'], true));
        $citasOrdenadas = $citas->sortByDesc('fecha_hora');
        $estados = [
            'Confirmada' => 'badge-teal',
            'Pendiente' => 'badge-amber',
            'Atendida' => 'badge-slate',
            'Cancelada' => 'badge-red',
        ];
    @endphp

    <x-page-board>
        <x-slot:hero>
            <x-page-hero
                eyebrow="Panel ciudadano"
                :title="'Hola, ' . $ciudadano->nombres"
                :description="'DNI ' . $ciudadano->dni . ' · Microred Socabaya, Arequipa'"
                icon="user-check"
            >
                <x-slot:actions>
                    <a href="/medicos-hoy" class="btn btn-primary btn-sm">
                        <x-icon name="stethoscope" class="h-4 w-4" />
                        Médicos de hoy
                    </a>
                    <a href="/cerrar-sesion-ciudadano" class="btn btn-secondary btn-sm">
                        <x-icon name="log-out" class="h-4 w-4" />
                        Cerrar sesión
                    </a>
                </x-slot:actions>
            </x-page-hero>
        </x-slot:hero>

        <div class="container-page py-8 md:py-12">
            <div class="grid gap-6 lg:grid-cols-12">
                <div class="space-y-7 lg:col-span-8">
                    @if ($proxima)
                        <article class="grid border-2 border-ink-950 bg-paper-50 sm:grid-cols-12" data-reveal>
                            <div class="flex items-end justify-between gap-3 border-b border-ink-950 bg-brand-900 p-5 text-paper-50 sm:col-span-3 sm:flex-col sm:items-start sm:justify-between sm:border-b-0 sm:border-r sm:p-6">
                                <div>
                                    <p class="mono-label text-paper-100/60">Próxima atención</p>
                                    <p class="mt-3 font-display text-6xl font-extrabold leading-none tracking-[-0.06em]">{{ $proxima->fecha_hora->format('d') }}</p>
                                    <p class="mt-1 font-mono text-[10px] uppercase tracking-[0.08em] text-paper-100/70">
                                        {{ $proxima->fecha_hora->locale('es')->isoFormat('MMM YYYY') }}
                                    </p>
                                </div>
                                <span class="border border-paper-50/30 px-2 py-1 font-mono text-[10px] uppercase tracking-[0.06em] text-paper-50">{{ $proxima->estado }}</span>
                            </div>

                            <div class="p-5 sm:col-span-9 sm:p-6">
                                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-ink-300 pb-3">
                                    <span class="mono-label">Cita programada</span>
                                    <span class="font-mono text-[10px] uppercase tracking-[0.05em] text-ink-500">{{ $proxima->fecha_hora->format('H:i') }}</span>
                                </div>

                                <h2 class="mt-4 font-display text-2xl font-bold tracking-[-0.03em] text-ink-950 sm:text-3xl">{{ $proxima->especialidad }}</h2>

                                <div class="mt-3 space-y-1.5 text-sm text-ink-600">
                                    <p class="flex items-center gap-2">
                                        <x-icon name="building-2" class="h-4 w-4 shrink-0 text-brand-700" />
                                        {{ $proxima->posta->nombre ?? 'Establecimiento de la Microred Socabaya' }}
                                    </p>
                                    @if ($proxima->medico)
                                        <p class="flex items-center gap-2">
                                            <x-icon name="stethoscope" class="h-4 w-4 shrink-0 text-brand-700" />
                                            {{ $proxima->medico->name }}
                                        </p>
                                    @endif
                                </div>

                                <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-ink-200 pt-4">
                                    <span class="font-mono text-[10px] uppercase tracking-[0.05em] text-ink-500">Llega 10 minutos antes</span>
                                    <a href="/directorio-postas" class="link-arrow">
                                        Ver ubicación de la posta
                                        <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                                    </a>
                                </div>
                            </div>
                        </article>
                    @else
                        <x-empty-state
                            icon="calendar"
                            title="No tienes citas próximas"
                            description="Cuando el personal de salud registre una cita a tu nombre, aparecerá aquí con la fecha, la especialidad y el establecimiento."
                            data-reveal
                        >
                            <x-slot:action>
                                <a href="/medicos-hoy" class="btn btn-primary btn-sm">
                                    <x-icon name="stethoscope" class="h-4 w-4" />
                                    Ver médicos de hoy
                                </a>
                            </x-slot:action>
                        </x-empty-state>
                    @endif

                    <section data-reveal>
                        <div class="flex flex-wrap items-end justify-between gap-3 border-b-2 border-ink-950 pb-2">
                            <div>
                                <h2 class="font-display text-xl font-bold text-ink-950">Mis citas</h2>
                                <p class="mt-1 text-xs text-ink-600">Registro de atenciones asociadas a tu DNI.</p>
                            </div>
                            <span class="badge badge-slate">{{ $citas->count() }} {{ $citas->count() === 1 ? 'cita' : 'citas' }}</span>
                        </div>

                        @if ($citas->isEmpty())
                            <p class="border-b border-ink-200 py-8 text-sm text-ink-600">Aún no hay citas registradas para tu DNI.</p>
                        @else
                            <div class="divide-y divide-ink-200">
                                @foreach ($citasOrdenadas as $cita)
                                    <article class="grid gap-3 py-4 sm:grid-cols-12 sm:items-start">
                                        <p class="font-mono text-[10px] uppercase leading-relaxed tracking-[0.05em] text-ink-500 sm:col-span-3">
                                            {{ $cita->fecha_hora ? $cita->fecha_hora->locale('es')->isoFormat('ddd D MMM YYYY') : 'Fecha por confirmar' }}
                                            @if ($cita->fecha_hora)
                                                <span class="mt-0.5 block text-ink-700">{{ $cita->fecha_hora->format('H:i') }}</span>
                                            @endif
                                        </p>

                                        <div class="sm:col-span-7">
                                            <h3 class="font-display text-base font-bold text-ink-950">{{ $cita->especialidad }}</h3>
                                            <p class="mt-1 text-xs leading-relaxed text-ink-600">
                                                @if ($cita->posta)
                                                    {{ $cita->posta->nombre }}
                                                @endif
                                                @if ($cita->medico)
                                                    <span class="text-ink-300" aria-hidden="true"> / </span>{{ $cita->medico->name }}
                                                @endif
                                            </p>
                                        </div>

                                        <div class="sm:col-span-2 sm:text-right">
                                            <span class="badge {{ $estados[$cita->estado] ?? 'badge-slate' }}">{{ $cita->estado }}</span>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </section>
                </div>

                <aside class="space-y-6 lg:col-span-4">
                    <section class="border border-ink-300 bg-paper-50 p-5" data-reveal>
                        <div class="flex items-center gap-3">
                            <span class="grid h-12 w-12 place-items-center border border-ink-950 bg-brand-700 font-display text-lg font-bold text-paper-50">
                                {{ mb_substr($ciudadano->nombres, 0, 1) }}{{ mb_substr($ciudadano->apellidos, 0, 1) }}
                            </span>
                            <div class="min-w-0">
                                <h2 class="truncate font-display text-base font-bold text-ink-950">{{ $ciudadano->nombres }} {{ $ciudadano->apellidos }}</h2>
                                <p class="font-mono text-[10px] uppercase tracking-[0.05em] text-ink-500">DNI {{ $ciudadano->dni }}</p>
                            </div>
                        </div>

                        <dl class="mt-5 divide-y divide-ink-200 border-t border-ink-200 text-sm">
                            <div class="flex items-center justify-between gap-3 py-2.5">
                                <dt class="text-ink-600">Documento</dt>
                                <dd class="font-mono text-xs font-semibold text-ink-800">{{ $ciudadano->dni }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-2.5">
                                <dt class="text-ink-600">Jurisdicción</dt>
                                <dd class="font-semibold text-ink-800">Socabaya</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-2.5">
                                <dt class="text-ink-600">Sesión</dt>
                                <dd class="badge badge-teal">Activa</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="border-t-2 border-ink-950" data-reveal>
                        <h2 class="border-b border-ink-300 py-3 font-display text-base font-bold text-ink-950">Accesos rápidos</h2>
                        <div class="divide-y divide-ink-200">
                            <a href="/medicos-hoy" class="flex min-h-12 items-center gap-3 py-3 text-sm font-semibold text-ink-800 transition-colors hover:text-brand-700">
                                <x-icon name="stethoscope" class="h-4 w-4 text-brand-700" />
                                <span class="flex-1">Médicos de hoy</span>
                                <x-icon name="arrow-right" class="h-4 w-4 text-ink-400" />
                            </a>
                            <a href="/pizarrin-vacunas" class="flex min-h-12 items-center gap-3 py-3 text-sm font-semibold text-ink-800 transition-colors hover:text-brand-700">
                                <x-icon name="syringe" class="h-4 w-4 text-brand-700" />
                                <span class="flex-1">Pizarrín de vacunas</span>
                                <x-icon name="arrow-right" class="h-4 w-4 text-ink-400" />
                            </a>
                            <a href="/directorio-postas" class="flex min-h-12 items-center gap-3 py-3 text-sm font-semibold text-ink-800 transition-colors hover:text-brand-700">
                                <x-icon name="map-pin" class="h-4 w-4 text-brand-700" />
                                <span class="flex-1">Directorio de postas</span>
                                <x-icon name="arrow-right" class="h-4 w-4 text-ink-400" />
                            </a>
                        </div>
                    </section>

                    <section class="border border-maize-300 bg-maize-50 p-5" data-reveal>
                        <p class="flex items-center gap-2 font-mono text-[10px] font-semibold uppercase tracking-[0.08em] text-maize-900">
                            <x-icon name="info" class="h-4 w-4" />
                            Recuerda
                        </p>
                        <p class="mt-2 text-xs leading-relaxed text-maize-900/90">
                            Lleva tu DNI físico a tu atención. Si no puedes asistir, comunícate con tu establecimiento de salud para reprogramar tu cita.
                        </p>
                    </section>
                </aside>
            </div>
        </div>
    </x-page-board>
@endsection
