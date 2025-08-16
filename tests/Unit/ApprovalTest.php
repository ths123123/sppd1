<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Approval;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $approver;
    protected $travelRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->approver = User::factory()->create(['role' => 'ppk']);
        $this->travelRequest = TravelRequest::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function approval_can_be_created()
    {
        $approval = Approval::factory()->create([
            'travel_request_id' => $this->travelRequest->id,
            'approver_id' => $this->approver->id,
            'level' => 3,
            'role' => 'ppk'
        ]);

        $this->assertDatabaseHas('approvals', [
            'travel_request_id' => $this->travelRequest->id,
            'approver_id' => $this->approver->id,
            'level' => 3,
            'role' => 'ppk'
        ]);
    }

    /** @test */
    public function approval_has_default_status()
    {
        $approval = Approval::factory()->create();

        $this->assertEquals('pending', $approval->status);
    }

    /** @test */
    public function approval_can_be_approved()
    {
        $approval = Approval::factory()->create([
            'status' => 'pending'
        ]);

        $approval->update([
            'status' => 'approved',
            'approved_at' => now()
        ]);

        $this->assertEquals('approved', $approval->fresh()->status);
        $this->assertNotNull($approval->fresh()->approved_at);
    }

    /** @test */
    public function approval_can_be_rejected()
    {
        $approval = Approval::factory()->create([
            'status' => 'pending'
        ]);

        $approval->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'comments' => 'Ditolak karena alasan tertentu'
        ]);

        $this->assertEquals('rejected', $approval->fresh()->status);
        $this->assertNotNull($approval->fresh()->rejected_at);
        $this->assertEquals('Ditolak karena alasan tertentu', $approval->fresh()->comments);
    }

    /** @test */
    public function approval_belongs_to_travel_request()
    {
        $approval = Approval::factory()->create([
            'travel_request_id' => $this->travelRequest->id
        ]);

        $this->assertEquals($this->travelRequest->id, $approval->travel_request_id);
        $this->assertInstanceOf(TravelRequest::class, $approval->travelRequest);
    }

    /** @test */
    public function approval_belongs_to_approver()
    {
        $approval = Approval::factory()->create([
            'approver_id' => $this->approver->id
        ]);

        $this->assertEquals($this->approver->id, $approval->approver_id);
        $this->assertInstanceOf(User::class, $approval->approver);
    }

    /** @test */
    public function approval_can_have_different_levels()
    {
        $kasubbagApproval = Approval::factory()->kasubbag()->create();
        $this->assertEquals(1, $kasubbagApproval->level);
        $this->assertEquals('kasubbag', $kasubbagApproval->role);

        $sekretarisApproval = Approval::factory()->sekretaris()->create();
        $this->assertEquals(2, $sekretarisApproval->level);
        $this->assertEquals('sekretaris', $sekretarisApproval->role);

        $ppkApproval = Approval::factory()->ppk()->create();
        $this->assertEquals(3, $ppkApproval->level);
        $this->assertEquals('ppk', $ppkApproval->role);
    }

    /** @test */
    public function approval_can_have_revision_notes()
    {
        $revisionNotes = [
            'biaya_transport' => 'Biaya transport terlalu tinggi',
            'tanggal_perjalanan' => 'Tanggal perlu disesuaikan'
        ];

        $approval = Approval::factory()->create([
            'revision_notes' => $revisionNotes
        ]);

        $this->assertEquals($revisionNotes, $approval->revision_notes);
    }
}
