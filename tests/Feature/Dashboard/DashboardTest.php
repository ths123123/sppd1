<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class DashboardTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function authenticated_user_can_access_dashboard()
    {
        $response = $this->actingAs($this->user)
            ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.dashboard-utama');
    }

    /** @test */
    public function dashboard_shows_user_travel_requests_count()
    {
        // Create some travel requests for the user
        TravelRequest::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->get('/dashboard');

        $response->assertStatus(200);
        // Dashboard should show the count of user's travel requests
    }

    /** @test */
    public function dashboard_shows_pending_approvals_count()
    {
        // Create a travel request that needs approval
        TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'in_review'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/dashboard');

        $response->assertStatus(200);
        // Dashboard should show pending approvals
    }

    /** @test */
    public function guest_cannot_access_dashboard()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }
}
