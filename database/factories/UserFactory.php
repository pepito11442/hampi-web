<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'dni' => (string) fake()->unique()->numberBetween(10000000, 99999999),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'password',
            'role' => User::ROL_ENFERMERA,
            'establecimiento' => 'C.S. San Martín de Socabaya',
            'telefono' => fake()->numerify('9## ### ###'),
            'activo' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (): array => ['role' => User::ROL_ADMIN]);
    }

    public function doctor(): static
    {
        return $this->state(fn (): array => [
            'role' => User::ROL_DOCTOR,
            'especialidad' => 'Medicina General',
        ]);
    }

    public function enfermero(): static
    {
        return $this->state(fn (): array => [
            'role' => User::ROL_ENFERMERO,
            'especialidad' => 'Enfermería',
        ]);
    }

    public function inactivo(): static
    {
        return $this->state(fn (): array => ['activo' => false]);
    }
}
