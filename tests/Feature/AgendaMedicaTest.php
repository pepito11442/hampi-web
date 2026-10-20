<?php

namespace Tests\Feature;

use App\Models\Horario;
use App\Models\Posta;
use App\Models\TurnoMedico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaMedicaTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_agenda_publica_muestra_los_horarios_generados_de_hoy(): void
    {
        $doctor = User::factory()->doctor()->create([
            'name' => 'Dr. Agenda Programada',
            'especialidad' => 'Cardiología',
        ]);
        $enfermero = User::factory()->enfermero()->create([
            'name' => 'Enfermero Agenda',
        ]);

        $this->crearHorario($doctor, 'manana');
        $this->crearHorario($enfermero, 'manana');

        $this->get('/medicos-hoy')
            ->assertOk()
            ->assertSee('Dr. Agenda Programada')
            ->assertSee('Cardiología')
            ->assertSee('07:00')
            ->assertDontSee('Enfermero Agenda');
    }

    public function test_el_panel_administrativo_muestra_el_horario_generado_de_hoy(): void
    {
        $admin = User::factory()->admin()->create();
        $doctor = User::factory()->doctor()->create([
            'name' => 'Dr. Agenda Administrativa',
        ]);

        $this->crearHorario($doctor, 'tarde');

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Dr. Agenda Administrativa')
            ->assertSee('Ver horario');
    }

    public function test_un_medico_de_todo_el_dia_aparece_en_ambos_turnos(): void
    {
        $doctor = User::factory()->doctor()->create([
            'name' => 'Dr. Agenda Completa',
        ]);

        Horario::create([
            'user_id' => $doctor->id,
            'fecha' => today()->toDateString(),
            'turno' => 'completo',
            'hora_inicio' => '07:00:00',
            'hora_fin' => '19:00:00',
            'horas' => 12,
        ]);

        $response = $this->get('/medicos-hoy');

        $response->assertOk()->assertSee('Dr. Agenda Completa');
        $this->assertSame(2, substr_count($response->getContent(), 'Dr. Agenda Completa'));
    }

    public function test_la_agenda_conserva_el_rol_manual_y_no_duplica_al_medico_con_horario(): void
    {
        $posta = Posta::create([
            'nombre' => 'C.S. de Prueba',
            'direccion' => 'Av. Prueba 123',
        ]);

        $doctor = User::factory()->doctor()->create([
            'name' => 'Dr. Agenda Duplicada',
        ]);
        $this->crearHorario($doctor, 'manana');

        TurnoMedico::create([
            'posta_id' => $posta->id,
            'medico_nombre' => 'Dr. Agenda Duplicada',
            'especialidad' => 'Medicina General',
            'turno' => 'Mañana',
            'consultorio' => 'Consultorio 9',
            'estado' => 'Atendiendo',
        ]);

        TurnoMedico::create([
            'posta_id' => $posta->id,
            'medico_nombre' => 'Dr. Visitante Manual',
            'especialidad' => 'Medicina General',
            'turno' => 'Mañana',
            'consultorio' => 'Consultorio 10',
            'estado' => 'Atendiendo',
        ]);

        $response = $this->get('/medicos-hoy');

        $response->assertOk()
            ->assertSee('Dr. Agenda Duplicada')
            ->assertSee('Dr. Visitante Manual')
            ->assertSee('Consultorio 9')
            ->assertSee('Consultorio 10');

        $this->assertSame(1, substr_count($response->getContent(), 'Dr. Agenda Duplicada'));
    }

    public function test_un_medico_inactivo_no_aparece_en_la_agenda_publica(): void
    {
        $doctor = User::factory()->doctor()->inactivo()->create([
            'name' => 'Dr. Agenda Inactiva',
        ]);

        $this->crearHorario($doctor, 'manana');

        $this->get('/medicos-hoy')
            ->assertOk()
            ->assertDontSee('Dr. Agenda Inactiva');
    }

    public function test_el_panel_administrativo_conserva_el_crud_manual(): void
    {
        Posta::create([
            'nombre' => 'C.S. de Prueba',
            'direccion' => 'Av. Prueba 123',
        ]);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/turnos/guardar', [
                'medico_nombre' => 'Dr. Manual CRUD',
                'especialidad' => 'Medicina General',
                'turno' => 'Mañana',
                'consultorio' => 'Consultorio 7',
            ])
            ->assertRedirect('/admin/dashboard');

        $turno = TurnoMedico::where('medico_nombre', 'Dr. Manual CRUD')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Dr. Manual CRUD')
            ->assertSee('Manual')
            ->assertSee('Consultorio 7');

        $this->actingAs($admin)
            ->post("/admin/turnos/{$turno->id}/estado", ['estado' => 'Reprogramado'])
            ->assertRedirect('/admin/dashboard');

        $this->assertDatabaseHas('turno_medicos', [
            'id' => $turno->id,
            'estado' => 'Reprogramado',
        ]);

        $this->actingAs($admin)
            ->delete("/admin/turnos/{$turno->id}")
            ->assertRedirect('/admin/dashboard');

        $this->assertDatabaseMissing('turno_medicos', ['id' => $turno->id]);
    }

    public function test_un_turno_invalido_es_rechazado_por_el_form_request(): void
    {
        Posta::create([
            'nombre' => 'C.S. de Prueba',
            'direccion' => 'Av. Prueba 123',
        ]);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/turnos/guardar', [
                'medico_nombre' => 'Dr. Turno Inválido',
                'especialidad' => 'Medicina General',
                'turno' => 'Noche',
                'consultorio' => 'Consultorio 1',
            ])
            ->assertSessionHasErrors('turno');

        $this->assertDatabaseCount('turno_medicos', 0);
    }

    private function crearHorario(User $user, string $turno): Horario
    {
        $esTarde = $turno === 'tarde';

        return Horario::create([
            'user_id' => $user->id,
            'fecha' => today()->toDateString(),
            'turno' => $turno,
            'hora_inicio' => $esTarde ? '13:00:00' : '07:00:00',
            'hora_fin' => $esTarde ? '19:30:00' : '13:00:00',
            'horas' => $esTarde ? 6.5 : 6,
        ]);
    }
}
