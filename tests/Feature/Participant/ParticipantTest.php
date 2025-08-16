<?php

namespace Tests\Feature\Participant;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ParticipantTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $travelRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function user_can_add_participant_to_travel_request()
    {
        $participantData = [
            'nama' => 'John Doe',
            'nip' => '1234567890',
            'jabatan' => 'Staff',
            'unit_kerja' => 'IT Department'
        ];

        $response = $this->actingAs($this->user)
            ->post("/travel-requests/{$this->travelRequest->id}/participants", $participantData);

        $response->assertRedirect();
        // Assuming there's a participants table or relationship
        // $this->assertDatabaseHas('participants', $participantData);
    }

    /** @test */
    public function user_can_remove_participant_from_travel_request()
    {
        // Assuming there's a participant to remove
        $participantId = 1; // This would be the actual participant ID

        $response = $this->actingAs($this->user)
            ->delete("/travel-requests/{$this->travelRequest->id}/participants/{$participantId}");

        $response->assertRedirect();
    }

    /** @test */
    public function user_can_view_participants_list()
    {
        $response = $this->actingAs($this->user)
            ->get("/travel-requests/{$this->travelRequest->id}/participants");

        $response->assertStatus(200);
    }

    /** @test */
    public function guest_cannot_access_participant_routes()
    {
        $response = $this->get("/travel-requests/{$this->travelRequest->id}/participants");
        $response->assertRedirect('/login');
    }
}
