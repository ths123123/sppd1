<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityLog>
 */
class ActivityLogFactory extends Factory
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
            'action' => fake()->randomElement(['login', 'logout', 'create', 'update', 'delete', 'export', 'import']),
            'description' => fake()->sentence(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'url' => fake()->url(),
            'method' => fake()->randomElement(['GET', 'POST', 'PUT', 'PATCH', 'DELETE']),
            'status_code' => fake()->randomElement([200, 201, 400, 401, 403, 404, 500]),
            'response_time' => fake()->numberBetween(100, 5000),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the activity is a login action.
     */
    public function login(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'login',
            'description' => 'User logged in successfully',
            'method' => 'POST',
            'status_code' => 200,
        ]);
    }

    /**
     * Indicate that the activity is a logout action.
     */
    public function logout(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'logout',
            'description' => 'User logged out',
            'method' => 'POST',
            'status_code' => 200,
        ]);
    }

    /**
     * Indicate that the activity is a create action.
     */
    public function createAction(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'create',
            'description' => 'Record created successfully',
            'method' => 'POST',
            'status_code' => 201,
        ]);
    }

    /**
     * Indicate that the activity is an update action.
     */
    public function update(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'update',
            'description' => 'Record updated successfully',
            'method' => 'PUT',
            'status_code' => 200,
        ]);
    }

    /**
     * Indicate that the activity is a delete action.
     */
    public function delete(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'delete',
            'description' => 'Record deleted successfully',
            'method' => 'DELETE',
            'status_code' => 200,
        ]);
    }

    /**
     * Indicate that the activity is an export action.
     */
    public function export(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'export',
            'description' => 'Data exported successfully',
            'method' => 'GET',
            'status_code' => 200,
        ]);
    }

    /**
     * Indicate that the activity is an import action.
     */
    public function import(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'import',
            'description' => 'Data imported successfully',
            'method' => 'POST',
            'status_code' => 200,
        ]);
    }
}
