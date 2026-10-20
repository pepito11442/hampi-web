<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\HorarioGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HorarioGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_genera_exactamente_150_horas_respetando_las_reglas(): void
    {
        $user = User::factory()->create(['role' => User::ROL_DOCTOR]);

        app(HorarioGenerator::class)->generar($user, 2026, 10);

        $horarios = $user->horarios()->get();

        $this->assertGreaterThan(0, $horarios->count());
        $this->assertSame(150.0, round((float) $horarios->sum('horas'), 1));
        $this->assertSame($horarios->count(), $horarios->pluck('fecha')->unique()->count());

        foreach ($horarios as $horario) {
            $this->assertNotSame(0, $horario->fecha->dayOfWeek, 'No debe haber turnos los domingos.');

            if ($horario->fecha->isSaturday()) {
                $this->assertSame('sabado', $horario->turno);
                $this->assertSame(8.0, $horario->horas);
            } else {
                $this->assertContains($horario->turno, ['manana', 'tarde', 'completo']);
            }
        }
    }

    public function test_genera_todos_los_meses_de_varios_anios(): void
    {
        $user = User::factory()->create(['role' => User::ROL_DOCTOR]);
        $generador = app(HorarioGenerator::class);

        foreach (range(2024, 2028) as $anio) {
            foreach (range(1, 12) as $mes) {
                $generador->generar($user, $anio, $mes);

                $total = $user->horarios()
                    ->whereYear('fecha', $anio)
                    ->whereMonth('fecha', $mes)
                    ->sum('horas');

                $this->assertSame(150.0, round((float) $total, 1), "El mes {$mes} de {$anio} no suma 150 horas.");
            }
        }
    }

    public function test_regenerar_reemplaza_el_horario_del_mismo_mes(): void
    {
        $user = User::factory()->create(['role' => User::ROL_ENFERMERO]);
        $generador = app(HorarioGenerator::class);

        $generador->generar($user, 2026, 10);
        $generador->generar($user, 2026, 10);

        $horarios = $user->horarios()->get();

        $this->assertSame(150.0, round((float) $horarios->sum('horas'), 1));
        $this->assertSame($horarios->count(), $horarios->pluck('fecha')->unique()->count());
    }
}
