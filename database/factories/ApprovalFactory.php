<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\TravelRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Approval>
 */
class ApprovalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $level = fake()->numberBetween(1, 3);
        $role = match($level) {
            1 => 'kasubbag',
            2 => 'sekretaris',
            3 => 'ppk',
            default => 'kasubbag'
        };

        return [
            'travel_request_id' => TravelRequest::factory(),
            'approver_id' => User::factory()->create(['role' => $role]),
            'level' => $level,
            'role' => $role,
            'status' => 'pending',
            'comments' => fake()->optional()->sentence(),
            'revision_notes' => null,
            'approved_at' => null,
            'rejected_at' => null,
        ];
    }

    /**
     * Indicate that the approval is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'approved_at' => null,
            'rejected_at' => null,
        ]);
    }

    /**
     * Indicate that the approval is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_at' => now(),
            'rejected_at' => null,
            'comments' => fake()->optional()->sentence(),
        ]);
    }

    /**
     * Indicate that the approval is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'approved_at' => null,
            'rejected_at' => now(),
            'comments' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the approval is for Kasubbag level.
     */
    public function kasubbag(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 1,
            'role' => 'kasubbag',
        ]);
    }

    /**
     * Indicate that the approval is for Sekretaris level.
     */
    public function sekretaris(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 2,
            'role' => 'sekretaris',
        ]);
    }

    /**
     * Indicate that the approval is for PPK level.
     */
    public function ppk(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 3,
            'role' => 'ppk',
        ]);
    }
}
