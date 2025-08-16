<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->word(),
            'value' => fake()->sentence(),
            'type' => fake()->randomElement(['string', 'number', 'boolean', 'json']),
            'group' => fake()->randomElement(['general', 'approval', 'notification', 'system']),
            'label' => fake()->sentence(2),
            'description' => fake()->optional()->sentence(),
            'is_editable' => true,
        ];
    }

    /**
     * Indicate that the setting is a system name.
     */
    public function systemName(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'system_name',
            'value' => 'SPPD KPU System',
            'type' => 'string',
            'group' => 'system',
            'label' => 'Nama Sistem',
            'description' => 'Nama resmi sistem SPPD KPU',
            'is_editable' => true,
        ]);
    }

    /**
     * Indicate that the setting is a system description.
     */
    public function systemDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'system_description',
            'value' => 'Sistem Pengelolaan Surat Perintah Perjalanan Dinas KPU',
            'type' => 'string',
            'group' => 'system',
            'label' => 'Deskripsi Sistem',
            'description' => 'Deskripsi lengkap sistem SPPD KPU',
            'is_editable' => true,
        ]);
    }

    /**
     * Indicate that the setting is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => 'general',
            'is_editable' => true,
        ]);
    }

    /**
     * Indicate that the setting is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => 'system',
            'is_editable' => false,
        ]);
    }
}
