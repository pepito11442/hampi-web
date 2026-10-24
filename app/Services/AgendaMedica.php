<?php

namespace App\Services;

use App\Models\Horario;
use App\Models\TurnoMedico;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Construye la agenda médica de un día combinando:
 *
 * - Los horarios mensuales generados para médicos activos.
 * - El rol manual histórico (turno_medicos), que sigue funcionando para
 *   médicos visitantes o ajustes puntuales del día.
 *
 * Si un médico tiene horario y además un registro manual con el mismo
 * nombre, se muestra una sola vez y se usa el consultorio del registro
 * manual. El horario mensual manda para el turno y las horas.
 */
class AgendaMedica
{
    /**
     * @return Collection<int, MedicoDelDia>
     */
    public function delDia(?CarbonInterface $fecha = null): Collection
    {
        $fecha = $fecha ? Carbon::parse($fecha) : Carbon::today();

        $programados = Horario::query()
            ->with('user')
            ->whereDate('fecha', $fecha->toDateString())
            ->whereHas('user', fn ($query) => $query
                ->where('role', User::ROL_DOCTOR)
                ->where('activo', true))
            ->get();

        // Los registros manuales no tienen fecha: representan el rol de hoy.
        $manuales = $fecha->isToday()
            ? TurnoMedico::with('posta')->get()
            : collect();

        $manualesPorNombre = $manuales->keyBy(
            fn (TurnoMedico $turno) => $this->normalizarNombre($turno->medico_nombre)
        );

        $medicos = collect();
        $nombresProgramados = [];

        foreach ($programados as $horario) {
            $user = $horario->user;

            if (! $user) {
                continue;
            }

            $clave = $this->normalizarNombre($user->name);
            $nombresProgramados[$clave] = true;
            $manual = $manualesPorNombre->get($clave);

            $medicos->push(new MedicoDelDia(
                clave: 'horario-'.$horario->id,
                medico_nombre: $user->name,
                especialidad: $user->especialidad ?: 'Medicina General',
                turno: $this->etiquetaTurno($horario->turno),
                turno_clave: $horario->turno,
                hora_inicio: $horario->hora_inicio,
                hora_fin: $horario->hora_fin,
                consultorio: $manual?->consultorio ?: 'Consultorio de la Microred',
                estado: $this->estadoHorario($horario, $fecha),
                fuente: 'horario',
                en_manana: in_array($horario->turno, ['manana', 'completo', 'sabado'], true),
                en_tarde: in_array($horario->turno, ['tarde', 'completo'], true),
                user_id: $user->id,
                horario_id: $horario->id,
                foto_url: $user->foto_url,
            ));
        }

        foreach ($manuales as $manual) {
            $clave = $this->normalizarNombre($manual->medico_nombre);

            if (isset($nombresProgramados[$clave])) {
                continue;
            }

            $esManana = $manual->turno === 'Mañana';

            $medicos->push(new MedicoDelDia(
                clave: 'manual-'.$manual->id,
                medico_nombre: $manual->medico_nombre,
                especialidad: $manual->especialidad ?: 'Medicina General',
                turno: $manual->turno,
                turno_clave: $esManana ? 'manana' : 'tarde',
                hora_inicio: $esManana ? '07:00' : '13:00',
                hora_fin: $esManana ? '13:00' : '19:30',
                consultorio: $manual->consultorio,
                estado: $manual->estado,
                fuente: 'manual',
                en_manana: $esManana,
                en_tarde: ! $esManana,
                manual_id: $manual->id,
            ));
        }

        return $medicos->values();
    }

    /**
     * @param  Collection<int, MedicoDelDia>  $medicos
     * @return Collection<int, MedicoDelDia>
     */
    public function manana(Collection $medicos): Collection
    {
        return $medicos->where('en_manana', true)->values();
    }

    /**
     * @param  Collection<int, MedicoDelDia>  $medicos
     * @return Collection<int, MedicoDelDia>
     */
    public function tarde(Collection $medicos): Collection
    {
        return $medicos->where('en_tarde', true)->values();
    }

    /**
     * @param  Collection<int, MedicoDelDia>  $medicos
     */
    public function total(Collection $medicos): int
    {
        return $medicos->count();
    }

    /**
     * @param  Collection<int, MedicoDelDia>  $medicos
     */
    public function atendiendo(Collection $medicos): int
    {
        return $medicos->where('estado', 'Atendiendo')->count();
    }

    private function normalizarNombre(string $nombre): string
    {
        $nombre = preg_replace('/^(Dr\.?|Dra\.?|Lic\.?|Téc\.?|Tec\.?)\s+/iu', '', trim($nombre)) ?? $nombre;
        $nombre = Str::lower(Str::ascii($nombre));
        $nombre = preg_replace('/\s+/', ' ', $nombre) ?? $nombre;

        return trim($nombre);
    }

    private function etiquetaTurno(string $turno): string
    {
        return match ($turno) {
            'manana' => 'Mañana',
            'tarde' => 'Tarde',
            'completo' => 'Todo el día',
            'sabado' => 'Sábado',
            default => ucfirst($turno),
        };
    }

    private function estadoHorario(Horario $horario, CarbonInterface $fecha): string
    {
        $inicio = Carbon::parse($fecha->toDateString().' '.($horario->hora_inicio ?? '00:00'));
        $fin = Carbon::parse($fecha->toDateString().' '.($horario->hora_fin ?? '23:59'));
        $ahora = Carbon::now();

        if ($ahora->lessThan($inicio)) {
            return 'Programado';
        }

        if ($ahora->greaterThan($fin)) {
            return 'Finalizado';
        }

        return 'Atendiendo';
    }
}
