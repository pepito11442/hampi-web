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

<li class="rounded-2xl border border-ink-100 bg-white p-4 sm:p-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex min-w-0 items-start gap-3">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-brand-50 text-sm font-bold text-brand-700">
                {{ $iniciales }}
            </span>

            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-sm font-bold text-ink-900">
                        {{ $cita->ciudadano->nombres }} {{ $cita->ciudadano->apellidos }}
                    </h3>
                    <span class="badge {{ $estados[$cita->estado] ?? 'badge-slate' }}">{{ $cita->estado }}</span>
                </div>

                <p class="mt-1 text-xs text-ink-500">
                    DNI {{ $cita->ciudadano->dni }}
                    · {{ $cita->ciudadano->telefono ?: 'Sin teléfono registrado' }}
                    · {{ $cita->especialidad }}
                </p>

                <p class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-ink-500">
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="calendar" class="h-3.5 w-3.5 text-brand-600" />
                        {{ $cita->fecha_hora->locale('es')->isoFormat('ddd D MMM YYYY') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="clock" class="h-3.5 w-3.5 text-brand-600" />
                        {{ $cita->fecha_hora->format('h:i a') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="building-2" class="h-3.5 w-3.5 text-brand-600" />
                        {{ $cita->posta->nombre ?? 'Establecimiento de la Microred' }}
                    </span>
                </p>
            </div>
        </div>

        @if ($mostrarAcciones && $activa)
            <div class="flex shrink-0 flex-wrap gap-2 sm:justify-end">
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
    </div>
</li>
