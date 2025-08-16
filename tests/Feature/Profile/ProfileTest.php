<?php

namespace Tests\Feature\Profile;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ProfileTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function user_can_view_own_profile()
    {
        $response = $this->actingAs($this->user)
            ->get('/profile');

        $response->assertStatus(200);
        $response->assertViewIs('profile.show');
    }

    /** @test */
    public function user_can_edit_own_profile()
    {
        $response = $this->actingAs($this->user)
            ->get('/profile/edit');

        $response->assertStatus(200);
        $response->assertViewIs('profile.edit');
    }

    /** @test */
    public function user_can_update_own_profile()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '08123456789'
        ];

        $response = $this->actingAs($this->user)
            ->patch('/profile', $updateData);

        $response->assertRedirect('/profile/edit');
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    /** @test */
    public function user_can_update_password()
    {
        $passwordData = [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];

        $response = $this->actingAs($this->user)
            ->put('/password', $passwordData);

        $response->assertRedirect('/');
    }

    /** @test */
    public function guest_cannot_access_profile_routes()
    {
        $response = $this->get('/profile');
        $response->assertRedirect('/login');

        $response = $this->get('/profile/edit');
        $response->assertRedirect('/login');
    }
}
