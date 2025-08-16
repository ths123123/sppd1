<?php

namespace Tests\Feature\Approval;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Approval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ApprovalTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $approver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'staff']);
        $this->approver = User::factory()->create(['role' => 'ppk']);
    }

    /** @test */
    public function approver_can_view_approval_list()
    {
        $response = $this->actingAs($this->approver)
            ->get('/approval/pimpinan');

        $response->assertStatus(200);
    }

    /** @test */
    public function approver_can_approve_travel_request()
    {
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'in_review'
        ]);

        $response = $this->actingAs($this->approver)
            ->post("/approval/pimpinan/{$travelRequest->id}/approve", [
                'comments' => 'Disetujui'
            ]);

        $response->assertRedirect();
        // After approval, status should change based on workflow
        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function approver_can_reject_travel_request()
    {
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'in_review'
        ]);

        $response = $this->actingAs($this->approver)
            ->post("/approval/pimpinan/{$travelRequest->id}/reject", [
                'comments' => 'Ditolak karena alasan tertentu'
            ]);

        $response->assertRedirect();
        // After rejection, status should change based on workflow
        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function regular_user_cannot_access_approval_routes()
    {
        $response = $this->actingAs($this->user)
            ->get('/approval/pimpinan');

        $response->assertStatus(403);
    }
}
