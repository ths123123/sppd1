<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TravelRequestTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function travel_request_can_be_created()
    {
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'tujuan' => 'Jakarta',
            'keperluan' => 'Meeting dengan klien'
        ]);

        $this->assertDatabaseHas('travel_requests', [
            'user_id' => $this->user->id,
            'tujuan' => 'Jakarta',
            'keperluan' => 'Meeting dengan klien'
        ]);
    }

    /** @test */
    public function travel_request_has_default_status()
    {
        $travelRequest = TravelRequest::factory()->create();

        $this->assertEquals('in_review', $travelRequest->status);
    }

    /** @test */
    public function travel_request_can_be_submitted()
    {
        $travelRequest = TravelRequest::factory()->create([
            'status' => 'in_review',
            'submitted_at' => now()
        ]);

        $this->assertNotNull($travelRequest->submitted_at);
        $this->assertEquals('in_review', $travelRequest->status);
    }

    /** @test */
    public function travel_request_can_be_completed()
    {
        $travelRequest = TravelRequest::factory()->completed()->create();

        $this->assertEquals('completed', $travelRequest->status);
        $this->assertNotNull($travelRequest->approved_at);
    }

    /** @test */
    public function travel_request_can_be_rejected()
    {
        $travelRequest = TravelRequest::factory()->rejected()->create();

        $this->assertEquals('rejected', $travelRequest->status);
        $this->assertNotNull($travelRequest->catatan_approval);
    }

    /** @test */
    public function travel_request_belongs_to_user()
    {
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertEquals($this->user->id, $travelRequest->user_id);
        $this->assertInstanceOf(User::class, $travelRequest->user);
    }

    /** @test */
    public function travel_request_can_calculate_total_biaya()
    {
        $travelRequest = TravelRequest::factory()->create([
            'biaya_transport' => 1000000,
            'biaya_penginapan' => 500000,
            'uang_harian' => 300000,
            'biaya_lainnya' => 200000
        ]);

        $expectedTotal = 1000000 + 500000 + 300000 + 200000;
        $this->assertEquals($expectedTotal, $travelRequest->total_biaya);
    }

    /** @test */
    public function travel_request_can_have_urgent_flag()
    {
        $travelRequest = TravelRequest::factory()->urgent()->create();

        $this->assertTrue($travelRequest->is_urgent);
    }

    /** @test */
    public function travel_request_can_get_status_label()
    {
        $travelRequest = TravelRequest::factory()->create(['status' => 'in_review']);
        $this->assertEquals('Sedang Direview', $travelRequest->status_label);

        $travelRequest = TravelRequest::factory()->create(['status' => 'completed']);
        $this->assertEquals('Disetujui', $travelRequest->status_label);
    }
}
