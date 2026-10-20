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
@endphp

<div>
    {{-- Calendario de escritorio --}}
    <div class="hidden sm:block">
        <div class="grid grid-cols-7 gap-1.5 text-center text-[10px] font-bold uppercase tracking-wider text-ink-400">
            @foreach (['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $dia)
                <div class="py-1">{{ $dia }}</div>
            @endforeach
        </div>

        <div class="mt-1.5 grid grid-cols-7 gap-1.5">
            @for ($i = 0; $i < $desplazamiento; $i++)
                <div class="min-h-[88px]" aria-hidden="true"></div>
            @endfor

            @for ($dia = 1; $dia <= $diasDelMes; $dia++)
                @php
                    $fecha = \Carbon\Carbon::create($anio, $mes, $dia);
                    $clave = $fecha->format('Y-m-d');
                    $horario = $horarios->get($clave);
                    $estilo = match ($horario?->turno) {
                        'manana' => 'border-amber-200 bg-amber-50 text-amber-900',
                        'tarde' => 'border-sky-200 bg-sky-50 text-sky-900',
                        'completo' => 'border-brand-200 bg-brand-50 text-brand-900',
                        'sabado' => 'border-violet-200 bg-violet-50 text-violet-900',
                        default => $fecha->isSunday()
                            ? 'border-ink-100/60 bg-ink-50/50 text-ink-400'
                            : 'border-ink-100 bg-white text-ink-600',
                    };
                @endphp

                <div class="min-h-[88px] rounded-xl border p-2 text-left transition-colors {{ $estilo }} {{ $clave === $hoy ? 'ring-2 ring-brand-300 ring-offset-1' : '' }}">
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-xs font-bold">{{ $dia }}</span>
                        @if ($clave === $hoy)
                            <span class="text-[9px] font-bold uppercase tracking-wide text-brand-700">Hoy</span>
                        @endif
                    </div>

                    @if ($horario)
                        <span class="mt-1 inline-flex rounded-full bg-white/75 px-1.5 py-0.5 text-[9px] font-bold leading-none">
                            {{ $horario->turno_label }}
                        </span>
                        <p class="mt-1 text-[10px] leading-tight opacity-80">
                            {{ $horario->hora_inicio }}–{{ $horario->hora_fin }}
                        </p>
                    @elseif ($fecha->isSunday())
                        <p class="mt-2 text-[10px] leading-tight">Sin atención</p>
                    @else
                        <p class="mt-2 text-[10px] leading-tight opacity-50">Libre</p>
                    @endif
                </div>
            @endfor
        </div>
    </div>

    {{-- Agenda para móvil --}}
    <div class="space-y-2 sm:hidden">
        @if ($horarios->isEmpty())
            <p class="rounded-2xl border border-dashed border-ink-200 bg-white/70 px-4 py-8 text-center text-xs text-ink-500">
                No hay turnos asignados para este mes.
            </p>
        @else
            @foreach ($horarios as $horario)
                @php
                    $estilo = match ($horario->turno) {
                        'manana' => 'border-amber-200 bg-amber-50 text-amber-900',
                        'tarde' => 'border-sky-200 bg-sky-50 text-sky-900',
                        'completo' => 'border-brand-200 bg-brand-50 text-brand-900',
                        'sabado' => 'border-violet-200 bg-violet-50 text-violet-900',
                        default => 'border-ink-100 bg-white text-ink-700',
                    };
                @endphp

                <div class="flex items-center justify-between gap-3 rounded-2xl border p-3 {{ $estilo }}">
                    <div class="flex items-center gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white/75 text-xs font-bold">
                            {{ $horario->fecha->format('d') }}
                        </span>
                        <div>
                            <p class="text-xs font-bold">
                                {{ $diasSemana[$horario->fecha->dayOfWeek] }} {{ $horario->fecha->format('d/m') }}
                            </p>
                            <p class="mt-0.5 text-[11px] opacity-80">
                                {{ $horario->hora_inicio }}–{{ $horario->hora_fin }} · {{ $horario->horas }} h
                            </p>
                        </div>
                    </div>
                    <span class="rounded-full bg-white/80 px-2 py-1 text-[10px] font-bold">{{ $horario->turno_label }}</span>
                </div>
            @endforeach

            <p class="pt-1 text-center text-[11px] text-ink-400">Domingos: sin atención.</p>
        @endif
    </div>

    {{-- Leyenda --}}
    <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-[10px] font-semibold text-ink-500">
        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>Mañana 07:00–13:00</span>
        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-sky-400"></span>Tarde 13:00–19:30</span>
        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-brand-500"></span>Todo el día 07:00–19:00</span>
        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-violet-400"></span>Sábado 07:00–15:00</span>
        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-ink-200"></span>Domingo sin atención</span>
    </div>
</div>
