@php
    $estados = [
        'Confirmada' => 'badge-teal',
        'Pendiente' => 'badge-amber',
        'Atendida' => 'badge-slate',
        'Cancelada' => 'badge-red',
    ];
    $activa = in_array($cita->estado, ['Confirmada', 'Pendiente'], true);
    $pasada = $cita->fecha_hora->isPast();
    $iniciales = mb_substr($cita->ciudadano->nombres, 0, 1).mb_substr($cita->ciudadano->apellidos, 0, 1);
@endphp

<li class="grid gap-3 border-b border-ink-200 py-4 last:border-b-0 sm:grid-cols-12 sm:items-start">
    <div class="sm:col-span-3">
        <p class="font-mono text-[10px] font-semibold uppercase tracking-[0.05em] text-brand-800">
            {{ $cita->fecha_hora->locale('es')->isoFormat('ddd D MMM YYYY') }}
        </p>
        <p class="mt-0.5 font-mono text-[10px] text-ink-600">{{ $cita->fecha_hora->format('H:i') }}</p>
    </div>

    <div class="flex min-w-0 items-start gap-3 sm:col-span-6">
        <span class="grid h-10 w-10 shrink-0 place-items-center border border-ink-300 bg-paper-100 font-mono text-[11px] font-semibold text-brand-800">
            {{ $iniciales }}
        </span>

        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h3 class="font-display text-sm font-bold text-ink-950">
                    {{ $cita->ciudadano->nombres }} {{ $cita->ciudadano->apellidos }}
                </h3>
                <span class="badge {{ $estados[$cita->estado] ?? 'badge-slate' }}">{{ $cita->estado }}</span>
            </div>

            <p class="mt-1 text-xs leading-relaxed text-ink-600">
                DNI {{ $cita->ciudadano->dni }}
                <span class="text-ink-300" aria-hidden="true">/</span>
                {{ $cita->ciudadano->telefono ?: 'Sin teléfono' }}
                <span class="text-ink-300" aria-hidden="true">/</span>
                {{ $cita->especialidad }}
            </p>

            <p class="mt-1 flex items-center gap-1.5 text-xs text-ink-500">
                <x-icon name="building-2" class="h-3.5 w-3.5 shrink-0 text-ink-400" />
                {{ $cita->posta->nombre ?? 'Establecimiento de la Microred' }}
            </p>
        </div>
    </div>

    @if ($mostrarAcciones && $activa)
        <div class="flex flex-wrap gap-2 sm:col-span-3 sm:justify-end">
            @if ($pasada)
                <form action="{{ route('empleado.citas.estado', $cita) }}" method="POST">
                    @csrf
                    <input type="hidden" name="estado" value="Atendida">
                    <button type="submit" class="btn btn-secondary btn-sm">
                        <x-icon name="check" class="h-4 w-4" />
                        Atendida
                    </button>
                </form>
            @endif

            <form action="{{ route('empleado.citas.estado', $cita) }}" method="POST" onsubmit="return confirm('¿Cancelar esta cita? El bloque volverá a quedar disponible.');">
                @csrf
                <input type="hidden" name="estado" value="Cancelada">
                <button type="submit" class="btn btn-danger btn-sm">
                    <x-icon name="x" class="h-4 w-4" />
                    Cancelar
                </button>
            </form>
        </div>
    @endif
</li>
