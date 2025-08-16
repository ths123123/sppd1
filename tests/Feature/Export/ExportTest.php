<?php

namespace Tests\Feature\Export;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ExportTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function user_can_export_travel_request_to_pdf()
    {
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->get("/travel-requests/{$travelRequest->id}/export/pdf");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function user_can_export_travel_request_to_zip()
    {
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->get("/travel-requests/{$travelRequest->id}/export/zip");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/zip');
    }

    /** @test */
    public function user_can_download_approval_letter()
    {
        $travelRequest = TravelRequest::factory()->approved()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->get("/travel-requests/{$travelRequest->id}/download-approval");

        $response->assertStatus(200);
    }

    /** @test */
    public function guest_cannot_export_documents()
    {
        $travelRequest = TravelRequest::factory()->create();

        $response = $this->get("/travel-requests/{$travelRequest->id}/export/pdf");
        $response->assertRedirect('/login');

        $response = $this->get("/travel-requests/{$travelRequest->id}/export/zip");
        $response->assertRedirect('/login');
    }
}
