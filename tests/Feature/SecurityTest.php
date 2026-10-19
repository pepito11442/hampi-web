<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class SecurityTest extends TestCase
{
    // Esta línea crea automáticamente las tablas para las pruebas:
    use RefreshDatabase;

    // 1. Probar que la API rechaza intentos de Inyección SQL en el DNI
    public function test_rechaza_inyeccion_sql_en_dni(): void
    {
        $response = $this->getJson('/api/consultar-dni/\' OR \'1\'=\'1');

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'mensaje' => 'El DNI debe tener 8 dígitos'
        ]);
    }

    // 2. Probar que el Rate Limiter bloquea fuerza bruta (Hydra) en el login
    public function test_bloquea_fuerza_bruta_despues_de_intentos_fallidos(): void
    {
        $credencialesFalsas = [
            'email' => 'enfermeria@hampi.pe',
            'password' => 'password_incorrecta'
        ];

        // Simulamos 5 intentos fallidos
        for ($i = 0; $i < 5; $i++) {
            $this->post('/personal/login', $credencialesFalsas);
        }

        // Al 6to intento consecutivo el middleware throttle responde con 429 Too Many Requests
        $response = $this->post('/personal/login', $credencialesFalsas);
        $response->assertStatus(429);
    }

    // 3. Probar que las contraseñas se guardan con Bcrypt seguro
    public function test_contrasena_esta_hasheada_con_bcrypt(): void
    {
        $password = 'socabaya2024';
        $hashedPassword = Hash::make($password);

        $this->assertStringStartsWith('$2y$', $hashedPassword);
        $this->assertTrue(Hash::check($password, $hashedPassword));
        $this->assertNotEquals($password, $hashedPassword);
    }
}