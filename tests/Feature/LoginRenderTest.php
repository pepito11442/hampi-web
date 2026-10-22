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
            ->assertSee('<span class="text-signal-300">en un solo lugar.</span>', false)
            ->assertSee('Ingreso con DNI')
            ->assertSee('Horario mensual con turnos');
    }

    public function test_el_login_administrativo_renderiza_la_plantilla_compartida(): void
    {
        $this->get('/personal/login')
            ->assertOk()
            ->assertSee('La gestión de la microred,', false)
            ->assertSee('<span class="text-signal-300">en una sola hoja.</span>', false)
            ->assertSee('Acceso administrativo')
            ->assertSee('Control de intentos');
    }
}
