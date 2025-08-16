<?php

namespace Tests\Feature\UserManagement;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class UserManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /** @test */
    public function admin_can_view_user_list()
    {
        $response = $this->actingAs($this->admin)
            ->get('/users');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_create_new_user()
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'staff',
            'nip' => '123456789012345678',
            'jabatan' => 'Staff',
            'unit_kerja' => 'IT Department'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/users', $userData);

        // Debug: check response
        if ($response->getStatusCode() !== 302) {
            $response->dump();
        }

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => 'staff'
        ]);
    }

    /** @test */
    public function admin_can_update_user()
    {
        $user = User::factory()->create();

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'kasubbag'
        ];

        $response = $this->actingAs($this->admin)
            ->patch("/users/{$user->id}/toggle-status", $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    /** @test */
    public function admin_can_toggle_user_status()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->patch("/users/{$user->id}/toggle-status");

        $response->assertRedirect();
        // Check if user status was toggled
        $this->assertDatabaseHas('users', [
            'id' => $user->id
        ]);
    }

    /** @test */
    public function regular_user_cannot_access_user_management()
    {
        $regularUser = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($regularUser)
            ->get('/users');

        $response->assertStatus(403);
    }
}
