<?php

namespace Tests\Feature\Review;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ReviewTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $reviewer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->reviewer = User::factory()->approver()->create();
    }

    /** @test */
    public function reviewer_can_view_review_list()
    {
        $response = $this->actingAs($this->reviewer)
            ->get('/review');

        $response->assertStatus(200);
    }

    /** @test */
    public function reviewer_can_submit_review()
    {
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'submitted'
        ]);

        $reviewData = [
            'status' => 'approved',
            'catatan' => 'Dokumen lengkap dan sesuai prosedur'
        ];

        $response = $this->actingAs($this->reviewer)
            ->post("/review/{$travelRequest->id}", $reviewData);

        $response->assertRedirect();
        // Check if review was submitted
    }

    /** @test */
    public function reviewer_can_request_revision()
    {
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'submitted'
        ]);

        $reviewData = [
            'status' => 'revision_required',
            'catatan' => 'Mohon lengkapi dokumen pendukung'
        ];

        $response = $this->actingAs($this->reviewer)
            ->post("/review/{$travelRequest->id}", $reviewData);

        $response->assertRedirect();
        // Check if revision was requested
    }

    /** @test */
    public function regular_user_cannot_access_review_routes()
    {
        $response = $this->actingAs($this->user)
            ->get('/review');

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_review_routes()
    {
        $response = $this->get('/review');
        $response->assertRedirect('/login');
    }
}
