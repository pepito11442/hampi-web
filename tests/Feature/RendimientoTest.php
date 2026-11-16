<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Ciudadano;
use App\Models\Horario;
use App\Models\Posta;
use App\Models\User;
use App\Services\AgendaCitas;
use Carbon\Carbon;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Optimizaciones de navegación: columnas cargadas, consultas agrupadas,
 * agenda sin N+1, sesión en cookie y Speculation Rules.
 */
class RendimientoTest extends TestCase
{
    use RefreshDatabase;

    public function test_las_columnas_por_defecto_de_users_son_todas_menos_la_foto(): void
    {
        $esperadas = array_values(array_diff(Schema::getColumnListing('users'), ['foto_data']));

        $this->assertEqualsCanonicalizing($esperadas, User::COLUMNAS_SIN_FOTO);
    }

    public function test_las_consultas_de_usuarios_no_traen_foto_data_pero_la_url_de_la_foto_funciona(): void
    {
        $medico = User::factory()->doctor()->create();
        $medico->update(['foto_data' => base64_encode('imagen'), 'foto_mime' => 'image/webp']);

        $sql = $this->capturarConsultas(fn () => User::find($medico->id));
        $this->assertStringNotContainsString('foto_data', implode("\n", $sql));

        $cargado = User::find($medico->id);
        $this->assertNull($cargado->getAttributes()['foto_data'] ?? null);
        $this->assertSame(substr(md5(base64_encode('imagen')), 0, 12), $cargado->foto_version);
        $this->assertSame('/fotos/'.$medico->id.'/'.$cargado->foto_version, $cargado->foto_url);

        $this->get($cargado->foto_url)->assertOk()->assertHeader('Content-Type', 'image/webp');
    }

    public function test_los_conteos_del_personal_salen_de_una_sola_consulta(): void
    {
        User::factory()->doctor()->count(2)->create();
        User::factory()->create(['role' => User::ROL_ENFERMERO]);
        User::factory()->create(['role' => User::ROL_ENFERMERA]);
        User::factory()->create(['role' => User::ROL_ADMIN]);

        $sql = $this->capturarConsultas(fn () => $this->assertSame(
            ['todos' => 4, 'doctores' => 2, 'enfermeros' => 2],
            User::conteosPersonal()
        ));

        $this->assertCount(1, $sql);
    }

    public function test_el_primer_dia_con_cupo_no_hace_consultas_por_dia(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(6, 0));
        $medico = User::factory()->doctor()->create();
        $posta = Posta::create(['nombre' => 'C.S. Prueba', 'direccion' => 'Av. 1', 'estado_activo' => true]);
        $ciudadano = Ciudadano::create(['dni' => '70000001', 'nombres' => 'Ana', 'apellidos' => 'Quispe']);

        // Diez días con turno de una hora (2 bloques); los nueve primeros, llenos.
        for ($d = 1; $d <= 10; $d++) {
            $fecha = Carbon::today()->addDays($d);
            Horario::create(['user_id' => $medico->id, 'fecha' => $fecha->toDateString(), 'turno' => 'manana', 'hora_inicio' => '07:00:00', 'hora_fin' => '08:00:00', 'horas' => 1]);

            if ($d < 10) {
                foreach (['07:00', '07:30'] as $hora) {
                    Cita::create(['ciudadano_id' => $ciudadano->id, 'user_id' => $medico->id, 'posta_id' => $posta->id, 'especialidad' => 'Medicina General', 'fecha_hora' => $fecha->copy()->setTimeFromTimeString($hora), 'estado' => 'Confirmada']);
                }
            }
        }

        $horarios = Horario::where('user_id', $medico->id)->orderBy('fecha')->get();
        $agenda = app(AgendaCitas::class);

        $resultado = null;
        $sql = $this->capturarConsultas(function () use (&$resultado, $agenda, $medico, $horarios) {
            $resultado = $agenda->primerDiaConCupo($medico, $horarios);
        });

        $this->assertCount(1, $sql);
        $this->assertTrue($resultado['fecha']->isSameDay(Carbon::today()->addDays(10)));
        // Mismo resultado que el cálculo día por día.
        $this->assertSame($agenda->slots($medico, $resultado['fecha']), $resultado['bloques']);

        Carbon::setTestNow();
    }

    public function test_la_sesion_va_en_cookie_y_no_usa_la_tabla_sessions(): void
    {
        // phpunit.xml usa "array"; aquí se prueba el driver de producción.
        config(['session.driver' => 'cookie']);

        $sql = $this->capturarConsultas(fn () => $this->get('/directorio-postas')->assertOk());

        $this->assertStringNotContainsString('`sessions`', implode("\n", $sql));
        $this->assertStringNotContainsString('"sessions"', implode("\n", $sql));
    }

    public function test_las_reglas_de_especulacion_usan_el_nonce_de_la_csp_y_excluyen_acciones(): void
    {
        $respuesta = $this->get('/')->assertOk();

        preg_match("/'nonce-([^']+)'/", (string) $respuesta->headers->get('Content-Security-Policy'), $nonce);
        $this->assertNotEmpty($nonce[1] ?? null);

        preg_match('#<script type="speculationrules" nonce="([^"]+)">(.*?)</script>#s', $respuesta->getContent(), $script);
        $this->assertSame($nonce[1], $script[1] ?? null);

        $reglas = json_decode($script[2], true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('moderate', $reglas['prerender'][0]['eagerness']);
        $this->assertSame('moderate', $reglas['prefetch'][0]['eagerness']);

        $excluidas = $reglas['prefetch'][0]['where']['and'][1]['not']['href_matches'];
        foreach (['/mi-panel/citas/*', '/fotos/*', '/api/*', '/equipo/restablecer/*'] as $patron) {
            $this->assertContains($patron, $excluidas);
        }
    }

    public function test_una_precarga_especulativa_no_modifica_la_sesion(): void
    {
        config(['session.driver' => 'cookie']);

        $this->get('/medicos-hoy')->assertOk()->assertCookie(config('session.cookie'));

        $precarga = $this->get('/medicos-hoy', ['Sec-Purpose' => 'prefetch;prerender'])->assertOk();
        $this->assertEmpty($precarga->headers->getCookies());

        $prefetch = $this->get('/medicos-hoy', ['Sec-Purpose' => 'prefetch'])->assertOk();
        $this->assertEmpty($prefetch->headers->getCookies());
    }

    /** @return array<int, string> */
    private function capturarConsultas(callable $accion): array
    {
        $sql = [];
        DB::listen(function (QueryExecuted $q) use (&$sql) {
            $sql[] = $q->sql;
        });

        $accion();

        return $sql;
    }
}
