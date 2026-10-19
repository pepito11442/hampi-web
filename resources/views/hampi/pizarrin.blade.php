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

    <x-page-hero
        eyebrow="Tablón comunitario"
        title="Pizarrín digital de vacunas"
        description="Información publicada por el personal de salud de la Microred Socabaya sobre vacunas, campañas y requisitos de atención."
        icon="syringe"
    >
        <x-slot:actions>
            @if ($ultimaActualizacion)
                <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white px-4 py-2 text-xs font-semibold text-ink-600">
                    <x-icon name="clock" class="h-4 w-4 text-brand-600" />
                    Actualizado {{ $ultimaActualizacion->locale('es')->diffForHumans() }}
                </span>
            @endif
            <a href="/directorio-postas" class="btn btn-secondary btn-sm">
                <x-icon name="map-pin" class="h-4 w-4 text-brand-600" />
                Ver postas
            </a>
        </x-slot:actions>
    </x-page-hero>

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
                        <x-icon name="stethoscope" class="h-4 w-4 text-brand-600" />
                        Ver médicos de hoy
                    </a>
                </x-slot:action>
            </x-empty-state>
        @else
            {{-- Filtros por disponibilidad --}}
            <div class="card flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between" data-reveal>
                <div class="flex flex-wrap items-center gap-2" role="group" aria-label="Filtrar vacunas por disponibilidad">
                    <button type="button" class="filter-pill" data-stock-filter="all" aria-pressed="true">
                        Todas
                        <span class="rounded-full bg-white/20 px-1.5 py-0.5 text-[10px]" data-vaccine-count>{{ $vacunas->count() }}</span>
                    </button>
                    <button type="button" class="filter-pill" data-stock-filter="Disponible" aria-pressed="false">Disponibles</button>
                    <button type="button" class="filter-pill" data-stock-filter="Por agotarse" aria-pressed="false">Por agotarse</button>
                    <button type="button" class="filter-pill" data-stock-filter="Agotado" aria-pressed="false">Agotadas</button>
                </div>
                <p class="flex items-center gap-2 text-xs text-ink-500">
                    <x-icon name="info" class="h-4 w-4 shrink-0 text-brand-600" />
                    Confirma la disponibilidad al acudir al establecimiento.
                </p>
            </div>

            <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($vacunas as $vacuna)
                    @php
                        [$stockClass, $stockIcon] = $stockStyles[$vacuna->stock_estado] ?? ['badge-slate', 'info'];
                    @endphp

                    <article
                        class="card card-hover flex flex-col p-5 {{ $vacuna->es_urgente ? 'ring-1 ring-amber-200' : '' }}"
                        data-vaccine-card
                        data-stock="{{ $vacuna->stock_estado }}"
                        data-reveal
                    >
                        <div class="flex items-start justify-between gap-3">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl {{ $vacuna->es_urgente ? 'bg-amber-50 text-amber-600' : 'bg-brand-50 text-brand-700' }}">
                                <x-icon name="syringe" class="h-5 w-5" />
                            </span>
                            <span class="badge {{ $stockClass }}">
                                <x-icon :name="$stockIcon" class="h-3.5 w-3.5" />
                                {{ $vacuna->stock_estado }}
                            </span>
                        </div>

                        @if ($vacuna->es_urgente)
                            <span class="mt-4 inline-flex w-fit items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-amber-800">
                                <x-icon name="sparkles" class="h-3 w-3" />
                                Campaña especial
                            </span>
                        @endif

                        <h2 class="mt-3 text-base font-bold leading-snug text-ink-900">{{ $vacuna->titulo }}</h2>

                        <div class="mt-4 space-y-3 rounded-2xl border border-ink-100 bg-ink-50/50 p-3.5">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-ink-400">Grupo objetivo</p>
                                <p class="mt-0.5 text-xs leading-relaxed text-ink-700">{{ $vacuna->grupo_objetivo }}</p>
                            </div>
                            <div class="border-t border-ink-100 pt-2.5">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-ink-400">Requisitos</p>
                                <p class="mt-0.5 text-xs leading-relaxed text-ink-700">{{ $vacuna->requisitos }}</p>
                            </div>
                        </div>

                        <div class="mt-auto flex items-center justify-between gap-3 border-t border-ink-100 pt-4">
                            <p class="flex items-center gap-1.5 text-[11px] text-ink-500">
                                <x-icon name="building-2" class="h-3.5 w-3.5 shrink-0 text-ink-400" />
                                {{ $vacuna->posta->nombre ?? 'Microred Socabaya' }}
                            </p>
                            <a href="/directorio-postas" class="inline-flex items-center gap-1 text-[11px] font-semibold text-brand-700 hover:text-brand-800">
                                Cómo llegar
                                <x-icon name="chevron-right" class="h-3.5 w-3.5" />
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
@endsection
