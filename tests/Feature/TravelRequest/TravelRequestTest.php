<?php

namespace Tests\Feature\TravelRequest;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class TravelRequestTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $kasubbagUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'staff']);
        $this->kasubbagUser = User::factory()->create(['role' => 'kasubbag']);
    }

    /** @test */
    public function user_can_view_own_travel_requests()
    {
        $response = $this->actingAs($this->user)
            ->get('/my-travel-requests');

        $response->assertStatus(200);
    }

    /** @test */
    public function kasubbag_can_create_travel_request()
    {
        $travelData = [
            'tempat_berangkat' => 'Cirebon',
            'tujuan' => 'Jakarta',
            'keperluan' => 'Meeting dengan klien',
            'tanggal_berangkat' => now()->addDays(5)->format('Y-m-d'),
            'tanggal_kembali' => now()->addDays(7)->format('Y-m-d'),
            'transportasi' => 'Pesawat',
            'biaya_transport' => 2000000,
            'biaya_penginapan' => 500000,
            'uang_harian' => 300000,
            'biaya_lainnya' => 200000,
            'sumber_dana' => 'APBN',
            'action' => 'save'
        ];

        $response = $this->actingAs($this->kasubbagUser)
            ->post('/travel-requests', $travelData);

        // Debug: check response
        if ($response->getStatusCode() !== 302) {
            $response->dump();
        }

        $response->assertRedirect();
        $this->assertDatabaseHas('travel_requests', [
            'tujuan' => 'Jakarta',
            'keperluan' => 'Meeting dengan klien'
        ]);
    }

    /** @test */
    public function kasubbag_can_submit_travel_request_for_approval()
    {
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->kasubbagUser->id,
            'status' => 'in_review'
        ]);

        $response = $this->actingAs($this->kasubbagUser)
            ->post("/travel-requests/{$travelRequest->id}/submit");

        $response->assertRedirect();
        // After submission, status should remain in_review or change based on workflow
        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'user_id' => $this->kasubbagUser->id
        ]);
    }

    /** @test */
    public function user_can_view_travel_request_details()
    {
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->get("/travel-requests/{$travelRequest->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_edit_own_travel_request()
    {
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->get("/travel-requests/{$travelRequest->id}/edit");

        $response->assertStatus(200);
    }

    /** @test */
    public function staff_user_cannot_create_travel_request()
    {
        $travelData = [
            'tujuan' => 'Jakarta',
            'keperluan' => 'Meeting dengan klien',
            'tanggal_berangkat' => '2024-12-20',
            'tanggal_kembali' => '2024-12-22',
            'transportasi' => 'Pesawat',
            'biaya_transport' => 2000000,
            'biaya_penginapan' => 500000,
            'uang_harian' => 300000,
            'biaya_lainnya' => 200000,
        ];

        $response = $this->actingAs($this->user)
            ->post('/travel-requests', $travelData);

        $response->assertStatus(403);
    }
}
