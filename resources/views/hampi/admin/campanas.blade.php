@extends('hampi.layout')

@section('title', 'Campañas preventivas · Administración Hampi')
@section('meta_description', 'Publica y administra las campañas preventivas de la Microred Socabaya.')

@section('content')
    <x-page-hero
        eyebrow="Administración"
        title="Campañas preventivas"
        description="Publica actividades de prevención y control para la comunidad. Las campañas activas aparecen en el portal público."
        icon="shield-check"
    >
        <x-slot:actions>
            <a href="{{ route('campanas.index') }}" class="btn btn-primary btn-sm">
                <x-icon name="external-link" class="h-4 w-4" />
                Ver página pública
            </a>
        </x-slot:actions>
    </x-page-hero>

    @include('hampi.partials.admin-nav')

    <div class="container-page py-6 md:py-9">
        @if ($errors->any())
            <div role="alert" class="mb-6 flex items-start gap-3 border border-stamp-300 bg-stamp-50 p-4 text-sm text-stamp-800">
                <x-icon name="alert-circle" class="mt-0.5 h-5 w-5 shrink-0" />
                <div>
                    <p class="font-bold">Revisa los datos de la campaña</p>
                    <ul class="mt-1 list-inside list-disc space-y-0.5 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Formulario --}}
            <section class="border border-ink-300 bg-paper-50 lg:col-span-5 xl:col-span-4" data-reveal>
                <div class="border-b-2 border-ink-950 p-4">
                    <p class="mono-label">Convocatoria</p>
                    <h2 class="mt-1 flex items-center gap-2 font-display text-lg font-bold text-ink-950">
                        <x-icon name="shield-plus" class="h-5 w-5 text-brand-700" />
                        Publicar nueva campaña
                    </h2>
                    <p class="mt-1 text-xs leading-relaxed text-ink-600">Completa la información que verá la comunidad en el portal.</p>
                </div>

                <form action="{{ route('admin.campanas.store') }}" method="POST" class="space-y-4 p-4">
                    @csrf

                    <div>
                        <label for="titulo" class="label">Título</label>
                        <input type="text" name="titulo" id="titulo" required value="{{ old('titulo') }}" placeholder="Ej. Control de presión arterial y diabetes" class="input">
                    </div>

                    <div>
                        <label for="grupo_objetivo" class="label">Dirigido a</label>
                        <input type="text" name="grupo_objetivo" id="grupo_objetivo" value="{{ old('grupo_objetivo') }}" placeholder="Ej. Adultos mayores de 40 años" class="input">
                    </div>

                    <div>
                        <label for="descripcion" class="label">Descripción</label>
                        <textarea name="descripcion" id="descripcion" rows="4" required class="input" placeholder="Describe la actividad, los servicios incluidos y las recomendaciones para participar.">{{ old('descripcion') }}</textarea>
                    </div>

                    <div>
                        <label for="lugar" class="label">Lugar</label>
                        <input type="text" name="lugar" id="lugar" value="{{ old('lugar', 'C.S. San Martín de Socabaya') }}" class="input">
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="fecha_inicio" class="label">Fecha de inicio</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ old('fecha_inicio') }}" class="input">
                        </div>
                        <div>
                            <label for="fecha_fin" class="label">Fecha de fin</label>
                            <input type="date" name="fecha_fin" id="fecha_fin" value="{{ old('fecha_fin') }}" class="input">
                        </div>
                    </div>

                    <label class="flex min-h-11 items-center gap-2.5 border border-ink-300 bg-paper-100 px-3 text-xs font-semibold text-ink-700">
                        <input type="checkbox" name="activa" value="1" class="h-4 w-4 border-ink-400 text-brand-700 focus:ring-brand-500" @checked(old('activa', true))>
                        Publicar en el portal público
                    </label>

                    <button type="submit" class="btn btn-primary w-full">
                        <x-icon name="plus" class="h-4 w-4" />
                        Publicar campaña
                    </button>

                    <p class="border-t border-ink-200 pt-3 font-mono text-[9px] uppercase leading-relaxed tracking-[0.05em] text-ink-500">
                        Si desmarcas “Publicar”, la campaña quedará guardada como inactiva y no se mostrará a los ciudadanos.
                    </p>
                </form>
            </section>

            {{-- Listado --}}
            <section class="lg:col-span-7 xl:col-span-8">
                <div class="flex flex-wrap items-end justify-between gap-3 border-b-2 border-ink-950 pb-2">
                    <div>
                        <p class="mono-label">Calendario interno</p>
                        <h2 class="mt-1 font-display text-xl font-bold text-ink-950">Campañas registradas</h2>
                    </div>
                    <span class="badge badge-slate">{{ $campanas->count() }} {{ $campanas->count() === 1 ? 'campaña' : 'campañas' }}</span>
                </div>

                @if ($campanas->isEmpty())
                    <x-empty-state
                        class="mt-5"
                        icon="shield-check"
                        title="Aún no hay campañas preventivas"
                        description="Publica la primera campaña con el formulario de la izquierda. Aparecerá automáticamente en el portal público."
                    />
                @else
                    <div class="divide-y divide-ink-200">
                        @foreach ($campanas as $campana)
                            @php
                                $colorEstado = match ($campana->estado) {
                                    'En curso' => 'teal',
                                    'Próxima' => 'sky',
                                    default => 'slate',
                                };
                            @endphp

                            <article class="grid gap-4 py-5 sm:grid-cols-12" data-reveal style="--reveal-delay: {{ $loop->index * 30 }}ms">
                                <div class="sm:col-span-3">
                                    <div class="flex h-20 w-20 flex-col items-center justify-center border border-ink-300 bg-paper-100">
                                        <span class="font-display text-3xl font-extrabold leading-none text-ink-950">
                                            {{ $campana->fecha_inicio?->format('d') ?? '—' }}
                                        </span>
                                        <span class="mt-1 font-mono text-[9px] uppercase tracking-[0.06em] text-ink-600">
                                            {{ $campana->fecha_inicio?->locale('es')->isoFormat('MMM YYYY') ?? 'Por confirmar' }}
                                        </span>
                                    </div>
                                    <p class="mt-2 font-mono text-[9px] uppercase leading-relaxed tracking-[0.05em] text-ink-500">{{ $campana->periodo }}</p>
                                </div>

                                <div class="sm:col-span-9">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h3 class="font-display text-lg font-bold leading-tight text-ink-950">{{ $campana->titulo }}</h3>
                                            <div class="mt-1.5 flex flex-wrap gap-1.5">
                                                <x-badge :color="$colorEstado">{{ $campana->estado }}</x-badge>
                                                <x-badge :color="$campana->activa ? 'sky' : 'slate'">{{ $campana->activa ? 'Publicada' : 'Inactiva' }}</x-badge>
                                            </div>
                                        </div>

                                        <div class="flex shrink-0 items-center gap-1">
                                            <form action="{{ route('admin.campanas.estado', $campana) }}" method="POST">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="inline-flex min-h-10 items-center gap-1.5 border border-ink-300 bg-paper-50 px-2.5 text-[11px] font-semibold text-ink-700 transition-colors hover:border-ink-500 hover:bg-paper-200"
                                                    title="{{ $campana->activa ? 'Ocultar del portal público' : 'Publicar en el portal público' }}"
                                                >
                                                    <x-icon :name="$campana->activa ? 'lock' : 'check-circle'" class="h-4 w-4" />
                                                    {{ $campana->activa ? 'Ocultar' : 'Publicar' }}
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.campanas.destroy', $campana) }}" method="POST" onsubmit="return confirm('¿Eliminar la campaña «{{ $campana->titulo }}»?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex h-10 w-10 items-center justify-center border border-ink-300 text-ink-500 transition-colors hover:border-stamp-300 hover:bg-stamp-50 hover:text-stamp-700" aria-label="Eliminar campaña {{ $campana->titulo }}">
                                                    <x-icon name="trash-2" class="h-4 w-4" />
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <p class="mt-3 text-xs leading-relaxed text-ink-600">{{ $campana->descripcion }}</p>

                                    <dl class="mt-3 grid gap-x-6 gap-y-2 border-t border-ink-200 pt-3 text-xs sm:grid-cols-2">
                                        <div class="flex items-start gap-2">
                                            <dt class="mono-label">Dirigido a</dt>
                                            <dd class="text-ink-700">{{ $campana->grupo_objetivo ?: 'Público general' }}</dd>
                                        </div>
                                        <div class="flex items-start gap-2">
                                            <dt class="mono-label">Lugar</dt>
                                            <dd class="text-ink-700">{{ $campana->lugar ?: 'Por confirmar' }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
