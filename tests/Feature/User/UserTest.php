<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class UserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    /** @test */
    public function admin_can_view_user_list()
    {
        $response = $this->actingAs($this->admin)
            ->get('/users');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_view_user_details()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get("/users/{$user->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_update_user_role()
    {
        $user = User::factory()->create(['role' => 'user']);

        $updateData = [
            'role' => 'approver'
        ];

        $response = $this->actingAs($this->admin)
            ->patch("/users/{$user->id}", $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'approver'
        ]);
    }

    /** @test */
    public function admin_can_deactivate_user()
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)
            ->patch("/users/{$user->id}/deactivate");

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_active' => false
        ]);
    }

    /** @test */
    public function admin_can_activate_user()
    {
        $user = User::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->admin)
            ->patch("/users/{$user->id}/activate");

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_active' => true
        ]);
    }

    /** @test */
    public function regular_user_cannot_access_user_management()
    {
        $regularUser = User::factory()->create();

        $response = $this->actingAs($regularUser)
            ->get('/users');

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_user_management()
    {
        $response = $this->get('/users');
        $response->assertRedirect('/login');
    }
}
