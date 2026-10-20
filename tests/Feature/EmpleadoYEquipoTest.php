<?php

namespace Tests\Feature;

use App\Models\CampanaPreventiva;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmpleadoYEquipoTest extends TestCase
{
    use RefreshDatabase;

    public function test_empleado_ingresa_con_dni_y_accede_a_su_panel(): void
    {
        $empleado = User::factory()->create([
            'name' => 'Dra. Prueba',
            'dni' => '45678912',
            'role' => User::ROL_DOCTOR,
            'password' => 'secreto123',
            'activo' => true,
        ]);

        $response = $this->post('/equipo/login', [
            'dni' => '45678912',
            'password' => 'secreto123',
        ]);

        $response->assertRedirect(route('empleado.panel'));
        $this->assertAuthenticatedAs($empleado);

        $this->get(route('empleado.panel'))
            ->assertOk()
            ->assertSee('Dra. Prueba');
    }

    public function test_empleado_no_puede_acceder_al_panel_administrativo(): void
    {
        $empleado = User::factory()->create(['role' => User::ROL_DOCTOR]);

        $this->actingAs($empleado)
            ->get('/admin/dashboard')
            ->assertRedirect(route('empleado.panel'));
    }

    public function test_personal_no_puede_usar_el_login_administrativo(): void
    {
        $empleado = User::factory()->create([
            'role' => User::ROL_DOCTOR,
            'password' => 'secreto123',
        ]);

        $response = $this->post('/personal/login', [
            'email' => $empleado->email,
            'password' => 'secreto123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_cuenta_inactiva_no_puede_acceder_al_panel(): void
    {
        $empleado = User::factory()->inactivo()->create([
            'role' => User::ROL_DOCTOR,
        ]);

        $this->actingAs($empleado)
            ->get(route('empleado.panel'))
            ->assertRedirect(route('empleado.login'))
            ->assertSessionHasErrors('dni');

        $this->assertGuest();
    }

    public function test_administrador_puede_crear_cuenta_de_trabajador(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROL_ADMIN,
            'dni' => '40123456',
        ]);

        $response = $this->actingAs($admin)->post('/admin/equipo', [
            'name' => 'Dr. Nuevo',
            'dni' => '49999999',
            'email' => 'nuevo@hampi.pe',
            'role' => User::ROL_DOCTOR,
            'especialidad' => 'Medicina General',
            'telefono' => '999 999 999',
            'establecimiento' => 'C.S. San Martín de Socabaya',
            'password' => 'prueba12345',
            'password_confirmation' => 'prueba12345',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'dni' => '49999999',
            'role' => User::ROL_DOCTOR,
        ]);
    }

    public function test_empleado_puede_actualizar_su_perfil_y_subir_foto(): void
    {
        Storage::fake('public');
        $empleado = User::factory()->create([
            'dni' => '45678912',
            'role' => User::ROL_DOCTOR,
        ]);

        $response = $this->actingAs($empleado)->post(route('empleado.perfil.update'), [
            'email' => 'nuevo-correo@hampi.pe',
            'telefono' => '999 111 222',
            'especialidad' => 'Medicina Familiar',
            'biografia' => 'Nueva biografía profesional.',
            'titulos' => 'Médico Cirujano',
            'experiencia' => 'Atención primaria.',
            // PNG 1x1 válido: el entorno de pruebas no siempre tiene la extensión GD.
            'foto_perfil' => UploadedFile::fake()->createWithContent(
                'perfil.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9Zl6sAAAAASUVORK5CYII=')
            ),
        ]);

        $response->assertRedirect();
        $empleado->refresh();

        $this->assertSame('nuevo-correo@hampi.pe', $empleado->email);
        $this->assertSame('Medicina Familiar', $empleado->especialidad);
        $this->assertNotNull($empleado->foto_perfil);
        Storage::disk('public')->assertExists($empleado->foto_perfil);
    }

    public function test_administrador_puede_ocultar_una_campana_publicada(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROL_ADMIN,
            'dni' => '40123456',
        ]);

        $campana = CampanaPreventiva::create([
            'titulo' => 'Campaña para ocultar',
            'grupo_objetivo' => 'Público general',
            'descripcion' => 'Campaña de prueba.',
            'activa' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.campanas.estado', $campana))
            ->assertRedirect();

        $this->assertFalse($campana->fresh()->activa);
    }

    public function test_solo_las_campanas_activas_se_muestran_en_el_portal(): void
    {
        CampanaPreventiva::create([
            'titulo' => 'Campaña activa de prueba',
            'grupo_objetivo' => 'Público general',
            'descripcion' => 'Campaña visible en el portal público.',
            'lugar' => 'C.S. San Martín de Socabaya',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addDay()->toDateString(),
            'activa' => true,
        ]);

        CampanaPreventiva::create([
            'titulo' => 'Campaña inactiva de prueba',
            'grupo_objetivo' => 'Público general',
            'descripcion' => 'Campaña que no debe mostrarse.',
            'lugar' => 'C.S. San Martín de Socabaya',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addDay()->toDateString(),
            'activa' => false,
        ]);

        $this->get('/campanas-preventivas')
            ->assertOk()
            ->assertSee('Campaña activa de prueba')
            ->assertDontSee('Campaña inactiva de prueba');
    }
}
