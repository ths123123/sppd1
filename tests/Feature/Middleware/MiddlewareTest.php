<?php

namespace Tests\Feature\Middleware;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Approval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $admin;
    protected $approver;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->admin = User::factory()->admin()->create();
        $this->approver = User::factory()->approver()->create();
    }

    /** @test */
    public function guest_cannot_access_protected_routes()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_access_protected_routes()
    {
        $response = $this->actingAs($this->user)
            ->get('/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function non_admin_user_cannot_access_admin_routes()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_user_can_access_admin_routes()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function non_approver_user_cannot_access_approver_routes()
    {
        $response = $this->actingAs($this->user)
            ->get('/approver/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function approver_user_can_access_approver_routes()
    {
        $response = $this->actingAs($this->approver)
            ->get('/approver/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function user_cannot_access_other_user_travel_requests()
    {
        $otherUser = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $otherUser->id
        ]);

        $response = $this->actingAs($this->user)
            ->get("/travel-requests/{$travelRequest->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_access_own_travel_requests()
    {
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->get("/travel-requests/{$travelRequest->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function user_cannot_edit_other_user_travel_requests()
    {
        $otherUser = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $otherUser->id
        ]);

        $response = $this->actingAs($this->user)
            ->put("/travel-requests/{$travelRequest->id}", [
                'tujuan' => 'Updated Destination'
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_edit_own_travel_requests()
    {
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'draft'
        ]);

        $response = $this->actingAs($this->user)
            ->put("/travel-requests/{$travelRequest->id}", [
                'tujuan' => 'Updated Destination'
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function user_cannot_delete_other_user_travel_requests()
    {
        $otherUser = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $otherUser->id
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/travel-requests/{$travelRequest->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_delete_own_draft_travel_requests()
    {
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'draft'
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/travel-requests/{$travelRequest->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function user_cannot_delete_submitted_travel_requests()
    {
        $travelRequest = TravelRequest::factory()->submitted()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/travel-requests/{$travelRequest->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function approver_can_access_approval_routes()
    {
        $response = $this->actingAs($this->approver)
            ->get('/approvals');

        $response->assertStatus(200);
    }

    /** @test */
    public function non_approver_cannot_access_approval_routes()
    {
        $response = $this->actingAs($this->user)
            ->get('/approvals');

        $response->assertStatus(403);
    }

    /** @test */
    public function approver_can_approve_travel_requests()
    {
        $travelRequest = TravelRequest::factory()->submitted()->create([
            'user_id' => $this->user->id
        ]);

        $approval = Approval::factory()->create([
            'travel_request_id' => $travelRequest->id,
            'approver_id' => $this->approver->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->approver)
            ->put("/approvals/{$approval->id}", [
                'status' => 'approved',
                'catatan' => 'Disetujui'
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function approver_cannot_approve_own_travel_requests()
    {
        $travelRequest = TravelRequest::factory()->submitted()->create([
            'user_id' => $this->approver->id
        ]);

        $approval = Approval::factory()->create([
            'travel_request_id' => $travelRequest->id,
            'approver_id' => $this->approver->id,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->approver)
            ->put("/approvals/{$approval->id}", [
                'status' => 'approved',
                'catatan' => 'Disetujui'
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_all_routes()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/users');

        $response->assertStatus(200);

        $response = $this->actingAs($this->admin)
            ->get('/admin/settings');

        $response->assertStatus(200);

        $response = $this->actingAs($this->admin)
            ->get('/admin/logs');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_override_approval_decisions()
    {
        $travelRequest = TravelRequest::factory()->rejected()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/admin/travel-requests/{$travelRequest->id}/approve", [
                'status' => 'approved',
                'catatan' => 'Admin override'
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function middleware_prevents_csrf_attacks()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->post('/travel-requests', [
            'tujuan' => 'Test Destination',
            'keperluan' => 'Test Purpose'
        ]);

        $response->assertStatus(419); // CSRF token mismatch
    }

    /** @test */
    public function middleware_handles_rate_limiting()
    {
        // Simulate multiple rapid requests
        for ($i = 0; $i < 60; $i++) {
            $response = $this->actingAs($this->user)
                ->get('/dashboard');
            
            if ($response->status() === 429) {
                break;
            }
        }

        // After rate limit is exceeded
        $response = $this->actingAs($this->user)
            ->get('/dashboard');

        $response->assertStatus(429);
    }

    /** @test */
    public function middleware_prevents_sql_injection()
    {
        $maliciousInput = "'; DROP TABLE users; --";

        $response = $this->actingAs($this->user)
            ->get("/travel-requests/search?q={$maliciousInput}");

        // Should not crash and should handle gracefully
        $response->assertStatus(200);
    }

    /** @test */
    public function middleware_prevents_xss_attacks()
    {
        $maliciousInput = '<script>alert("XSS")</script>';

        $response = $this->actingAs($this->user)
            ->post('/travel-requests', [
                'tujuan' => $maliciousInput,
                'keperluan' => 'Test Purpose'
            ]);

        // Should escape HTML and prevent XSS
        $response->assertStatus(422); // Validation error
    }

    /** @test */
    public function middleware_handles_session_timeout()
    {
        // Simulate session timeout by clearing session
        $this->actingAs($this->user);
        
        // Clear session
        $this->app['session']->flush();

        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function middleware_prevents_unauthorized_file_access()
    {
        $document = Document::factory()->create([
            'user_id' => $this->user->id
        ]);

        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->get("/documents/{$document->id}/download");

        $response->assertStatus(403);
    }

    /** @test */
    public function middleware_allows_authorized_file_access()
    {
        $document = Document::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->get("/documents/{$document->id}/download");

        $response->assertStatus(200);
    }

    /** @test */
    public function middleware_prevents_privilege_escalation()
    {
        // User trying to access admin functionality
        $response = $this->actingAs($this->user)
            ->post('/admin/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password',
                'role' => 'admin'
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function middleware_prevents_mass_assignment()
    {
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', [
                'tujuan' => 'Test Destination',
                'keperluan' => 'Test Purpose',
                'user_id' => 999, // Trying to assign to different user
                'status' => 'approved' // Trying to set approved status directly
            ]);

        $response->assertStatus(422); // Validation error
    }

    /** @test */
    public function middleware_handles_invalid_json()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post('/api/travel-requests', 'invalid json');

        $response->assertStatus(400);
    }

    /** @test */
    public function middleware_prevents_method_spoofing()
    {
        $response = $this->actingAs($this->user)
            ->post('/travel-requests/999', [
                '_method' => 'PUT',
                'tujuan' => 'Updated Destination'
            ]);

        $response->assertStatus(404); // Resource not found
    }
}
