<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Horario;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Reglas de disponibilidad de la agenda médica.
 *
 * Calcula bloques de 30 minutos a partir del horario mensual del médico y
 * valida que una cita no se salga del turno ni quede en el pasado.
 */
class AgendaCitas
{
    /** Duración de cada bloque de atención. */
    public const DURACION_MINUTOS = 30;

    /** Estados que ocupan un bloque de la agenda. */
    public const ESTADOS_ACTIVOS = ['Confirmada', 'Pendiente'];

    /**
     * Devuelve el turno mensual asignado al médico para una fecha.
     */
    public function horarioDelDia(User $medico, CarbonInterface $fecha): ?Horario
    {
        return Horario::where('user_id', $medico->id)
            ->whereDate('fecha', $fecha->toDateString())
            ->first();
    }

    /**
     * Genera los bloques de 30 minutos disponibles dentro del turno del médico.
     *
     * @return array<int, array{hora: string, etiqueta: string}>
     */
    public function slots(User $medico, CarbonInterface $fecha): array
    {
        $horario = $this->horarioDelDia($medico, $fecha);

        if (! $horario || ! $horario->hora_inicio || ! $horario->hora_fin) {
            return [];
        }

        $inicioTurno = Carbon::parse($fecha->toDateString().' '.$horario->hora_inicio);
        $finTurno = Carbon::parse($fecha->toDateString().' '.$horario->hora_fin);

        $ocupadas = Cita::where('user_id', $medico->id)
            ->whereDate('fecha_hora', $fecha->toDateString())
            ->whereIn('estado', self::ESTADOS_ACTIVOS)
            ->get(['fecha_hora'])
            ->map(fn (Cita $cita) => $cita->fecha_hora->format('H:i'))
            ->all();

        $slots = [];
        $ahora = now();

        for ($inicio = $inicioTurno->copy(); $inicio->lt($finTurno); $inicio->addMinutes(self::DURACION_MINUTOS)) {
            if ($inicio->lte($ahora)) {
                continue;
            }

            $hora = $inicio->format('H:i');

            if (in_array($hora, $ocupadas, true)) {
                continue;
            }

            $slots[] = [
                'hora' => $hora,
                'etiqueta' => $inicio->format('H:i'),
            ];
        }

        return $slots;
    }

    /**
     * Comprueba que la hora pertenezca al turno, esté alineada a 30 minutos
     * y todavía no haya pasado.
     */
    public function slotValido(User $medico, CarbonInterface $fecha, string $hora): bool
    {
        if (! preg_match('/^\d{2}:\d{2}$/', $hora)) {
            return false;
        }

        [$horas, $minutos] = array_map('intval', explode(':', $hora));

        if ($horas > 23 || $minutos > 59 || $minutos % self::DURACION_MINUTOS !== 0) {
            return false;
        }

        $horario = $this->horarioDelDia($medico, $fecha);

        if (! $horario || ! $horario->hora_inicio || ! $horario->hora_fin) {
            return false;
        }

        $inicio = $this->fechaHora($fecha, $hora);
        $inicioTurno = Carbon::parse($fecha->toDateString().' '.$horario->hora_inicio);
        $finTurno = Carbon::parse($fecha->toDateString().' '.$horario->hora_fin);

        return $inicio->gte($inicioTurno)
            && $inicio->lt($finTurno)
            && $inicio->gt(now());
    }

    /**
     * Combina una fecha y una hora en la zona horaria de la aplicación.
     */
    public function fechaHora(CarbonInterface $fecha, string $hora): Carbon
    {
        return Carbon::parse($fecha->toDateString().' '.$hora);
    }
}
