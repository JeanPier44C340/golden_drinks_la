<?php

namespace Database\Factories;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre_completo' => fake()->name(),
            'correo' => fake()->unique()->safeEmail(),
            'password_hash' => static::$password ??= Hash::make('password'),
            'telefono' => fake()->numerify('300#######'),
            'rol_id' => fn () => Rol::query()->value('id') ?? Rol::query()->create([
                'nombre' => 'administrador',
                'descripcion' => 'Control total del sistema',
                'activo' => true,
            ])->id,
            'estado' => 'activo',
        ];
    }
}
