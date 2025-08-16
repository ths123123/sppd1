<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\TravelRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'travel_request_id' => TravelRequest::factory(),
            'uploaded_by' => User::factory(),
            'filename' => fake()->uuid() . '.pdf',
            'original_filename' => fake()->word() . '.pdf',
            'file_path' => 'documents/' . fake()->uuid() . '.pdf',
            'file_type' => 'pdf',
            'file_size' => fake()->numberBetween(100000, 5000000),
            'mime_type' => 'application/pdf',
            'document_type' => fake()->randomElement(['supporting', 'proof', 'receipt', 'photo', 'report', 'generated_pdf']),
            'description' => fake()->optional()->sentence(),
            'is_required' => false,
            'is_verified' => false,
            'verified_at' => null,
            'verified_by' => null,
        ];
    }

    /**
     * Indicate that the document is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => User::factory()->approver(),
        ]);
    }

    /**
     * Indicate that the document is a supporting document.
     */
    public function supporting(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => 'supporting',
            'description' => 'Dokumen Pendukung - ' . fake()->sentence(2),
        ]);
    }

    /**
     * Indicate that the document is a proof document.
     */
    public function proof(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => 'proof',
            'description' => 'Bukti Perjalanan - ' . fake()->sentence(2),
        ]);
    }

    /**
     * Indicate that the document is a receipt.
     */
    public function receipt(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => 'receipt',
            'description' => 'Kwitansi - ' . fake()->sentence(2),
        ]);
    }

    /**
     * Indicate that the document is a report.
     */
    public function report(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => 'report',
            'description' => 'Laporan Perjalanan - ' . fake()->sentence(2),
        ]);
    }
}
