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

    <div class="container-page py-8 md:py-12">
        <div class="grid gap-6 lg:grid-cols-12">
            <div class="space-y-6 lg:col-span-8">
                {{-- Próxima cita --}}
                @if ($proxima)
                    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 via-brand-700 to-ink-900 p-6 text-white shadow-lift sm:p-8" data-reveal>
                        <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>
                        <div class="pointer-events-none absolute -bottom-20 left-1/4 h-52 w-52 rounded-full bg-brand-400/20 blur-3xl" aria-hidden="true"></div>

                        <div class="relative">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-white">
                                    <x-icon name="calendar-check" class="h-4 w-4" />
                                    Próxima atención
                                </span>
                                <span class="rounded-full bg-white/15 px-3 py-1.5 text-[11px] font-bold text-white">{{ $proxima->estado }}</span>
                            </div>

                            <h2 class="mt-6 text-2xl font-bold tracking-tight sm:text-3xl">{{ $proxima->especialidad }}</h2>
                            <p class="mt-2 flex items-center gap-2 text-sm text-white/80">
                                <x-icon name="building-2" class="h-4 w-4 shrink-0" />
                                {{ $proxima->posta->nombre ?? 'Establecimiento de la Microred Socabaya' }}
                            </p>

                            @if ($proxima->medico)
                                <p class="mt-1 flex items-center gap-2 text-sm text-white/80">
                                    <x-icon name="stethoscope" class="h-4 w-4 shrink-0" />
                                    {{ $proxima->medico->name }}
                                </p>
                            @endif

                            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                                <div class="flex items-center gap-3 rounded-2xl border border-white/15 bg-white/10 p-3.5 backdrop-blur">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white/15">
                                        <x-icon name="calendar" class="h-5 w-5" />
                                    </span>
                                    <span>
                                        <span class="block text-[10px] font-bold uppercase tracking-wider text-white/60">Fecha</span>
                                        <span class="mt-0.5 block text-sm font-semibold">{{ $proxima->fecha_hora->locale('es')->isoFormat('dddd D [de] MMMM') }}</span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 rounded-2xl border border-white/15 bg-white/10 p-3.5 backdrop-blur">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white/15">
                                        <x-icon name="clock" class="h-5 w-5" />
                                    </span>
                                    <span>
                                        <span class="block text-[10px] font-bold uppercase tracking-wider text-white/60">Hora</span>
                                        <span class="mt-0.5 block text-sm font-semibold">{{ $proxima->fecha_hora->format('h:i a') }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </section>
                @else
                    <x-empty-state
                        icon="calendar"
                        title="No tienes citas programadas"
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

                {{-- Historial de citas --}}
                <section class="card p-6" data-reveal>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-bold text-ink-900">Mis citas</h2>
                            <p class="mt-0.5 text-xs text-ink-500">Registro de atenciones asociadas a tu DNI.</p>
                        </div>
                        <span class="badge badge-slate">{{ $citas->count() }} {{ $citas->count() === 1 ? 'cita' : 'citas' }}</span>
                    </div>

                    @if ($citas->isEmpty())
                        <p class="mt-6 rounded-2xl border border-dashed border-ink-200 bg-ink-50/50 px-4 py-8 text-center text-sm text-ink-500">
                            Aún no hay citas registradas para tu DNI.
                        </p>
                    @else
                        <ul class="mt-5 divide-y divide-ink-100">
                            @foreach ($citasOrdenadas as $cita)
                                <li class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-start gap-3">
                                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700">
                                            <x-icon name="calendar" class="h-5 w-5" />
                                        </span>
                                        <div>
                                            <p class="text-sm font-bold text-ink-900">{{ $cita->especialidad }}</p>
                                            <p class="mt-0.5 text-xs text-ink-500">
                                                {{ $cita->fecha_hora ? $cita->fecha_hora->locale('es')->isoFormat('ddd D MMM YYYY, h:mm a') : 'Fecha por confirmar' }}
                                                @if ($cita->posta)
                                                    · {{ $cita->posta->nombre }}
                                                @endif
                                                @if ($cita->medico)
                                                    · {{ $cita->medico->name }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <span class="badge {{ $estados[$cita->estado] ?? 'badge-slate' }} self-start sm:self-auto">{{ $cita->estado }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>

            <aside class="space-y-6 lg:col-span-4">
                {{-- Datos del ciudadano --}}
                <section class="card p-6" data-reveal>
                    <div class="flex items-center gap-3">
                        <span class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 text-lg font-bold text-white shadow-glow">
                            {{ mb_substr($ciudadano->nombres, 0, 1) }}{{ mb_substr($ciudadano->apellidos, 0, 1) }}
                        </span>
                        <div class="min-w-0">
                            <h2 class="truncate text-sm font-bold text-ink-900">{{ $ciudadano->nombres }} {{ $ciudadano->apellidos }}</h2>
                            <p class="text-xs text-ink-500">DNI {{ $ciudadano->dni }}</p>
                        </div>
                    </div>

                    <dl class="mt-5 space-y-3 border-t border-ink-100 pt-5 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="flex items-center gap-2 text-ink-500">
                                <x-icon name="id-card" class="h-4 w-4 text-ink-400" />
                                Documento
                            </dt>
                            <dd class="font-semibold text-ink-800">{{ $ciudadano->dni }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="flex items-center gap-2 text-ink-500">
                                <x-icon name="map-pin" class="h-4 w-4 text-ink-400" />
                                Jurisdicción
                            </dt>
                            <dd class="font-semibold text-ink-800">Socabaya</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="flex items-center gap-2 text-ink-500">
                                <x-icon name="shield-check" class="h-4 w-4 text-ink-400" />
                                Sesión
                            </dt>
                            <dd class="font-semibold text-brand-700">Activa</dd>
                        </div>
                    </dl>
                </section>

                {{-- Accesos rápidos --}}
                <section class="card p-6" data-reveal style="--reveal-delay: 80ms">
                    <h2 class="text-sm font-bold text-ink-900">Accesos rápidos</h2>
                    <div class="mt-4 space-y-2">
                        <a href="/medicos-hoy" class="group flex items-center gap-3 rounded-2xl border border-ink-100 p-3 transition-colors hover:border-brand-200 hover:bg-brand-50/60">
                            <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-50 text-brand-700">
                                <x-icon name="stethoscope" class="h-4 w-4" />
                            </span>
                            <span class="flex-1 text-sm font-semibold text-ink-800">Médicos de hoy</span>
                            <x-icon name="chevron-right" class="h-4 w-4 text-ink-300 transition-transform group-hover:translate-x-0.5" />
                        </a>
                        <a href="/pizarrin-vacunas" class="group flex items-center gap-3 rounded-2xl border border-ink-100 p-3 transition-colors hover:border-brand-200 hover:bg-brand-50/60">
                            <span class="grid h-9 w-9 place-items-center rounded-xl bg-sky-50 text-sky-700">
                                <x-icon name="syringe" class="h-4 w-4" />
                            </span>
                            <span class="flex-1 text-sm font-semibold text-ink-800">Pizarrín de vacunas</span>
                            <x-icon name="chevron-right" class="h-4 w-4 text-ink-300 transition-transform group-hover:translate-x-0.5" />
                        </a>
                        <a href="/directorio-postas" class="group flex items-center gap-3 rounded-2xl border border-ink-100 p-3 transition-colors hover:border-brand-200 hover:bg-brand-50/60">
                            <span class="grid h-9 w-9 place-items-center rounded-xl bg-amber-50 text-amber-700">
                                <x-icon name="map-pin" class="h-4 w-4" />
                            </span>
                            <span class="flex-1 text-sm font-semibold text-ink-800">Directorio de postas</span>
                            <x-icon name="chevron-right" class="h-4 w-4 text-ink-300 transition-transform group-hover:translate-x-0.5" />
                        </a>
                    </div>
                </section>

                <section class="rounded-3xl border border-brand-100 bg-brand-50/70 p-5" data-reveal style="--reveal-delay: 160ms">
                    <p class="flex items-center gap-2 text-xs font-bold text-brand-800">
                        <x-icon name="info" class="h-4 w-4" />
                        Recuerda
                    </p>
                    <p class="mt-2 text-xs leading-relaxed text-brand-900/80">
                        Lleva tu DNI físico a tu atención. Si no puedes asistir, comunícate con tu establecimiento de salud para reprogramar tu cita.
                    </p>
                </section>
            </aside>
        </div>
    </div>
@endsection
