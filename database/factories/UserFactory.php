<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
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
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password: 'password' (bcrypt)
            'remember_token' => Str::random(10),
            'nip' => fake()->unique()->numerify('1984##########'), // NIP format: 18 digit (YYYYMMDDNNNNNNNNN)
            'jabatan' => fake()->jobTitle(),
            'role' => fake()->randomElement(['staff', 'kasubbag', 'sekretaris', 'ppk']),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'pangkat' => fake()->randomElement(['Pengatur', 'Penata', 'Pembina']),
            'golongan' => fake()->randomElement(['II/c', 'III/c', 'IV/a']),
            'unit_kerja' => fake()->company(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a user with specific role.
     */
    public function role(string $role): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => $role,
        ]);
    }
}
