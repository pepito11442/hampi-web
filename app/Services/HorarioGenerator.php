<?php

namespace App\Services;

use App\Models\Horario;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Genera un horario mensual aleatorio que cumple exactamente 150 horas.
 *
 * Reglas:
 * - Lunes a viernes: mañana (6 h), tarde (6.5 h) o todo el día (12 h).
 * - Sábados: únicamente turno completo de 07:00 a 15:00 (8 h).
 * - Domingos: sin atención.
 */
class HorarioGenerator
{
    public const HORAS_MENSUALES = 150.0;

    /**
     * Genera y guarda el horario del mes indicado.
     *
     * @return float Total de horas asignadas (siempre 150).
     */
    public function generar(User $user, int $anio, int $mes): float
    {
        $inicioMes = Carbon::create($anio, $mes, 1)->startOfDay();
        $diasDelMes = $inicioMes->daysInMonth;

        $diasSemana = [];
        $sabados = [];

        for ($dia = 1; $dia <= $diasDelMes; $dia++) {
            $fecha = Carbon::create($anio, $mes, $dia);

            if ($fecha->isSunday()) {
                continue;
            }

            if ($fecha->isSaturday()) {
                $sabados[] = $fecha->toDateString();
            } else {
                $diasSemana[] = $fecha->toDateString();
            }
        }

        $asignaciones = $this->buscarCombinacion($diasSemana, $sabados);

        if ($asignaciones === null) {
            throw new RuntimeException('No se encontró una combinación de turnos que sume 150 horas para este mes.');
        }

        DB::transaction(function () use ($user, $inicioMes, $asignaciones) {
            Horario::where('user_id', $user->id)
                ->whereBetween('fecha', [
                    $inicioMes->toDateString(),
                    $inicioMes->copy()->endOfMonth()->toDateString(),
                ])
                ->delete();

            $ahora = now();
            $filas = [];

            foreach ($asignaciones as $fecha => $turno) {
                $definicion = Horario::TURNOS[$turno];

                $filas[] = [
                    'user_id' => $user->id,
                    'fecha' => $fecha,
                    'turno' => $turno,
                    'hora_inicio' => $definicion['inicio'].':00',
                    'hora_fin' => $definicion['fin'].':00',
                    'horas' => $definicion['horas'],
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];
            }

            Horario::insert($filas);
        });

        return self::HORAS_MENSUALES;
    }

    /**
     * Busca turnos para días de semana y sábados hasta sumar 150 horas.
     *
     * @param  array<int, string>  $diasSemana
     * @param  array<int, string>  $sabados
     * @return array<string, string>|null
     */
    private function buscarCombinacion(array $diasSemana, array $sabados): ?array
    {
        $totalDiasSemana = count($diasSemana);
        $totalSabados = count($sabados);

        // Varios intentos con distinta cantidad de días completos y sábados
        // para que el resultado no sea siempre el mismo.
        for ($intento = 0; $intento < 500; $intento++) {
            $diasCompletos = random_int(0, min(6, $totalDiasSemana));
            $sabadosAsignados = random_int(0, min(5, $totalSabados));

            $horasRestantes = self::HORAS_MENSUALES
                - (Horario::TURNOS['completo']['horas'] * $diasCompletos)
                - (Horario::TURNOS['sabado']['horas'] * $sabadosAsignados);

            $diasLibres = $totalDiasSemana - $diasCompletos;

            if ($horasRestantes < 0 || $diasLibres < 0) {
                continue;
            }

            // Resolver 6 * mañanas + 6.5 * tardes = horasRestantes.
            $opciones = [];
            $maxTardes = (int) floor($horasRestantes / Horario::TURNOS['tarde']['horas']);

            for ($tardes = 0; $tardes <= min($diasLibres, $maxTardes); $tardes++) {
                $resto = $horasRestantes - (Horario::TURNOS['tarde']['horas'] * $tardes);
                $mananas = $resto / Horario::TURNOS['manana']['horas'];

                if (abs($mananas - round($mananas)) > 0.001) {
                    continue;
                }

                $mananas = (int) round($mananas);

                if ($mananas >= 0 && ($mananas + $tardes) <= $diasLibres) {
                    $opciones[] = [$tardes, $mananas];
                }
            }

            if ($opciones === []) {
                continue;
            }

            [$tardes, $mananas] = $opciones[array_rand($opciones)];

            shuffle($diasSemana);
            shuffle($sabados);

            $asignaciones = [];
            $indice = 0;

            for ($i = 0; $i < $diasCompletos; $i++) {
                $asignaciones[$diasSemana[$indice++]] = 'completo';
            }

            for ($i = 0; $i < $mananas; $i++) {
                $asignaciones[$diasSemana[$indice++]] = 'manana';
            }

            for ($i = 0; $i < $tardes; $i++) {
                $asignaciones[$diasSemana[$indice++]] = 'tarde';
            }

            for ($i = 0; $i < $sabadosAsignados; $i++) {
                $asignaciones[$sabados[$i]] = 'sabado';
            }

            return $asignaciones;
        }

        return null;
    }
}
