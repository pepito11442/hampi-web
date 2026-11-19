<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Ciudadano;
use App\Models\Posta;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Pruebas de regresión de los hallazgos de docs/seguridad (H-01 … H-07).
 */
class HallazgosSeguridadTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int, MessageLogged> */
    private array $registros = [];

    protected function setUp(): void
    {
        parent::setUp();

        Event::listen(MessageLogged::class, function (MessageLogged $evento) {
            $this->registros[] = $evento;
        });
    }

    /* H-01 · A03 ------------------------------------------------------------ */

    public function test_h01_el_framework_es_laravel_12_o_superior(): void
    {
        $this->assertTrue(
            version_compare($this->app->version(), '12.0.0', '>='),
            'Laravel '.$this->app->version().' ya no recibe parches de seguridad.'
        );
    }

    /* H-02 · A01/A06 -------------------------------------------------------- */

    public function test_h02_la_consulta_reniec_ya_no_existe(): void
    {
        $this->getJson('/api/consultar-dni/45879632')->assertNotFound();
    }

    public function test_h03_se_audita_el_codigo_generado_en_ventanilla(): void
    {
        $ciudadano = Ciudadano::create(['dni' => '72345678', 'nombres' => 'Rosa', 'apellidos' => 'Quispe']);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.ciudadanos.codigo'), ['dni' => $ciudadano->dni]);

        $registro = $this->auditoria('codigo.generado');
        $this->assertSame($admin->id, $registro->context['usuario_id']);
        $this->assertSame($ciudadano->id, $registro->context['ciudadano_afectado']);
        $this->assertSame('127.0.0.1', $registro->context['ip']);
        $this->assertArrayHasKey('fecha', $registro->context);
        // Nunca se registra el código en sí.
        $this->assertArrayNotHasKey('codigo', $registro->context);
    }

    public function test_h03_se_audita_el_cambio_de_contrasena_y_el_borrado_de_usuarios(): void
    {
        $admin = User::factory()->admin()->create();
        $medico = User::factory()->doctor()->create();

        $this->actingAs($admin)->post(route('admin.equipo.update', $medico), [
            'name' => $medico->name,
            'dni' => $medico->dni,
            'email' => $medico->email,
            'role' => User::ROL_DOCTOR,
            'activo' => '1',
            'password' => 'OtraClave2026',
            'password_confirmation' => 'OtraClave2026',
        ])->assertSessionHasNoErrors();

        $this->assertSame($medico->id, $this->auditoria('contrasena.cambiada')->context['usuario_afectado']);

        $this->actingAs($admin)->delete(route('admin.equipo.destroy', $medico));

        $registro = $this->auditoria('usuario.eliminado');
        $this->assertSame($admin->id, $registro->context['usuario_id']);
        $this->assertSame($medico->id, $registro->context['usuario_afectado']);
    }

    public function test_h03_se_audita_el_cambio_de_estado_de_una_cita(): void
    {
        $medico = User::factory()->doctor()->create();
        $posta = Posta::create(['nombre' => 'C.S. Prueba', 'direccion' => 'Av. 1', 'estado_activo' => true]);
        $ciudadano = Ciudadano::create(['dni' => '72345678', 'nombres' => 'Rosa', 'apellidos' => 'Quispe']);
        $cita = Cita::create([
            'ciudadano_id' => $ciudadano->id,
            'user_id' => $medico->id,
            'posta_id' => $posta->id,
            'especialidad' => 'Medicina General',
            'fecha_hora' => Carbon::tomorrow()->setTime(8, 0),
            'estado' => 'Confirmada',
        ]);

        $this->actingAs($medico)->post(route('empleado.citas.estado', $cita), ['estado' => 'Cancelada']);

        $registro = $this->auditoria('cita.estado_cambiado');
        $this->assertSame($cita->id, $registro->context['cita_id']);
        $this->assertSame('Confirmada', $registro->context['de']);
        $this->assertSame('Cancelada', $registro->context['a']);
    }

    /* H-05 / H-06 · A02 ----------------------------------------------------- */

    public function test_h05_el_portal_no_se_puede_incrustar_en_iframes(): void
    {
        $respuesta = $this->get('/')->assertOk();

        $respuesta->assertHeader('X-Frame-Options', 'DENY');
        $this->assertStringContainsString("frame-ancestors 'none'", $respuesta->headers->get('Content-Security-Policy'));
    }

    public function test_h06_la_csp_no_permite_estilos_en_linea(): void
    {
        $csp = $this->get('/')->headers->get('Content-Security-Policy');

        preg_match('/style-src ([^;]+)/', $csp, $estilo);
        $this->assertNotEmpty($estilo);
        $this->assertStringNotContainsString('unsafe-inline', $estilo[1]);
    }

    public function test_h06_el_grafico_del_admin_no_usa_style_en_linea(): void
    {
        $admin = User::factory()->admin()->create();

        $html = $this->actingAs($admin)->get('/admin/dashboard')->assertOk()->getContent();

        $this->assertDoesNotMatchRegularExpression('/\sstyle="/', $html);
        $this->assertStringContainsString('data-alto="', $html);
    }

    public function test_h06_la_pagina_404_no_usa_estilos_en_linea(): void
    {
        $html = $this->get('/no-existe-esta-pagina')->assertNotFound()->getContent();

        $this->assertStringNotContainsString('<style', $html);
    }

    /* H-07 · A01/A02 -------------------------------------------------------- */

    public function test_h07_la_api_no_envia_cors_abierto(): void
    {
        $this->withHeader('Origin', 'https://sitio-ajeno.example')
            ->getJson('/')
            ->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    /* ---------------------------------------------------------------------- */

    private function auditoria(string $accion): MessageLogged
    {
        $registro = collect($this->registros)->last(fn (MessageLogged $e) => $e->message === $accion);

        $this->assertNotNull($registro, "No se registró la acción de auditoría «{$accion}».");

        return $registro;
    }
}
