<?php

namespace Tests\Feature\ApprovalWorkflow;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Approval;
use App\Models\Notification;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $kasubbag;
    protected $sekretaris;
    protected $ppk;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->kasubbag = User::factory()->create(['role' => 'kasubbag']);
        $this->sekretaris = User::factory()->create(['role' => 'sekretaris']);
        $this->ppk = User::factory()->create(['role' => 'ppk']);
        $this->regularUser = User::factory()->create(['role' => 'user']);
    }

    /** @test */
    public function kasubbag_cannot_access_approval_menu()
    {
        $this->actingAs($this->kasubbag);

        // Try to access approval menu
        $response = $this->get(route('approval.pimpinan.index'));

        // Should be forbidden
        $response->assertStatus(403);
        $response->assertSee('Anda tidak memiliki akses ke menu ini silahkan hubungi kasubbag');
    }

    /** @test */
    public function kasubbag_cannot_access_approval_ajax()
    {
        $this->actingAs($this->kasubbag);

        // Try to access approval AJAX endpoint
        $response = $this->get(route('approval.pimpinan.ajax'));

        // Should be forbidden
        $response->assertStatus(403);
        $response->assertSee('Anda tidak memiliki akses ke menu ini silahkan hubungi kasubbag');
    }

    /** @test */
    public function kasubbag_can_submit_sppd_for_approval()
    {
        $this->actingAs($this->kasubbag);

        // Create SPPD
        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
            'status' => 'draft',
        ]);

        // Submit for approval
        $response = $this->patch(route('travel-requests.update', $sppd->id), [
            'status' => 'submitted',
        ]);

        $response->assertRedirect();

        // Check SPPD status changed
        $sppd->refresh();
        $this->assertEquals('submitted', $sppd->status);

        // Check approval record was created
        $this->assertDatabaseHas('approvals', [
            'travel_request_id' => $sppd->id,
            'approver_role' => 'sekretaris',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function sekretaris_can_approve_sppd()
    {
        $this->actingAs($this->kasubbag);

        // Create and submit SPPD
        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
            'status' => 'draft',
        ]);

        $this->patch(route('travel-requests.update', $sppd->id), [
            'status' => 'submitted',
        ]);

        // Switch to sekretaris for approval
        $this->actingAs($this->sekretaris);

        // Check approval page shows SPPD
        $response = $this->get(route('approval.pimpinan.index'));
        $response->assertStatus(200);
        $response->assertSee($sppd->title);

        // Approve SPPD
        $response = $this->patch(route('approval.pimpinan.approve', $sppd->id), [
            'status' => 'approved',
            'notes' => 'Approved by sekretaris',
        ]);

        $response->assertRedirect();

        // Check SPPD status changed
        $sppd->refresh();
        $this->assertEquals('approved', $sppd->status);

        // Check approval record updated
        $this->assertDatabaseHas('approvals', [
            'travel_request_id' => $sppd->id,
            'approver_role' => 'sekretaris',
            'status' => 'approved',
            'notes' => 'Approved by sekretaris',
        ]);
    }

    /** @test */
    public function sekretaris_can_reject_sppd()
    {
        $this->actingAs($this->kasubbag);

        // Create and submit SPPD
        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
            'status' => 'draft',
        ]);

        $this->patch(route('travel-requests.update', $sppd->id), [
            'status' => 'submitted',
        ]);

        // Switch to sekretaris for rejection
        $this->actingAs($this->sekretaris);

        // Reject SPPD
        $response = $this->patch(route('approval.pimpinan.reject', $sppd->id), [
            'status' => 'rejected',
            'notes' => 'Rejected due to incomplete information',
        ]);

        $response->assertRedirect();

        // Check SPPD status changed
        $sppd->refresh();
        $this->assertEquals('rejected', $sppd->status);

        // Check approval record updated
        $this->assertDatabaseHas('approvals', [
            'travel_request_id' => $sppd->id,
            'approver_role' => 'sekretaris',
            'status' => 'rejected',
            'notes' => 'Rejected due to incomplete information',
        ]);
    }

    /** @test */
    public function ppk_can_approve_financial_aspects()
    {
        $this->actingAs($this->kasubbag);

        // Create and submit SPPD with budget
        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
            'status' => 'draft',
            'budget' => 5000000,
        ]);

        $this->patch(route('travel-requests.update', $sppd->id), [
            'status' => 'submitted',
        ]);

        // First approval by sekretaris
        $this->actingAs($this->sekretaris);
        $this->patch(route('approval.pimpinan.approve', $sppd->id), [
            'status' => 'approved',
            'notes' => 'Approved by sekretaris',
        ]);

        // Switch to PPK for financial approval
        $this->actingAs($this->ppk);

        // Check PPK approval page
        $response = $this->get(route('approval.pimpinan.index'));
        $response->assertStatus(200);
        $response->assertSee($sppd->title);

        // Approve financial aspect
        $response = $this->patch(route('approval.pimpinan.approve', $sppd->id), [
            'status' => 'approved',
            'notes' => 'Budget approved by PPK',
            'approval_type' => 'financial',
        ]);

        $response->assertRedirect();

        // Check final SPPD status
        $sppd->refresh();
        $this->assertEquals('approved', $sppd->status);

        // Check both approval records
        $this->assertDatabaseHas('approvals', [
            'travel_request_id' => $sppd->id,
            'approver_role' => 'sekretaris',
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('approvals', [
            'travel_request_id' => $sppd->id,
            'approver_role' => 'ppk',
            'status' => 'approved',
            'approval_type' => 'financial',
        ]);
    }

    /** @test */
    public function multi_level_approval_workflow_works()
    {
        $this->actingAs($this->kasubbag);

        // Create SPPD with high budget requiring multi-level approval
        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
            'status' => 'draft',
            'budget' => 10000000, // High budget requiring PPK approval
        ]);

        // Submit for approval
        $this->patch(route('travel-requests.update', $sppd->id), [
            'status' => 'submitted',
        ]);

        // First level: Sekretaris approval
        $this->actingAs($this->sekretaris);
        $this->patch(route('approval.pimpinan.approve', $sppd->id), [
            'status' => 'approved',
            'notes' => 'Approved by sekretaris',
        ]);

        // Check intermediate status
        $sppd->refresh();
        $this->assertEquals('pending_ppk', $sppd->status);

        // Second level: PPK approval
        $this->actingAs($this->ppk);
        $this->patch(route('approval.pimpinan.approve', $sppd->id), [
            'status' => 'approved',
            'notes' => 'Budget approved by PPK',
        ]);

        // Check final status
        $sppd->refresh();
        $this->assertEquals('approved', $sppd->status);
    }

    /** @test */
    public function approval_notifications_are_sent()
    {
        $this->actingAs($this->kasubbag);

        // Create and submit SPPD
        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
            'status' => 'draft',
        ]);

        $this->patch(route('travel-requests.update', $sppd->id), [
            'status' => 'submitted',
        ]);

        // Check notification was sent to sekretaris
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->sekretaris->id,
            'type' => 'sppd_submitted',
            'data->travel_request_id' => $sppd->id,
        ]);

        // Check notification was sent to kasubbag
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->kasubbag->id,
            'type' => 'sppd_submitted_confirmation',
            'data->travel_request_id' => $sppd->id,
        ]);
    }

    /** @test */
    public function approval_status_changes_trigger_notifications()
    {
        $this->actingAs($this->kasubbag);

        // Create and submit SPPD
        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
            'status' => 'draft',
        ]);

        $this->patch(route('travel-requests.update', $sppd->id), [
            'status' => 'submitted',
        ]);

        // Approve by sekretaris
        $this->actingAs($this->sekretaris);
        $this->patch(route('approval.pimpinan.approve', $sppd->id), [
            'status' => 'approved',
            'notes' => 'Approved by sekretaris',
        ]);

        // Check approval notification was sent to kasubbag
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->kasubbag->id,
            'type' => 'sppd_approved',
            'data->travel_request_id' => $sppd->id,
        ]);
    }

    /** @test */
    public function approval_history_is_tracked()
    {
        $this->actingAs($this->kasubbag);

        // Create and submit SPPD
        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
            'status' => 'draft',
        ]);

        $this->patch(route('travel-requests.update', $sppd->id), [
            'status' => 'submitted',
        ]);

        // Approve by sekretaris
        $this->actingAs($this->sekretaris);
        $this->patch(route('approval.pimpinan.approve', $sppd->id), [
            'status' => 'approved',
            'notes' => 'Approved by sekretaris',
        ]);

        // Check approval history
        $this->assertDatabaseHas('approvals', [
            'travel_request_id' => $sppd->id,
            'approver_role' => 'sekretaris',
            'status' => 'approved',
            'notes' => 'Approved by sekretaris',
        ]);

        // Check activity log
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->sekretaris->id,
            'action' => 'approved_sppd',
            'details->travel_request_id' => $sppd->id,
        ]);
    }

    /** @test */
    public function approval_deadlines_are_enforced()
    {
        $this->actingAs($this->kasubbag);

        // Create SPPD with deadline
        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
            'status' => 'draft',
            'deadline' => now()->addDays(3),
        ]);

        // Submit for approval
        $this->patch(route('travel-requests.update', $sppd->id), [
            'status' => 'submitted',
        ]);

        // Check deadline tracking
        $this->assertDatabaseHas('approvals', [
            'travel_request_id' => $sppd->id,
            'deadline' => now()->addDays(3),
        ]);

        // Simulate deadline passed
        $this->travel(5)->days();

        // Check if overdue notifications are sent
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->sekretaris->id,
            'type' => 'approval_overdue',
            'data->travel_request_id' => $sppd->id,
        ]);
    }

    /** @test */
    public function approval_escalation_works()
    {
        $this->actingAs($this->kasubbag);

        // Create SPPD
        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
            'status' => 'draft',
        ]);

        // Submit for approval
        $this->patch(route('travel-requests.update', $sppd->id), [
            'status' => 'submitted',
        ]);

        // Simulate sekretaris unavailable (escalation to admin)
        $this->actingAs($this->admin);

        // Check escalation notification
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->admin->id,
            'type' => 'approval_escalated',
            'data->travel_request_id' => $sppd->id,
        ]);

        // Admin can approve escalated SPPD
        $response = $this->patch(route('approval.pimpinan.approve', $sppd->id), [
            'status' => 'approved',
            'notes' => 'Approved by admin (escalated)',
        ]);

        $response->assertRedirect();

        // Check final status
        $sppd->refresh();
        $this->assertEquals('approved', $sppd->status);
    }

    /** @test */
    public function approval_delegation_works()
    {
        $this->actingAs($this->sekretaris);

        // Delegate approval authority to another user
        $delegate = User::factory()->create(['role' => 'sekretaris']);

        $response = $this->post(route('approval.delegate'), [
            'delegate_user_id' => $delegate->id,
            'delegation_type' => 'temporary',
            'start_date' => now(),
            'end_date' => now()->addDays(7),
            'reason' => 'Out of office',
        ]);

        $response->assertRedirect();

        // Check delegation was created
        $this->assertDatabaseHas('approval_delegations', [
            'delegator_id' => $this->sekretaris->id,
            'delegate_id' => $delegate->id,
            'status' => 'active',
        ]);

        // Delegate can now approve SPPD
        $this->actingAs($delegate);

        $sppd = TravelRequest::factory()->create([
            'status' => 'submitted',
        ]);

        $response = $this->patch(route('approval.pimpinan.approve', $sppd->id), [
            'status' => 'approved',
            'notes' => 'Approved by delegate',
        ]);

        $response->assertRedirect();

        // Check SPPD was approved
        $sppd->refresh();
        $this->assertEquals('approved', $sppd->status);
    }

    /** @test */
    public function approval_batch_operations_work()
    {
        $this->actingAs($this->sekretaris);

        // Create multiple SPPD for batch approval
        $sppds = TravelRequest::factory()->count(3)->create([
            'status' => 'submitted',
        ]);

        $sppdIds = $sppds->pluck('id')->toArray();

        // Batch approve
        $response = $this->post(route('approval.pimpinan.batch-approve'), [
            'sppd_ids' => $sppdIds,
            'status' => 'approved',
            'notes' => 'Batch approved by sekretaris',
        ]);

        $response->assertRedirect();

        // Check all SPPD were approved
        foreach ($sppdIds as $id) {
            $this->assertDatabaseHas('travel_requests', [
                'id' => $id,
                'status' => 'approved',
            ]);
        }

        // Check batch approval record
        $this->assertDatabaseHas('batch_approvals', [
            'approver_id' => $this->sekretaris->id,
            'count' => 3,
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function approval_conditions_are_enforced()
    {
        $this->actingAs($this->kasubbag);

        // Create SPPD with missing required documents
        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
            'status' => 'draft',
        ]);

        // Try to submit without required documents
        $response = $this->patch(route('travel-requests.update', $sppd->id), [
            'status' => 'submitted',
        ]);

        // Should fail due to missing documents
        $response->assertSessionHasErrors(['documents']);

        // Add required documents
        // ... document creation logic ...

        // Now submit should work
        $response = $this->patch(route('travel-requests.update', $sppd->id), [
            'status' => 'submitted',
        ]);

        $response->assertRedirect();

        // Check SPPD was submitted
        $sppd->refresh();
        $this->assertEquals('submitted', $sppd->status);
    }

    /** @test */
    public function approval_workflow_rules_are_respected()
    {
        $this->actingAs($this->kasubbag);

        // Create SPPD with budget exceeding limit
        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
            'status' => 'draft',
            'budget' => 50000000, // Exceeds limit requiring special approval
        ]);

        // Submit for approval
        $this->patch(route('travel-requests.update', $sppd->id), [
            'status' => 'submitted',
        ]);

        // Check special approval workflow was triggered
        $this->assertDatabaseHas('approvals', [
            'travel_request_id' => $sppd->id,
            'approver_role' => 'special_committee',
            'status' => 'pending',
        ]);

        // Regular sekretaris cannot approve high-budget SPPD
        $this->actingAs($this->sekretaris);
        $response = $this->patch(route('approval.pimpinan.approve', $sppd->id), [
            'status' => 'approved',
        ]);

        // Should fail
        $response->assertStatus(403);
    }

    /** @test */
    public function approval_audit_trail_is_complete()
    {
        $this->actingAs($this->kasubbag);

        // Create and submit SPPD
        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
            'status' => 'draft',
        ]);

        $this->patch(route('travel-requests.update', $sppd->id), [
            'status' => 'submitted',
        ]);

        // Approve by sekretaris
        $this->actingAs($this->sekretaris);
        $this->patch(route('approval.pimpinan.approve', $sppd->id), [
            'status' => 'approved',
            'notes' => 'Approved by sekretaris',
        ]);

        // Check complete audit trail
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->kasubbag->id,
            'action' => 'submitted_sppd',
            'details->travel_request_id' => $sppd->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->sekretaris->id,
            'action' => 'approved_sppd',
            'details->travel_request_id' => $sppd->id,
        ]);

        // Check approval timeline
        $this->assertDatabaseHas('approval_timelines', [
            'travel_request_id' => $sppd->id,
            'action' => 'submitted',
            'user_id' => $this->kasubbag->id,
        ]);

        $this->assertDatabaseHas('approval_timelines', [
            'travel_request_id' => $sppd->id,
            'action' => 'approved',
            'user_id' => $this->sekretaris->id,
        ]);
    }

    /** @test */
    public function approval_statistics_are_accurate()
    {
        $this->actingAs($this->admin);

        // Create SPPD with different statuses
        TravelRequest::factory()->count(5)->create(['status' => 'approved']);
        TravelRequest::factory()->count(3)->create(['status' => 'pending']);
        TravelRequest::factory()->count(2)->create(['status' => 'rejected']);

        // Check approval statistics
        $response = $this->get(route('approval.statistics'));
        $response->assertStatus(200);

        $response->assertSee('5'); // Approved count
        $response->assertSee('3'); // Pending count
        $response->assertSee('2'); // Rejected count
        $response->assertSee('10'); // Total count

        // Check approval rate
        $response->assertSee('50'); // 50% approval rate
    }

    /** @test */
    public function approval_export_functionality_works()
    {
        $this->actingAs($this->admin);

        // Create test data
        TravelRequest::factory()->count(5)->create(['status' => 'approved']);
        TravelRequest::factory()->count(3)->create(['status' => 'pending']);

        // Test PDF export
        $response = $this->get(route('approval.export.pdf'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        // Test Excel export
        $response = $this->get(route('approval.export.excel'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
