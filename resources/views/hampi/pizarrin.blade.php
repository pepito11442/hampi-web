@extends('hampi.layout')

@section('title', 'Pizarrín de vacunas · Hampi')
@section('meta_description', 'Consulta la disponibilidad de vacunas y campañas de la Microred Socabaya, con grupo objetivo y requisitos de atención.')

@section('content')
    @php
        $ultimaActualizacion = $vacunas->max('updated_at');
        $stockStyles = [
            'Disponible' => ['badge-teal', 'check-circle'],
            'Por agotarse' => ['badge-amber', 'alert-circle'],
            'Agotado' => ['badge-red', 'x'],
        ];
    @endphp
    <x-page-board>
        <x-slot:hero>
            <x-page-hero
                eyebrow="Tablón comunitario"
                title="Pizarrín digital de vacunas"
                description="Información publicada por el personal de salud de la Microred Socabaya sobre vacunas, campañas y requisitos de atención."
                icon="syringe"
            >
                <x-slot:actions>
                    @if ($ultimaActualizacion)
                        <span class="inline-flex min-h-10 items-center gap-2 border border-ink-300 bg-paper-50 px-3 font-mono text-[10px] uppercase tracking-[0.05em] text-ink-600">
                            <x-icon name="clock" class="h-4 w-4 text-brand-700" />
                            Actualizado {{ $ultimaActualizacion->locale('es')->diffForHumans() }}
                        </span>
                    @endif
                    <a href="/directorio-postas" class="btn btn-secondary btn-sm">
                        <x-icon name="map-pin" class="h-4 w-4" />
                        Ver postas
                    </a>
                </x-slot:actions>
            </x-page-hero>
        </x-slot:hero>

        <div class="container-page py-8 md:py-12">
            @if ($vacunas->isEmpty())
                <x-empty-state
                    icon="syringe"
                    title="No hay avisos publicados"
                    description="En este momento el pizarrín no tiene vacunas ni campañas informadas. Vuelve a consultar más tarde."
                    data-reveal
                >
                    <x-slot:action>
                        <a href="/medicos-hoy" class="btn btn-secondary btn-sm">
                            <x-icon name="stethoscope" class="h-4 w-4" />
                            Ver médicos de hoy
                        </a>
                    </x-slot:action>
                </x-empty-state>
            @else
                <div class="flex flex-col gap-4 border-y border-ink-300 py-3 lg:flex-row lg:items-center lg:justify-between" data-reveal>
                    <div class="flex flex-wrap items-center gap-2" role="group" aria-label="Filtrar vacunas por disponibilidad">
                        <button type="button" class="filter-pill" data-stock-filter="all" aria-pressed="true">
                            Todas
                            <span class="border border-current px-1.5 py-0.5 text-[9px]" data-vaccine-count>{{ $vacunas->count() }}</span>
                        </button>
                        <button type="button" class="filter-pill" data-stock-filter="Disponible" aria-pressed="false">Disponibles</button>
                        <button type="button" class="filter-pill" data-stock-filter="Por agotarse" aria-pressed="false">Por agotarse</button>
                        <button type="button" class="filter-pill" data-stock-filter="Agotado" aria-pressed="false">Agotadas</button>
                    </div>
                    <p class="flex items-center gap-2 text-xs text-ink-600">
                        <x-icon name="info" class="h-4 w-4 shrink-0 text-brand-700" />
                        Confirma la disponibilidad al acudir al establecimiento.
                    </p>
                </div>

                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    @foreach ($vacunas as $vacuna)
                        @php
                            [$stockClass, $stockIcon] = $stockStyles[$vacuna->stock_estado] ?? ['badge-slate', 'info'];
                        @endphp

                        <article
                            class="flex flex-col  bg-paper-50 p-5 {{ $vacuna->es_urgente ? 'border-maize-400 md:col-span-2' : 'border-ink-300' }}"
                            data-vaccine-card
                            data-stock="{{ $vacuna->stock_estado }}"
                            data-reveal
                        >
                            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-ink-200 pb-3">
                                <span class="mono-label text-ink-500">
                                    {{ $vacuna->es_urgente ? 'Aviso destacado' : 'Aviso de vacunación' }} / {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <span class="badge {{ $stockClass }}">
                                    <x-icon :name="$stockIcon" class="h-3.5 w-3.5" />
                                    {{ $vacuna->stock_estado }}
                                </span>
                            </div>

                            <div class="mt-4 flex items-start gap-3">
                                <span class="icon-seal {{ $vacuna->es_urgente ? 'border-maize-400 bg-maize-50 text-maize-800' : '' }}">
                                    <x-icon name="syringe" class="h-5 w-5" />
                                </span>
                                <div>
                                    @if ($vacuna->es_urgente)
                                        <span class="mono-label text-maize-800">Campaña especial</span>
                                    @endif
                                    <h2 class="mt-1 font-display text-xl font-bold leading-tight tracking-[-0.03em] text-ink-950">{{ $vacuna->titulo }}</h2>
                                </div>
                            </div>

                            <dl class="mt-5 flex-1 divide-y divide-ink-200 border-y border-ink-200">
                                <div class="py-3">
                                    <dt class="mono-label">Grupo objetivo</dt>
                                    <dd class="mt-1 text-sm leading-relaxed text-ink-700">{{ $vacuna->grupo_objetivo }}</dd>
                                </div>
                                <div class="py-3">
                                    <dt class="mono-label">Requisitos</dt>
                                    <dd class="mt-1 text-sm leading-relaxed text-ink-700">{{ $vacuna->requisitos }}</dd>
                                </div>
                            </dl>

                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                <p class="flex items-center gap-1.5 text-xs text-ink-600">
                                    <x-icon name="building-2" class="h-3.5 w-3.5 shrink-0 text-ink-400" />
                                    {{ $vacuna->posta->nombre ?? 'Microred Socabaya' }}
                                </p>
                                <a href="/directorio-postas" class="link-arrow">
                                    Cómo llegar
                                    <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div data-filter-empty hidden class="mt-6">
                    <x-empty-state
                        icon="search"
                        title="No hay vacunas con ese estado"
                        description="Prueba con otro filtro para ver el resto de avisos publicados en el pizarrín."
                    />
                </div>
            @endif
        </div>
    </x-page-board>
@endsection
