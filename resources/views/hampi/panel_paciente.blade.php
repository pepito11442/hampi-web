@extends('hampi.layout')

@section('title', 'Mi panel · Hampi')
@section('meta_description', 'Consulta y pide tus citas en la Microred Socabaya.')

@section('content')
    @php
        $activos = ['Confirmada', 'Pendiente'];
        $proxima = $citas->first(fn ($cita) => $cita->fecha_hora
            && $cita->fecha_hora->isFuture()
            && in_array($cita->estado, $activos, true));
        $citasOrdenadas = $citas->sortByDesc('fecha_hora');
        $estados = [
            'Confirmada' => 'badge-teal',
            'Pendiente' => 'badge-amber',
            'Atendida' => 'badge-slate',
            'Cancelada' => 'badge-red',
        ];
        $etiquetaEstado = [
            'Confirmada' => 'Confirmada',
            'Pendiente' => 'Reservada',
            'Atendida' => 'Atendida',
            'Cancelada' => 'Cancelada',
        ];

        $mapa = fn ($posta) => 'https://www.google.com/maps/search/?api=1&query='
            .urlencode(trim(($posta?->nombre ?? 'Microred Socabaya').' '.($posta?->direccion ?? '').' Socabaya Arequipa'));

        $whatsapp = function ($cita) {
            $texto = "Mi cita en la posta (Hampi):\n"
                .'📅 '.$cita->fecha_hora->locale('es')->isoFormat('dddd D [de] MMMM, HH:mm')."\n"
                .'🩺 '.$cita->especialidad.($cita->medico ? ' con '.$cita->medico->name : '')."\n"
                .'🏥 '.($cita->posta->nombre ?? 'Microred Socabaya').($cita->posta?->direccion ? ' - '.$cita->posta->direccion : '')."\n"
                .'Llevar DNI y llegar 15 minutos antes.';

            return 'https://wa.me/?text='.rawurlencode($texto);
        };
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
                    <a href="{{ route('ciudadano.citas.create') }}" class="btn btn-primary btn-sm">
                        <x-icon name="calendar-check" class="h-4 w-4" />
                        Pedir una cita
                    </a>
                    <form action="{{ route('ciudadano.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">
                            <x-icon name="log-out" class="h-4 w-4" />
                            Cerrar sesión
                        </button>
                    </form>
                </x-slot:actions>
            </x-page-hero>
        </x-slot:hero>

        <div class="container-page py-8 md:py-12">
            @error('cita')
                <div role="alert" class="notice notice-warning mb-6 flex items-start gap-2 text-sm">
                    <x-icon name="alert-circle" class="mt-0.5 h-4 w-4 shrink-0" />
                    <span>{{ $message }}</span>
                </div>
            @enderror

            <div class="grid gap-6 lg:grid-cols-12">
                <div class="space-y-7 lg:col-span-8">
                    @if ($proxima)
                        <article class="grid border-2 border-ink-950 bg-paper-50 sm:grid-cols-12" data-reveal>
                            <div class="flex items-end justify-between gap-3 border-b border-ink-950 bg-brand-900 p-5 text-paper-50 sm:col-span-3 sm:flex-col sm:items-start sm:justify-between sm:border-b-0 sm:border-r sm:p-6">
                                <div>
                                    <p class="mono-label text-paper-100/70">Próxima atención</p>
                                    <p class="mt-3 font-display text-6xl font-extrabold leading-none tracking-[-0.06em]">{{ $proxima->fecha_hora->format('d') }}</p>
                                    <p class="mt-1 font-mono text-xs uppercase tracking-[0.08em] text-paper-100/80">
                                        {{ $proxima->fecha_hora->locale('es')->isoFormat('MMM YYYY') }}
                                    </p>
                                </div>
                                <span class="border border-paper-50/40 px-2 py-1 font-mono text-[11px] uppercase tracking-[0.06em] text-paper-50">{{ $etiquetaEstado[$proxima->estado] ?? $proxima->estado }}</span>
                            </div>

                            <div class="p-5 sm:col-span-9 sm:p-6">
                                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-ink-300 pb-3">
                                    <span class="mono-label">{{ $proxima->fecha_hora->locale('es')->isoFormat('dddd') }}</span>
                                    <span class="font-mono text-lg font-semibold text-ink-950">{{ $proxima->fecha_hora->format('H:i') }}</span>
                                </div>

                                <h2 class="mt-4 font-display text-2xl font-bold tracking-[-0.03em] text-ink-950 sm:text-3xl">{{ $proxima->especialidad }}</h2>

                                <div class="mt-3 space-y-2 text-base text-ink-700">
                                    <p class="flex items-start gap-2">
                                        <x-icon name="building-2" class="mt-1 h-4 w-4 shrink-0 text-brand-700" />
                                        <span>
                                            {{ $proxima->posta->nombre ?? 'Establecimiento de la Microred Socabaya' }}
                                            @if ($proxima->posta?->direccion)
                                                <span class="block text-sm text-ink-500">{{ $proxima->posta->direccion }}</span>
                                            @endif
                                        </span>
                                    </p>
                                    @if ($proxima->medico)
                                        <p class="flex items-center gap-2">
                                            <x-icon name="stethoscope" class="h-4 w-4 shrink-0 text-brand-700" />
                                            {{ $proxima->medico->name }}
                                        </p>
                                    @endif
                                </div>

                                <p class="mt-4 text-sm text-ink-600">Lleva tu DNI físico y llega 15 minutos antes.</p>

                                <div class="mt-5 grid gap-2 border-t border-ink-200 pt-4 sm:grid-cols-3">
                                    <a href="{{ route('ciudadano.citas.ics', $proxima) }}" class="btn btn-secondary btn-sm justify-center">
                                        <x-icon name="calendar" class="h-4 w-4" />
                                        A mi calendario
                                    </a>
                                    <a href="{{ $whatsapp($proxima) }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm justify-center">
                                        <x-icon name="message-circle" class="h-4 w-4" />
                                        Enviar por WhatsApp
                                    </a>
                                    <a href="{{ $mapa($proxima->posta) }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm justify-center">
                                        <x-icon name="navigation" class="h-4 w-4" />
                                        Cómo llegar
                                    </a>
                                </div>
                            </div>
                        </article>
                    @else
                        <x-empty-state
                            icon="calendar"
                            title="No tienes citas próximas"
                            description="Puedes pedir una cita en línea con los médicos que tienen turnos en los próximos días. También la pueden registrar en tu posta."
                            data-reveal
                        >
                            <x-slot:action>
                                <a href="{{ route('ciudadano.citas.create') }}" class="btn btn-primary">
                                    <x-icon name="calendar-check" class="h-4 w-4" />
                                    Pedir una cita
                                </a>
                            </x-slot:action>
                        </x-empty-state>
                    @endif

                    <section data-reveal>
                        <div class="flex flex-wrap items-end justify-between gap-3 border-b-2 border-ink-950 pb-2">
                            <div>
                                <h2 class="font-display text-xl font-bold text-ink-950">Mis citas</h2>
                                <p class="mt-1 text-sm text-ink-600">Todas las citas asociadas a tu DNI.</p>
                            </div>
                            <span class="badge badge-slate">{{ $citas->count() }} {{ $citas->count() === 1 ? 'cita' : 'citas' }}</span>
                        </div>

                        @if ($citas->isEmpty())
                            <p class="border-b border-ink-200 py-8 text-base text-ink-600">Aún no hay citas registradas para tu DNI.</p>
                        @else
                            <div class="divide-y divide-ink-200">
                                @foreach ($citasOrdenadas as $cita)
                                    @php
                                        $puedeCancelar = $cita->fecha_hora
                                            && $cita->fecha_hora->isFuture()
                                            && in_array($cita->estado, $activos, true);
                                    @endphp
                                    <article class="grid gap-3 py-4 sm:grid-cols-12 sm:items-start">
                                        <p class="font-mono text-xs uppercase leading-relaxed tracking-[0.05em] text-ink-600 sm:col-span-3">
                                            {{ $cita->fecha_hora ? $cita->fecha_hora->locale('es')->isoFormat('ddd D MMM YYYY') : 'Fecha por confirmar' }}
                                            @if ($cita->fecha_hora)
                                                <span class="mt-0.5 block text-base font-semibold text-ink-900">{{ $cita->fecha_hora->format('H:i') }}</span>
                                            @endif
                                        </p>

                                        <div class="sm:col-span-6">
                                            <h3 class="font-display text-lg font-bold text-ink-950">{{ $cita->especialidad }}</h3>
                                            <p class="mt-1 text-sm leading-relaxed text-ink-600">
                                                @if ($cita->posta)
                                                    {{ $cita->posta->nombre }}
                                                @endif
                                                @if ($cita->medico)
                                                    <span class="text-ink-300" aria-hidden="true"> / </span>{{ $cita->medico->name }}
                                                @endif
                                            </p>

                                            @if ($puedeCancelar)
                                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm">
                                                    <a href="{{ route('ciudadano.citas.ics', $cita) }}" class="link-arrow">
                                                        <x-icon name="download" class="h-3.5 w-3.5" />
                                                        Calendario
                                                    </a>
                                                    <a href="{{ $whatsapp($cita) }}" target="_blank" rel="noopener" class="link-arrow">
                                                        <x-icon name="message-circle" class="h-3.5 w-3.5" />
                                                        WhatsApp
                                                    </a>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex flex-wrap items-center gap-2 sm:col-span-3 sm:flex-col sm:items-end">
                                            <span class="badge {{ $estados[$cita->estado] ?? 'badge-slate' }}">{{ $etiquetaEstado[$cita->estado] ?? $cita->estado }}</span>
                                            @if ($puedeCancelar)
                                                <form action="{{ route('ciudadano.citas.cancelar', $cita) }}" method="POST" data-confirm="¿Cancelar tu cita del {{ $cita->fecha_hora->locale('es')->isoFormat('D [de] MMMM [a las] HH:mm') }}?">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <x-icon name="x" class="h-4 w-4" />
                                                        Cancelar
                                                    </button>
                                                </form>
                                            @endif
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
                                <p class="font-mono text-xs uppercase tracking-[0.05em] text-ink-500">DNI {{ $ciudadano->dni }}</p>
                            </div>
                        </div>

                        <dl class="mt-5 divide-y divide-ink-200 border-t border-ink-200 text-sm">
                            <div class="flex items-center justify-between gap-3 py-2.5">
                                <dt class="text-ink-600">Teléfono</dt>
                                <dd class="font-mono text-xs font-semibold text-ink-800">{{ $ciudadano->telefono ?: 'Sin registrar' }}</dd>
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
                            <a href="{{ route('ciudadano.citas.create') }}" class="flex min-h-12 items-center gap-3 py-3 text-base font-semibold text-ink-800 transition-colors hover:text-brand-700">
                                <x-icon name="calendar-check" class="h-5 w-5 text-brand-700" />
                                <span class="flex-1">Pedir una cita</span>
                                <x-icon name="arrow-right" class="h-4 w-4 text-ink-400" />
                            </a>
                            <a href="/medicos-hoy" class="flex min-h-12 items-center gap-3 py-3 text-base font-semibold text-ink-800 transition-colors hover:text-brand-700">
                                <x-icon name="stethoscope" class="h-5 w-5 text-brand-700" />
                                <span class="flex-1">Médicos de hoy</span>
                                <x-icon name="arrow-right" class="h-4 w-4 text-ink-400" />
                            </a>
                            <a href="/pizarrin-vacunas" class="flex min-h-12 items-center gap-3 py-3 text-base font-semibold text-ink-800 transition-colors hover:text-brand-700">
                                <x-icon name="syringe" class="h-5 w-5 text-brand-700" />
                                <span class="flex-1">Pizarrín de vacunas</span>
                                <x-icon name="arrow-right" class="h-4 w-4 text-ink-400" />
                            </a>
                            <a href="/directorio-postas" class="flex min-h-12 items-center gap-3 py-3 text-base font-semibold text-ink-800 transition-colors hover:text-brand-700">
                                <x-icon name="map-pin" class="h-5 w-5 text-brand-700" />
                                <span class="flex-1">Directorio de postas</span>
                                <x-icon name="arrow-right" class="h-4 w-4 text-ink-400" />
                            </a>
                        </div>
                    </section>

                    <section class="border border-maize-300 bg-maize-50 p-5" data-reveal>
                        <p class="flex items-center gap-2 font-mono text-[11px] font-semibold uppercase tracking-[0.08em] text-maize-900">
                            <x-icon name="key-round" class="h-4 w-4" />
                            Cuida tu código
                        </p>
                        <p class="mt-2 text-sm leading-relaxed text-maize-900">
                            No compartas tu código de acceso. Si lo olvidaste o crees que alguien lo conoce, pide uno nuevo en tu posta mostrando tu DNI.
                        </p>
                    </section>
                </aside>
            </div>
        </div>
    </x-page-board>
@endsection
