<?php

namespace Tests\Feature;

use App\Models\Posta;
use App\Models\User;
use App\Models\VacunaAviso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VacunaAvisoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_admin_publica_y_retira_avisos_del_pizarrin(): void
    {
        Posta::create([
            'nombre' => 'C.S. de Prueba',
            'direccion' => 'Av. Prueba 123',
        ]);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/pizarrin/guardar', [
                'titulo' => 'Vacuna de prueba',
                'grupo_objetivo' => 'Niños menores de 5 años',
                'stock_estado' => 'Disponible',
                'requisitos' => 'DNI y carné de vacunación',
                'es_urgente' => 1,
            ])
            ->assertRedirect('/admin/dashboard');

        $aviso = VacunaAviso::firstOrFail();

        $this->assertSame('Vacuna de prueba', $aviso->titulo);
        $this->assertSame('Disponible', $aviso->stock_estado);
        $this->assertSame(1, (int) $aviso->es_urgente);

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Vacuna de prueba');

        $this->actingAs($admin)
            ->delete("/admin/pizarrin/{$aviso->id}")
            ->assertRedirect('/admin/dashboard');

        $this->assertDatabaseCount('vacuna_avisos', 0);
    }

    public function test_un_estado_de_stock_invalido_es_rechazado(): void
    {
        Posta::create([
            'nombre' => 'C.S. de Prueba',
            'direccion' => 'Av. Prueba 123',
        ]);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/pizarrin/guardar', [
                'titulo' => 'Vacuna inválida',
                'grupo_objetivo' => 'Población general',
                'stock_estado' => 'Inventado',
                'requisitos' => 'DNI',
            ])
            ->assertSessionHasErrors('stock_estado');

        $this->assertDatabaseCount('vacuna_avisos', 0);
    }
}
