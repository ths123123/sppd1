<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'message' => fake()->paragraph(),
            'type' => fake()->randomElement(['info', 'success', 'warning', 'error']),
            'is_read' => false,
            'read_at' => null,
            'data' => null,
            'action_url' => fake()->optional()->url(),
            'action_text' => fake()->optional()->words(2, true),
            'is_important' => false,
        ];
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Indicate that the notification is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Indicate that the notification is an info type.
     */
    public function info(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'info',
        ]);
    }

    /**
     * Indicate that the notification is a success type.
     */
    public function success(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'success',
        ]);
    }

    /**
     * Indicate that the notification is a warning type.
     */
    public function warning(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'warning',
        ]);
    }

    /**
     * Indicate that the notification is an error type.
     */
    public function error(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'error',
        ]);
    }

    /**
     * Indicate that the notification is about travel request approval.
     */
    public function travelRequestApproved(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'SPPD Disetujui',
            'message' => 'Surat Perintah Perjalanan Dinas Anda telah disetujui.',
            'type' => 'success',
        ]);
    }

    /**
     * Indicate that the notification is about travel request rejection.
     */
    public function travelRequestRejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => 'SPPD Ditolak',
            'message' => 'Surat Perintah Perjalanan Dinas Anda telah ditolak.',
            'type' => 'error',
        ]);
    }
}
