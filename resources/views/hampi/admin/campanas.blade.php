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
            <a href="{{ route('campanas.index') }}" class="btn btn-secondary btn-sm">
                <x-icon name="external-link" class="h-4 w-4 text-brand-600" />
                Ver página pública
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">
                <x-icon name="arrow-left" class="h-4 w-4 text-brand-600" />
                Volver al panel
            </a>
        </x-slot:actions>
    </x-page-hero>

    @include('hampi.partials.admin-nav')

    <div class="container-page py-8 md:py-12">
        @if ($errors->any())
            <div role="alert" class="mb-6 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
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
            <section class="card p-6 lg:col-span-5 lg:sticky lg:top-24 lg:self-start" data-reveal>
                <div class="flex items-start gap-3">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-700">
                        <x-icon name="shield-plus" class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-ink-900">Publicar nueva campaña</h2>
                        <p class="mt-1 text-xs leading-relaxed text-ink-500">Completa la información que verá la comunidad en el portal.</p>
                    </div>
                </div>

                <form action="{{ route('admin.campanas.store') }}" method="POST" class="mt-6 space-y-4">
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

                    <label class="flex items-center gap-2.5 text-xs font-semibold text-ink-600">
                        <input type="checkbox" name="activa" value="1" class="h-4 w-4 rounded border-ink-300 text-brand-600 focus:ring-brand-500" @checked(old('activa', true))>
                        Publicar en el portal público
                    </label>

                    <button type="submit" class="btn btn-primary w-full">
                        <x-icon name="plus" class="h-4 w-4" />
                        Publicar campaña
                    </button>

                    <p class="text-[11px] leading-relaxed text-ink-400">
                        Si desmarcas “Publicar”, la campaña quedará guardada como inactiva y no se mostrará a los ciudadanos.
                    </p>
                </form>
            </section>

            {{-- Listado --}}
            <section class="lg:col-span-7">
                @if ($campanas->isEmpty())
                    <x-empty-state
                        icon="shield-check"
                        title="Aún no hay campañas preventivas"
                        description="Publica la primera campaña con el formulario de la izquierda. Aparecerá automáticamente en el portal público."
                    />
                @else
                    <div class="space-y-4">
                        @foreach ($campanas as $campana)
                            @php
                                $colorEstado = match ($campana->estado) {
                                    'En curso' => 'teal',
                                    'Próxima' => 'sky',
                                    default => 'slate',
                                };
                            @endphp

                            <article class="card p-5" data-reveal style="--reveal-delay: {{ $loop->index * 40 }}ms">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-bold leading-snug text-ink-900">{{ $campana->titulo }}</h3>
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
                                                class="inline-flex h-9 items-center gap-1.5 rounded-full px-3 text-[11px] font-bold transition-colors {{ $campana->activa ? 'text-ink-500 hover:bg-ink-100 hover:text-ink-700' : 'text-brand-700 hover:bg-brand-50 hover:text-brand-800' }}"
                                                title="{{ $campana->activa ? 'Ocultar del portal público' : 'Publicar en el portal público' }}"
                                            >
                                                <x-icon :name="$campana->activa ? 'lock' : 'check-circle'" class="h-4 w-4" />
                                                {{ $campana->activa ? 'Ocultar' : 'Publicar' }}
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.campanas.destroy', $campana) }}" method="POST" onsubmit="return confirm('¿Eliminar la campaña «{{ $campana->titulo }}»?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-rose-500 transition-colors hover:bg-rose-50 hover:text-rose-700" aria-label="Eliminar campaña">
                                                <x-icon name="trash-2" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <p class="mt-3 text-xs leading-relaxed text-ink-500">{{ $campana->descripcion }}</p>

                                <dl class="mt-4 grid gap-x-6 gap-y-2 border-t border-ink-100 pt-3 text-xs sm:grid-cols-2">
                                    <div class="flex items-start gap-2">
                                        <dt class="font-semibold text-ink-400">Dirigido a</dt>
                                        <dd class="text-ink-700">{{ $campana->grupo_objetivo ?: 'Público general' }}</dd>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <dt class="font-semibold text-ink-400">Lugar</dt>
                                        <dd class="text-ink-700">{{ $campana->lugar ?: 'Por confirmar' }}</dd>
                                    </div>
                                    <div class="flex items-start gap-2 sm:col-span-2">
                                        <dt class="font-semibold text-ink-400">Fechas</dt>
                                        <dd class="text-ink-700">{{ $campana->periodo }}</dd>
                                    </div>
                                </dl>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
