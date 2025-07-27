<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TravelRequest>
 */
class TravelRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $transportCost = fake()->numberBetween(100000, 1000000);
        $accommodationCost = fake()->numberBetween(200000, 800000);
        $dailyAllowance = fake()->numberBetween(150000, 500000);
        $otherCosts = fake()->numberBetween(0, 300000);

        return [
            'kode_sppd' => 'SPPD-' . fake()->unique()->numerify('######'),
            'user_id' => User::factory(),
            'tujuan' => fake()->city(),
            'keperluan' => fake()->sentence(),
            'tanggal_berangkat' => fake()->dateTimeBetween('now', '+30 days'),
            'tanggal_kembali' => fake()->dateTimeBetween('+1 day', '+35 days'),
            'lama_perjalanan' => fake()->numberBetween(1, 7),
            'transportasi' => fake()->randomElement(['Kereta Api', 'Bus', 'Pesawat', 'Mobil Dinas']),
            'tempat_menginap' => fake()->optional()->company() . ' Hotel',
            'biaya_transport' => $transportCost,
            'biaya_penginapan' => $accommodationCost,
            'uang_harian' => $dailyAllowance,
            'biaya_lainnya' => $otherCosts,
            'total_biaya' => $transportCost + $accommodationCost + $dailyAllowance + $otherCosts,
            'nomor_surat_tugas' => fake()->optional()->numerify('ST-###/KPU/####'),
            'catatan_pemohon' => fake()->optional()->paragraph(),
            'catatan_approval' => null,
            'status' => 'in_review',
            'current_approval_level' => 0,
            'approval_history' => null,
            'is_urgent' => fake()->boolean(10), // 10% chance of being urgent
            'submitted_at' => fake()->optional()->dateTime(),
            'approved_at' => null,
        ];
    }

    /**
     * Indicate that the travel request is in review status.
     */
    public function inReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_review',
            'submitted_at' => now(),
            'current_approval_level' => 1,
        ]);
    }

    /**
     * Indicate that the travel request is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'submitted_at' => now()->subDays(3),
            'approved_at' => now(),
            'current_approval_level' => 0,
        ]);
    }

    /**
     * Indicate that the travel request is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'submitted_at' => now()->subDays(2),
            'approved_at' => null,
            'current_approval_level' => 0,
        ]);
    }

    /**
     * Indicate that the travel request is urgent.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_urgent' => true,
        ]);
    }
}
