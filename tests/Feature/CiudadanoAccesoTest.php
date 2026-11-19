<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Ciudadano;
use App\Models\Horario;
use App\Models\Posta;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Acceso ciudadano con DNI + código y autoservicio de citas.
 */
class CiudadanoAccesoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_dni_solo_ya_no_abre_el_panel(): void
    {
        [, , $ciudadano] = $this->escenario();
        $ciudadano->asignarCodigo('482913');

        $this->post('/ingreso-dni', ['dni' => $ciudadano->dni])
            ->assertSessionHasErrors('codigo');

        $this->post('/ingreso-dni', ['dni' => $ciudadano->dni, 'codigo' => '000001'])
            ->assertSessionHasErrors('codigo');

        $this->get('/mi-panel')->assertRedirect(route('inicio').'#acceso');
    }

    public function test_una_ficha_creada_por_el_personal_no_entra_sin_codigo(): void
    {
        [, , $ciudadano] = $this->escenario();

        $this->post('/ingreso-dni', ['dni' => $ciudadano->dni, 'codigo' => '482913'])
            ->assertSessionHasErrors('codigo');

        $this->assertNull(session('ciudadano_id'));
    }

    public function test_dni_y_codigo_correctos_abren_el_panel(): void
    {
        [, , $ciudadano] = $this->escenario();
        $ciudadano->asignarCodigo('482913');

        $this->post('/ingreso-dni', ['dni' => $ciudadano->dni, 'codigo' => '482913'])
            ->assertRedirect(route('ciudadano.panel'));

        $this->get('/mi-panel')->assertOk()->assertSee('Rosa Elena');
    }

    public function test_se_bloquea_tras_cinco_codigos_incorrectos(): void
    {
        [, , $ciudadano] = $this->escenario();
        $ciudadano->asignarCodigo('482913');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/ingreso-dni', ['dni' => $ciudadano->dni, 'codigo' => '11111'.$i]);
        }

        // Incluso con el código correcto, durante el bloqueo no entra.
        $this->post('/ingreso-dni', ['dni' => $ciudadano->dni, 'codigo' => '482913'])
            ->assertSessionHasErrors('codigo');

        $this->assertStringContainsString('Demasiados intentos', session('errors')->first('codigo'));
    }

    public function test_el_codigo_se_guarda_con_hash(): void
    {
        [, , $ciudadano] = $this->escenario();
        $ciudadano->asignarCodigo('482913');

        $this->assertNotSame('482913', $ciudadano->fresh()->codigo_acceso);
        $this->assertArrayNotHasKey('codigo_acceso', $ciudadano->fresh()->toArray());
    }

    public function test_ya_no_se_puede_crear_el_codigo_desde_internet(): void
    {
        $this->get('/crear-codigo')->assertNotFound();
        $this->post('/crear-codigo', ['dni' => '70111222'])->assertNotFound();
        $this->assertDatabaseMissing('ciudadanos', ['dni' => '70111222']);
    }

    public function test_el_medico_entrega_un_codigo_al_paciente_nuevo(): void
    {
        [$medico, $posta] = $this->escenario();

        $this->actingAs($medico)->post(route('empleado.citas.store'), [
            'dni' => '70000001',
            'nombres' => 'Lucía',
            'apellidos' => 'Torres',
            'posta_id' => $posta->id,
            'fecha' => Carbon::tomorrow()->toDateString(),
            'hora' => '08:00',
        ])->assertSessionHas('codigo_ciudadano');

        $codigo = session('codigo_ciudadano')['codigo'];
        $this->assertMatchesRegularExpression('/^\d{6}$/', $codigo);
        $this->assertTrue(Ciudadano::where('dni', '70000001')->first()->verificarCodigo($codigo));
    }

    public function test_el_medico_puede_regenerar_el_codigo_de_su_paciente(): void
    {
        [$medico, $posta, $ciudadano] = $this->escenario();
        $ciudadano->asignarCodigo('482913');
        $cita = $this->cita($ciudadano, $medico, $posta);

        $this->actingAs($medico)
            ->post(route('empleado.citas.codigo', $cita))
            ->assertSessionHas('codigo_ciudadano');

        $this->assertFalse($ciudadano->fresh()->verificarCodigo('482913'));
    }

    public function test_otro_medico_no_puede_regenerar_codigos_ajenos(): void
    {
        [$medico, $posta, $ciudadano] = $this->escenario();
        $cita = $this->cita($ciudadano, $medico, $posta);
        $otro = User::factory()->doctor()->create();

        $this->actingAs($otro)->post(route('empleado.citas.codigo', $cita))->assertNotFound();
    }

    public function test_admision_genera_codigo_en_ventanilla(): void
    {
        [, , $ciudadano] = $this->escenario();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.ciudadanos.codigo'), ['dni' => $ciudadano->dni])
            ->assertSessionHas('codigo_ciudadano');

        $this->assertTrue($ciudadano->fresh()->tieneCodigo());
    }

    public function test_ventanilla_registra_a_una_persona_nueva_con_su_nombre(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.ciudadanos.codigo'), ['dni' => '70111222'])
            ->assertSessionHasErrors('dni_codigo');
        $this->assertDatabaseMissing('ciudadanos', ['dni' => '70111222']);

        $this->actingAs($admin)
            ->post(route('admin.ciudadanos.codigo'), [
                'dni' => '70111222',
                'nombres' => 'Juana',
                'apellidos' => 'Ccama Luque',
            ])->assertSessionHas('codigo_ciudadano');

        $ciudadano = Ciudadano::where('dni', '70111222')->firstOrFail();
        $this->assertSame('Juana', $ciudadano->nombres);
        $this->assertTrue($ciudadano->tieneCodigo());
    }

    public function test_ventanilla_autocompleta_con_reniec_solo_para_el_personal(): void
    {
        config(['services.reniec.token' => 'token-de-prueba']);
        \Illuminate\Support\Facades\Http::fake(['*' => \Illuminate\Support\Facades\Http::response([
            'nombres' => 'JUANA', 'apellidoPaterno' => 'CCAMA', 'apellidoMaterno' => 'LUQUE',
        ])]);

        $this->getJson('/admin/ciudadanos/reniec/70111222')->assertUnauthorized();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->getJson('/admin/ciudadanos/reniec/70111222')
            ->assertOk()
            ->assertJson(['success' => true, 'existe' => false, 'nombres' => 'JUANA', 'apellidos' => 'CCAMA LUQUE']);

        [, , $ciudadano] = $this->escenario();
        $this->actingAs($admin)->getJson('/admin/ciudadanos/reniec/'.$ciudadano->dni)
            ->assertOk()
            ->assertJson(['existe' => true, 'nombres' => $ciudadano->nombres]);

        \Illuminate\Support\Facades\Http::assertSentCount(1);
    }

    public function test_el_ciudadano_reserva_un_horario_libre(): void
    {
        [$medico, , $ciudadano] = $this->escenario();
        $this->entrar($ciudadano);

        $this->get(route('ciudadano.citas.create'))->assertOk()->assertSee($medico->name);

        $this->getJson(route('ciudadano.citas.disponibilidad', ['medico' => $medico->id, 'fecha' => Carbon::tomorrow()->toDateString()]))
            ->assertOk()
            ->assertJsonFragment(['hora' => '08:00']);

        $this->post(route('ciudadano.citas.store'), [
            'medico' => $medico->id,
            'fecha' => Carbon::tomorrow()->toDateString(),
            'hora' => '08:00',
        ])->assertRedirect(route('ciudadano.panel'));

        $this->assertDatabaseHas('citas', [
            'ciudadano_id' => $ciudadano->id,
            'user_id' => $medico->id,
            'estado' => 'Pendiente',
            'origen' => 'ciudadano',
        ]);

        // El bloque ya no se ofrece a nadie más.
        $this->getJson(route('ciudadano.citas.disponibilidad', ['medico' => $medico->id, 'fecha' => Carbon::tomorrow()->toDateString()]))
            ->assertJsonMissing(['hora' => '08:00']);
    }

    public function test_no_se_reserva_fuera_del_turno_ni_un_bloque_ocupado(): void
    {
        [$medico, $posta, $ciudadano] = $this->escenario();
        $this->cita(Ciudadano::create(['dni' => '70000002', 'nombres' => 'A', 'apellidos' => 'B']), $medico, $posta, '09:00');
        $this->entrar($ciudadano);

        $this->post(route('ciudadano.citas.store'), [
            'medico' => $medico->id,
            'fecha' => Carbon::tomorrow()->toDateString(),
            'hora' => '15:00',
        ])->assertSessionHasErrors('hora');

        $this->post(route('ciudadano.citas.store'), [
            'medico' => $medico->id,
            'fecha' => Carbon::tomorrow()->toDateString(),
            'hora' => '09:00',
        ])->assertSessionHasErrors('hora');
    }

    public function test_maximo_tres_citas_activas(): void
    {
        [$medico, , $ciudadano] = $this->escenario();
        $this->entrar($ciudadano);

        foreach (['08:00', '09:00', '10:00'] as $indice => $hora) {
            // Una cita por día con el mismo médico: se crean días extra.
            $fecha = Carbon::tomorrow()->addDays($indice);
            if (! Horario::where('user_id', $medico->id)->whereDate('fecha', $fecha->toDateString())->exists()) {
                Horario::create(['user_id' => $medico->id, 'fecha' => $fecha->toDateString(), 'turno' => 'manana', 'hora_inicio' => '07:00:00', 'hora_fin' => '13:00:00', 'horas' => 6]);
            }

            $this->post(route('ciudadano.citas.store'), [
                'medico' => $medico->id,
                'fecha' => $fecha->toDateString(),
                'hora' => $hora,
            ])->assertSessionHasNoErrors();
        }

        $fecha = Carbon::tomorrow()->addDays(3);
        Horario::create(['user_id' => $medico->id, 'fecha' => $fecha->toDateString(), 'turno' => 'manana', 'hora_inicio' => '07:00:00', 'hora_fin' => '13:00:00', 'horas' => 6]);

        $this->post(route('ciudadano.citas.store'), [
            'medico' => $medico->id,
            'fecha' => $fecha->toDateString(),
            'hora' => '08:00',
        ])->assertSessionHasErrors('hora');
    }

    public function test_el_ciudadano_cancela_solo_sus_citas(): void
    {
        [$medico, $posta, $ciudadano] = $this->escenario();
        $propia = $this->cita($ciudadano, $medico, $posta);
        $ajena = $this->cita(Ciudadano::create(['dni' => '70000003', 'nombres' => 'C', 'apellidos' => 'D']), $medico, $posta, '10:00');
        $this->entrar($ciudadano);

        $this->post(route('ciudadano.citas.cancelar', $ajena))->assertNotFound();
        $this->post(route('ciudadano.citas.cancelar', $propia))->assertRedirect(route('ciudadano.panel'));

        $this->assertSame('Cancelada', $propia->fresh()->estado);
        $this->assertSame('Confirmada', $ajena->fresh()->estado);
    }

    public function test_la_cita_se_descarga_como_ics(): void
    {
        [$medico, $posta, $ciudadano] = $this->escenario();
        $cita = $this->cita($ciudadano, $medico, $posta);
        $this->entrar($ciudadano);

        $respuesta = $this->get(route('ciudadano.citas.ics', $cita))->assertOk();

        $this->assertStringContainsString('text/calendar', $respuesta->headers->get('Content-Type'));
        $this->assertStringContainsString('BEGIN:VEVENT', $respuesta->getContent());
        $this->assertStringContainsString('C.S. de Prueba', $respuesta->getContent());
    }

    public function test_cerrar_sesion_exige_post_y_la_portada_no_cierra_la_sesion(): void
    {
        [, , $ciudadano] = $this->escenario();
        $this->entrar($ciudadano);

        $this->get('/')->assertOk();
        $this->get('/mi-panel')->assertOk();

        $this->get('/cerrar-sesion-ciudadano')->assertNotFound();

        $this->post(route('ciudadano.logout'))->assertRedirect(route('inicio'));
        $this->get('/mi-panel')->assertRedirect(route('inicio').'#acceso');
    }

    public function test_las_respuestas_llevan_csp_con_nonce(): void
    {
        $respuesta = $this->get('/')->assertOk();
        $csp = $respuesta->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp);
        preg_match("/'nonce-([^']+)'/", $csp, $coincidencia);
        $this->assertNotEmpty($coincidencia[1] ?? null);
        $this->assertStringContainsString('nonce="'.$coincidencia[1].'"', $respuesta->getContent());
    }

    public function test_el_personal_puede_pedir_un_enlace_para_recuperar_su_clave(): void
    {
        Notification::fake();
        $medico = User::factory()->doctor()->create(['dni' => '45678900', 'email' => 'medico@hampi.pe']);

        $this->post(route('password.email'), ['identificador' => '45678900'])
            ->assertSessionHas('mensaje');

        Notification::assertSentTo($medico, ResetPassword::class);

        // Un DNI inexistente responde igual (no revela cuentas).
        $this->post(route('password.email'), ['identificador' => '99999999'])
            ->assertSessionHas('mensaje');
    }

    public function test_el_enlace_de_recuperacion_cambia_la_clave(): void
    {
        Notification::fake();
        $medico = User::factory()->doctor()->create(['email' => 'medico@hampi.pe']);
        $this->post(route('password.email'), ['identificador' => 'medico@hampi.pe']);

        $token = null;
        Notification::assertSentTo($medico, ResetPassword::class, function ($notificacion) use (&$token) {
            $token = $notificacion->token;

            return true;
        });

        $this->get(route('password.reset', ['token' => $token, 'email' => 'medico@hampi.pe']))->assertOk();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'medico@hampi.pe',
            'password' => 'NuevaClave2026',
            'password_confirmation' => 'NuevaClave2026',
        ])->assertRedirect(route('empleado.login'));

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NuevaClave2026', $medico->fresh()->password));
    }

    /* ------------------------------------------------------------------ */

    private function entrar(Ciudadano $ciudadano): void
    {
        $ciudadano->asignarCodigo('482913');
        $this->post('/ingreso-dni', ['dni' => $ciudadano->dni, 'codigo' => '482913'])
            ->assertRedirect(route('ciudadano.panel'));
    }

    private function cita(Ciudadano $ciudadano, User $medico, Posta $posta, string $hora = '08:00'): Cita
    {
        return Cita::create([
            'ciudadano_id' => $ciudadano->id,
            'user_id' => $medico->id,
            'posta_id' => $posta->id,
            'especialidad' => 'Medicina General',
            'fecha_hora' => Carbon::tomorrow()->setTimeFromTimeString($hora),
            'estado' => 'Confirmada',
        ]);
    }

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
