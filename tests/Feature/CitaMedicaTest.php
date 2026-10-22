<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Ciudadano;
use App\Models\Horario;
use App\Models\Posta;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitaMedicaTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_medico_puede_buscar_a_un_paciente_registrado_por_dni(): void
    {
        [$medico, $posta, $ciudadano] = $this->escenario();

        $this->actingAs($medico)
            ->get(route('empleado.citas.create', ['dni' => $ciudadano->dni]))
            ->assertOk()
            ->assertSee('Paciente registrado')
            ->assertSee($ciudadano->nombres)
            ->assertSee($ciudadano->apellidos);
    }

    public function test_el_medico_agenda_una_cita_dentro_de_su_turno(): void
    {
        [$medico, $posta, $ciudadano] = $this->escenario();

        $response = $this->actingAs($medico)->post(route('empleado.citas.store'), [
            'dni' => $ciudadano->dni,
            'posta_id' => $posta->id,
            'fecha' => Carbon::tomorrow()->toDateString(),
            'hora' => '08:00',
        ]);

        $response->assertRedirect(route('empleado.citas.index'));

        $this->assertDatabaseHas('citas', [
            'ciudadano_id' => $ciudadano->id,
            'user_id' => $medico->id,
            'posta_id' => $posta->id,
            'especialidad' => 'Medicina General',
            'estado' => 'Confirmada',
        ]);
    }

    public function test_el_medico_puede_registrar_a_un_paciente_nuevo_al_agendar(): void
    {
        [$medico, $posta] = $this->escenario();

        $response = $this->actingAs($medico)->post(route('empleado.citas.store'), [
            'dni' => '70000001',
            'nombres' => 'Lucía',
            'apellidos' => 'Torres Chávez',
            'telefono' => '987 000 111',
            'posta_id' => $posta->id,
            'fecha' => Carbon::tomorrow()->toDateString(),
            'hora' => '08:30',
        ]);

        $response->assertRedirect(route('empleado.citas.index'));

        $ciudadano = Ciudadano::where('dni', '70000001')->firstOrFail();

        $this->assertSame('Lucía', $ciudadano->nombres);
        $this->assertDatabaseHas('citas', [
            'ciudadano_id' => $ciudadano->id,
            'user_id' => $medico->id,
            'estado' => 'Confirmada',
        ]);
    }

    public function test_no_se_puede_agendar_fuera_del_turno_o_en_un_bloque_invalido(): void
    {
        [$medico, $posta, $ciudadano] = $this->escenario();

        $this->actingAs($medico)->post(route('empleado.citas.store'), [
            'dni' => $ciudadano->dni,
            'posta_id' => $posta->id,
            'fecha' => Carbon::tomorrow()->toDateString(),
            'hora' => '06:30',
        ])->assertSessionHasErrors('hora');

        $this->actingAs($medico)->post(route('empleado.citas.store'), [
            'dni' => $ciudadano->dni,
            'posta_id' => $posta->id,
            'fecha' => Carbon::tomorrow()->toDateString(),
            'hora' => '08:15',
        ])->assertSessionHasErrors('hora');

        $this->assertDatabaseCount('citas', 0);
    }

    public function test_la_disponibilidad_excluye_los_bloques_ya_ocupados(): void
    {
        [$medico, $posta, $ciudadano] = $this->escenario();

        Cita::create([
            'ciudadano_id' => $ciudadano->id,
            'user_id' => $medico->id,
            'posta_id' => $posta->id,
            'especialidad' => 'Medicina General',
            'fecha_hora' => Carbon::tomorrow()->setTime(8, 0),
            'estado' => 'Confirmada',
        ]);

        $horas = collect(
            $this->actingAs($medico)
                ->getJson(route('empleado.citas.disponibilidad', ['fecha' => Carbon::tomorrow()->toDateString()]))
                ->assertOk()
                ->json('slots')
        )->pluck('hora');

        $this->assertTrue($horas->contains('08:30'));
        $this->assertFalse($horas->contains('08:00'));
    }

    public function test_el_medico_puede_marcar_una_cita_como_atendida_y_no_reabrirla(): void
    {
        [$medico, $posta, $ciudadano] = $this->escenario();

        $cita = Cita::create([
            'ciudadano_id' => $ciudadano->id,
            'user_id' => $medico->id,
            'posta_id' => $posta->id,
            'especialidad' => 'Medicina General',
            'fecha_hora' => Carbon::yesterday()->setTime(9, 0),
            'estado' => 'Confirmada',
        ]);

        $this->actingAs($medico)
            ->post(route('empleado.citas.estado', $cita), ['estado' => 'Atendida'])
            ->assertRedirect(route('empleado.citas.index'));

        $this->assertSame('Atendida', $cita->fresh()->estado);

        $this->actingAs($medico)
            ->post(route('empleado.citas.estado', $cita), ['estado' => 'Cancelada'])
            ->assertSessionHasErrors('estado');

        $this->assertSame('Atendida', $cita->fresh()->estado);

        $futura = Cita::create([
            'ciudadano_id' => $ciudadano->id,
            'user_id' => $medico->id,
            'posta_id' => $posta->id,
            'especialidad' => 'Medicina General',
            'fecha_hora' => Carbon::tomorrow()->setTime(9, 0),
            'estado' => 'Confirmada',
        ]);

        $this->actingAs($medico)
            ->post(route('empleado.citas.estado', $futura), ['estado' => 'Atendida'])
            ->assertSessionHasErrors('estado');

        $this->assertSame('Confirmada', $futura->fresh()->estado);
    }

    public function test_un_medico_no_puede_cerrar_la_cita_de_otro(): void
    {
        [$medico, $posta, $ciudadano] = $this->escenario();
        $otroMedico = User::factory()->doctor()->create();

        $cita = Cita::create([
            'ciudadano_id' => $ciudadano->id,
            'user_id' => $otroMedico->id,
            'posta_id' => $posta->id,
            'especialidad' => 'Medicina General',
            'fecha_hora' => Carbon::yesterday()->setTime(9, 0),
            'estado' => 'Confirmada',
        ]);

        $this->actingAs($medico)
            ->post(route('empleado.citas.estado', $cita), ['estado' => 'Cancelada'])
            ->assertNotFound();

        $this->assertSame('Confirmada', $cita->fresh()->estado);
    }

    public function test_el_personal_de_enfermeria_no_puede_acceder_a_la_agenda_medica(): void
    {
        $enfermero = User::factory()->enfermero()->create();

        $this->actingAs($enfermero)
            ->get(route('empleado.citas.index'))
            ->assertRedirect(route('empleado.panel'));
    }

    public function test_el_paciente_ve_al_medico_en_su_panel(): void
    {
        [$medico, $posta, $ciudadano] = $this->escenario();

        Cita::create([
            'ciudadano_id' => $ciudadano->id,
            'user_id' => $medico->id,
            'posta_id' => $posta->id,
            'especialidad' => 'Medicina General',
            'fecha_hora' => Carbon::tomorrow()->setTime(10, 0),
            'estado' => 'Confirmada',
        ]);

        $this->post('/ingreso-dni', ['dni' => $ciudadano->dni])
            ->assertRedirect(route('ciudadano.panel'));

        $this->get(route('ciudadano.panel'))
            ->assertOk()
            ->assertSee($ciudadano->nombres)
            ->assertSee($medico->name)
            ->assertSee('Medicina General');

        $this->assertSame('Rosa Elena', $ciudadano->fresh()->nombres);
    }

    public function test_el_panel_ciudadano_sin_sesion_redirige_al_acceso(): void
    {
        $this->get('/mi-panel')->assertRedirect('/#acceso');
    }

    public function test_no_se_puede_regenerar_un_horario_con_citas_futuras(): void
    {
        [$medico, $posta, $ciudadano] = $this->escenario();
        $admin = User::factory()->admin()->create();
        $fecha = Carbon::tomorrow();

        Cita::create([
            'ciudadano_id' => $ciudadano->id,
            'user_id' => $medico->id,
            'posta_id' => $posta->id,
            'especialidad' => 'Medicina General',
            'fecha_hora' => $fecha->copy()->setTime(9, 0),
            'estado' => 'Confirmada',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.horarios.generar'), [
                'user_id' => $medico->id,
                'anio' => $fecha->year,
                'mes' => $fecha->month,
            ])
            ->assertSessionHasErrors('horario');
    }

    /**
     * Crea un médico con turno de mañana, una posta y un paciente de prueba.
     *
     * @return array{0: User, 1: Posta, 2: Ciudadano}
     */
    private function escenario(): array
    {
        $medico = User::factory()->doctor()->create([
            'name' => 'Dra. Agenda de Prueba',
            'especialidad' => 'Medicina General',
        ]);

        $posta = Posta::create([
            'nombre' => 'C.S. de Prueba',
            'direccion' => 'Av. Prueba 123',
            'estado_activo' => true,
        ]);

        $ciudadano = Ciudadano::create([
            'dni' => '72345678',
            'nombres' => 'Rosa Elena',
            'apellidos' => 'Quispe Mamani',
            'telefono' => '987 654 321',
        ]);

        Horario::create([
            'user_id' => $medico->id,
            'fecha' => Carbon::tomorrow()->toDateString(),
            'turno' => 'manana',
            'hora_inicio' => '07:00:00',
            'hora_fin' => '13:00:00',
            'horas' => 6,
        ]);

        return [$medico, $posta, $ciudadano];
    }
}
