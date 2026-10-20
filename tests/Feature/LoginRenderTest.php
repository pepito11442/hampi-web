<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginRenderTest extends TestCase
{
    public function test_el_login_del_personal_renderiza_la_plantilla_compartida(): void
    {
        $this->get('/equipo/login')
            ->assertOk()
            ->assertSee('Tu horario y tu perfil,', false)
            ->assertSee('<span class="text-brand-300">en un solo lugar.</span>', false)
            ->assertSee('Ingreso con DNI')
            ->assertSee('Horario mensual con turnos');
    }

    public function test_el_login_administrativo_renderiza_la_plantilla_compartida(): void
    {
        $this->get('/personal/login')
            ->assertOk()
            ->assertSee('Gestión de la atención,', false)
            ->assertSee('<span class="text-brand-300">en tiempo real.</span>', false)
            ->assertSee('Personal de salud')
            ->assertSee('Rate limiting activo');
    }
}
