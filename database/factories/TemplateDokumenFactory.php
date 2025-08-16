<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TemplateDokumen>
 */
class TemplateDokumenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_template' => fake()->sentence(3),
            'path_file' => 'templates/' . fake()->uuid() . '.docx',
            'tipe_file' => 'docx',
            'status_aktif' => true,
            'jenis_template' => fake()->randomElement(['spd', 'sppd', 'laporan_akhir']),
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }

    /**
     * Indicate that the template is for SPPD.
     */
    public function sppd(): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis_template' => 'sppd',
            'nama_template' => 'Template SPPD - ' . fake()->sentence(2),
        ]);
    }

    /**
     * Indicate that the template is for SPD.
     */
    public function spd(): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis_template' => 'spd',
            'nama_template' => 'Template SPD - ' . fake()->sentence(2),
        ]);
    }

    /**
     * Indicate that the template is for Laporan Akhir.
     */
    public function laporanAkhir(): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis_template' => 'laporan_akhir',
            'nama_template' => 'Template Laporan Akhir - ' . fake()->sentence(2),
        ]);
    }

    /**
     * Indicate that the template is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_aktif' => true,
        ]);
    }

    /**
     * Indicate that the template is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_aktif' => false,
        ]);
    }
}
