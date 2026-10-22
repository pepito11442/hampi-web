@php
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];
    $diasSemana = [
        0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
        4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado',
    ];
    $inicioMes = \Carbon\Carbon::create($anio, $mes, 1);
    $diasDelMes = $inicioMes->daysInMonth;
    $desplazamiento = ($inicioMes->dayOfWeek + 6) % 7; // lunes = 0
    $hoy = now()->toDateString();
    $estilos = [
        'manana' => 'border-l-2 border-l-maize-500 bg-maize-50/60',
        'tarde' => 'border-l-2 border-l-cobalt-500 bg-cobalt-50/60',
        'completo' => 'border-l-2 border-l-brand-600 bg-brand-50/60',
        'sabado' => 'border-l-2 border-l-signal-500 bg-signal-50/60',
    ];
@endphp

<div>
    {{-- Calendario de escritorio --}}
    <div class="hidden sm:block">
        <div class="grid grid-cols-7 border-l border-t border-ink-300 bg-ink-50">
            @foreach (['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $dia)
                <div class="border-b border-r border-ink-300 px-2 py-2 font-mono text-[10px] font-semibold uppercase tracking-[0.08em] text-ink-600">
                    {{ $dia }}
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-7 border-l border-ink-300">
            @for ($i = 0; $i < $desplazamiento; $i++)
                <div class="min-h-[100px] border-b border-r border-ink-200 bg-ink-50/50" aria-hidden="true"></div>
            @endfor

            @for ($dia = 1; $dia <= $diasDelMes; $dia++)
                @php
                    $fecha = \Carbon\Carbon::create($anio, $mes, $dia);
                    $clave = $fecha->format('Y-m-d');
                    $horario = $horarios->get($clave);
                    $estilo = $horario ? ($estilos[$horario->turno] ?? 'border-l-2 border-l-ink-300') : '';
                @endphp

                <div
                    class="relative min-h-[100px] border-b border-r border-ink-200 p-2.5 text-left {{ $fecha->isSunday() && ! $horario ? 'bg-ink-50/60 text-ink-500' : 'bg-paper-50 text-ink-700' }} {{ $estilo }} {{ $clave === $hoy ? 'outline outline-2 -outline-offset-2 outline-ink-950' : '' }}"
                    @if ($clave === $hoy) aria-current="date" @endif
                >
                    <div class="flex items-center justify-between gap-1">
                        <span class="font-mono text-[12px] font-semibold tabular">{{ $dia }}</span>
                        @if ($clave === $hoy)
                            <span class="border border-ink-950 bg-ink-950 px-1 py-0.5 font-mono text-[9px] font-semibold uppercase tracking-[0.06em] text-paper-50">Hoy</span>
                        @endif
                    </div>

                    @if ($horario)
                        <span class="mt-2 inline-flex items-center gap-1 border border-ink-300/70 bg-paper-50/80 px-1.5 py-0.5 font-mono text-[9px] font-semibold uppercase leading-none tracking-[0.04em] text-ink-700">
                            {{ $horario->turno_label }}
                        </span>
                        <p class="mt-1 font-mono text-[10px] leading-tight text-ink-700">
                            {{ $horario->hora_inicio }}–{{ $horario->hora_fin }}
                        </p>
                        <p class="font-mono text-[10px] leading-tight text-ink-600">{{ $horario->horas }} h</p>
                    @elseif ($fecha->isSunday())
                        <p class="mt-2 font-mono text-[10px] uppercase leading-tight tracking-[0.04em]">Sin atención</p>
                    @else
                        <p class="mt-2 font-mono text-[10px] uppercase leading-tight tracking-[0.04em] text-ink-500">Libre</p>
                    @endif
                </div>
            @endfor
        </div>
    </div>

    {{-- Agenda para móvil --}}
    <div class="sm:hidden">
        @if ($horarios->isEmpty())
            <p class="border border-dashed border-ink-300 bg-paper-50 px-4 py-8 text-center text-xs text-ink-600">
                No hay turnos asignados para este mes.
            </p>
        @else
            <div class="border-t border-ink-300">
                @foreach ($horarios as $horario)
                    @php
                        $estilo = $estilos[$horario->turno] ?? 'border-l-2 border-l-ink-300';
                    @endphp

                    <div class="flex items-center justify-between gap-3 border-b border-r border-ink-200 bg-paper-50 p-3 {{ $estilo }}">
                        <div class="flex items-center gap-3">
                            <span class="grid h-11 w-11 shrink-0 place-items-center border border-ink-300 bg-paper-100 font-mono text-[13px] font-semibold text-ink-800">
                                {{ $horario->fecha->format('d') }}
                            </span>
                            <div>
                                <p class="font-display text-sm font-bold text-ink-950">
                                    {{ $diasSemana[$horario->fecha->dayOfWeek] }} {{ $horario->fecha->format('d/m') }}
                                </p>
                                <p class="mt-0.5 font-mono text-[10px] text-ink-600">
                                    {{ $horario->hora_inicio }}–{{ $horario->hora_fin }} · {{ $horario->horas }} h
                                </p>
                            </div>
                        </div>
                        <span class="border border-ink-300 bg-paper-50 px-1.5 py-1 font-mono text-[9px] font-semibold uppercase leading-none tracking-[0.04em] text-ink-700">
                            {{ $horario->turno_label }}
                        </span>
                    </div>
                @endforeach
            </div>

            <p class="pt-2 text-center font-mono text-[10px] uppercase tracking-[0.05em] text-ink-500">Domingos: sin atención.</p>
        @endif
    </div>

    {{-- Leyenda --}}
    <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 font-mono text-[10px] uppercase tracking-[0.05em] text-ink-600">
        <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 border border-maize-500 bg-maize-400"></span>Mañana 07:00–13:00</span>
        <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 border border-cobalt-500 bg-cobalt-400"></span>Tarde 13:00–19:30</span>
        <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 border border-brand-600 bg-brand-500"></span>Todo el día 07:00–19:00</span>
        <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 border border-signal-500 bg-signal-400"></span>Sábado 07:00–15:00</span>
        <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 border border-ink-300 bg-ink-100"></span>Domingo sin atención</span>
    </div>
</div>
