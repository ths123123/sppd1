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
        $tanggalBerangkat = fake()->dateTimeBetween('now', '+1 month');
        $tanggalKembali = fake()->dateTimeBetween($tanggalBerangkat, '+2 months');
        $lamaPerjalanan = (strtotime($tanggalKembali->format('Y-m-d')) - strtotime($tanggalBerangkat->format('Y-m-d'))) / (60 * 60 * 24);
        
        $biayaTransport = fake()->numberBetween(500000, 5000000);
        $biayaPenginapan = fake()->numberBetween(200000, 2000000);
        $uangHarian = fake()->numberBetween(100000, 500000) * $lamaPerjalanan;
        $biayaLainnya = fake()->numberBetween(100000, 1000000);
        $totalBiaya = $biayaTransport + $biayaPenginapan + $uangHarian + $biayaLainnya;
        
        return [
            'kode_sppd' => 'SPPD-' . fake()->unique()->numerify('####'),
            'user_id' => User::factory(),
            'tujuan' => fake()->city(),
            'keperluan' => fake()->sentence(),
            'tanggal_berangkat' => $tanggalBerangkat->format('Y-m-d'),
            'tanggal_kembali' => $tanggalKembali->format('Y-m-d'),
            'lama_perjalanan' => $lamaPerjalanan,
            'transportasi' => fake()->randomElement(['Pesawat', 'Kereta', 'Bus', 'Mobil Dinas']),
            'tempat_menginap' => fake()->optional()->company(),
            'biaya_transport' => $biayaTransport,
            'biaya_penginapan' => $biayaPenginapan,
            'uang_harian' => $uangHarian,
            'biaya_lainnya' => $biayaLainnya,
            'total_biaya' => $totalBiaya,
            'sumber_dana' => fake()->optional()->randomElement(['APBN', 'APBD', 'Dana Sendiri']),
            'status' => 'in_review',
            'current_approval_level' => 0,
            'approval_history' => [],
            'catatan_pemohon' => fake()->optional()->sentence(),
            'catatan_approval' => null,
            'is_urgent' => fake()->boolean(20),
            'nomor_surat_tugas' => null,
            'tanggal_surat_tugas' => null,
            'submitted_at' => now(),
            'approved_at' => null,
        ];
    }

    /**
     * Indicate that the travel request is in revision.
     */
    public function revision(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'revision',
            'catatan_approval' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the travel request is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'approved_at' => now(),
            'current_approval_level' => 3, // Assuming 3 levels of approval
        ]);
    }

    /**
     * Indicate that the travel request is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'catatan_approval' => fake()->sentence(),
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
