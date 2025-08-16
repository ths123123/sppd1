<?php

namespace Tests\Feature\Workflow;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Approval;
use App\Models\Document;
use App\Models\Notification;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

class WorkflowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $approver;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->admin()->create();
        $this->approver = User::factory()->approver()->create();
        $this->user = User::factory()->create();
    }

    public function test_complete_travel_request_workflow()
    {
        // Step 1: User creates travel request
        $travelData = [
            'nomor_surat' => 'SPPD-2024-001',
            'tanggal_berangkat' => now()->addDays(7)->format('Y-m-d'),
            'tanggal_kembali' => now()->addDays(10)->format('Y-m-d'),
            'tujuan' => 'Jakarta',
            'keperluan' => 'Meeting dengan klien',
            'transportasi' => 'Pesawat',
            'estimasi_biaya' => 2500000,
            'catatan' => 'Perlu booking hotel'
        ];

        $response = $this->actingAs($this->user)
            ->post('/travel-requests', $travelData);

        $response->assertStatus(201);
        
        $travelRequest = TravelRequest::where('nomor_surat', 'SPPD-2024-001')->first();
        $this->assertNotNull($travelRequest);
        $this->assertEquals('submitted', $travelRequest->status);

        // Step 2: System creates approval record
        $this->assertDatabaseHas('approvals', [
            'travel_request_id' => $travelRequest->id,
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        // Step 3: System sends notification to approver
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->approver->id,
            'type' => 'travel_request_pending',
            'title' => 'Travel Request Pending Approval'
        ]);

        // Step 4: System logs the activity
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'action' => 'travel_request_created',
            'description' => "Travel request {$travelRequest->nomor_surat} created"
        ]);

        // Step 5: Approver reviews and approves
        $approvalData = [
            'status' => 'approved',
            'catatan' => 'Disetujui dengan catatan'
        ];

        $response = $this->actingAs($this->approver)
            ->put("/approvals/{$travelRequest->id}", $approvalData);

        $response->assertStatus(200);

        // Step 6: System updates travel request status
        $travelRequest->refresh();
        $this->assertEquals('approved', $travelRequest->status);
        $this->assertNotNull($travelRequest->approved_at);
        $this->assertEquals($this->approver->id, $travelRequest->approved_by);

        // Step 7: System sends notification to user
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'type' => 'travel_request_approved',
            'title' => 'Travel Request Approved'
        ]);

        // Step 8: System logs approval activity
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->approver->id,
            'action' => 'travel_request_approved',
            'description' => "Travel request {$travelRequest->nomor_surat} approved"
        ]);
    }

    public function test_travel_request_rejection_workflow()
    {
        // Create a travel request
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'submitted'
        ]);

        // Create approval record
        Approval::factory()->create([
            'travel_request_id' => $travelRequest->id,
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        // Approver rejects the request
        $rejectionData = [
            'status' => 'rejected',
            'catatan' => 'Ditolak karena budget tidak mencukupi'
        ];

        $response = $this->actingAs($this->approver)
            ->put("/approvals/{$travelRequest->id}", $rejectionData);

        $response->assertStatus(200);

        // Verify rejection workflow
        $travelRequest->refresh();
        $this->assertEquals('rejected', $travelRequest->status);
        $this->assertNotNull($travelRequest->rejected_at);
        $this->assertEquals($this->approver->id, $travelRequest->rejected_by);
        $this->assertEquals('Ditolak karena budget tidak mencukupi', $travelRequest->rejection_reason);

        // Verify notification sent
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'type' => 'travel_request_rejected',
            'title' => 'Travel Request Rejected'
        ]);

        // Verify activity logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->approver->id,
            'action' => 'travel_request_rejected',
            'description' => "Travel request {$travelRequest->nomor_surat} rejected"
        ]);
    }

    public function test_document_verification_workflow()
    {
        // User uploads document
        $documentData = [
            'nama_dokumen' => 'Surat Tugas.pdf',
            'jenis_dokumen' => 'surat_tugas',
            'deskripsi' => 'Surat tugas untuk perjalanan dinas',
            'file_path' => 'documents/surat_tugas.pdf',
            'file_size' => 1024000,
            'mime_type' => 'application/pdf'
        ];

        $response = $this->actingAs($this->user)
            ->post('/documents', $documentData);

        $response->assertStatus(201);

        $document = Document::where('nama_dokumen', 'Surat Tugas.pdf')->first();
        $this->assertNotNull($document);
        $this->assertEquals('pending', $document->status);

        // Admin verifies document
        $verificationData = [
            'status' => 'verified',
            'catatan' => 'Dokumen valid dan lengkap'
        ];

        $response = $this->actingAs($this->admin)
            ->put("/documents/{$document->id}/verify", $verificationData);

        $response->assertStatus(200);

        // Verify document status updated
        $document->refresh();
        $this->assertEquals('verified', $document->status);
        $this->assertNotNull($document->verified_at);
        $this->assertEquals($this->admin->id, $document->verified_by);

        // Verify notification sent
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'type' => 'document_verified',
            'title' => 'Document Verified'
        ]);

        // Verify activity logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'document_verified',
            'description' => "Document {$document->nama_dokumen} verified"
        ]);
    }

    public function test_user_role_change_workflow()
    {
        // Admin changes user role
        $roleChangeData = [
            'role' => 'approver'
        ];

        $response = $this->actingAs($this->admin)
            ->put("/users/{$this->user->id}/role", $roleChangeData);

        $response->assertStatus(200);

        // Verify role changed
        $this->user->refresh();
        $this->assertEquals('approver', $this->user->role);

        // Verify notification sent
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'type' => 'role_changed',
            'title' => 'Role Changed'
        ]);

        // Verify activity logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'user_role_changed',
            'description' => "User {$this->user->name} role changed to approver"
        ]);

        // Verify user can now access approver features
        $response = $this->actingAs($this->user)
            ->get('/approvals');

        $response->assertStatus(200);
    }

    public function test_bulk_approval_workflow()
    {
        // Create multiple travel requests
        $travelRequests = TravelRequest::factory()->count(3)->create([
            'status' => 'submitted'
        ]);

        // Create approval records
        foreach ($travelRequests as $request) {
            Approval::factory()->create([
                'travel_request_id' => $request->id,
                'user_id' => $request->user_id,
                'status' => 'pending'
            ]);
        }

        // Admin bulk approves
        $bulkData = [
            'travel_request_ids' => $travelRequests->pluck('id')->toArray(),
            'status' => 'approved',
            'catatan' => 'Bulk approval untuk efisiensi'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/approvals/bulk', $bulkData);

        $response->assertStatus(200);

        // Verify all requests approved
        foreach ($travelRequests as $request) {
            $request->refresh();
            $this->assertEquals('approved', $request->status);
            $this->assertNotNull($request->approved_at);
            $this->assertEquals($this->admin->id, $request->approved_by);
        }

        // Verify notifications sent
        $this->assertDatabaseCount('notifications', 3);

        // Verify activity logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'bulk_approval',
            'description' => 'Bulk approval for 3 travel requests'
        ]);
    }

    public function test_escalation_workflow()
    {
        // Create travel request
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'submitted',
            'estimasi_biaya' => 10000000 // High cost requiring escalation
        ]);

        // Create approval record
        Approval::factory()->create([
            'travel_request_id' => $travelRequest->id,
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        // System automatically escalates to admin due to high cost
        $escalationThreshold = Setting::factory()->create([
            'key' => 'approval_escalation_threshold',
            'value' => '5000000'
        ]);

        // Trigger escalation
        $response = $this->actingAs($this->admin)
            ->post("/approvals/{$travelRequest->id}/escalate");

        $response->assertStatus(200);

        // Verify escalation
        $this->assertDatabaseHas('approvals', [
            'travel_request_id' => $travelRequest->id,
            'escalated_to' => $this->admin->id,
            'escalated_at' => now()
        ]);

        // Verify notification sent to admin
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->admin->id,
            'type' => 'approval_escalated',
            'title' => 'Approval Escalated'
        ]);

        // Verify activity logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'approval_escalated',
            'description' => "Approval escalated for travel request {$travelRequest->nomor_surat}"
        ]);
    }

    public function test_auto_approval_workflow()
    {
        // Create low-cost travel request
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'submitted',
            'estimasi_biaya' => 500000 // Below auto-approval threshold
        ]);

        // Set auto-approval threshold
        Setting::factory()->create([
            'key' => 'auto_approval_threshold',
            'value' => '1000000'
        ]);

        // System auto-approves
        $response = $this->actingAs($this->user)
            ->post("/travel-requests/{$travelRequest->id}/auto-approve");

        $response->assertStatus(200);

        // Verify auto-approval
        $travelRequest->refresh();
        $this->assertEquals('approved', $travelRequest->status);
        $this->assertNotNull($travelRequest->approved_at);
        $this->assertEquals('system', $travelRequest->approved_by);

        // Verify notification sent
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'type' => 'travel_request_auto_approved',
            'title' => 'Travel Request Auto-Approved'
        ]);

        // Verify activity logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'action' => 'travel_request_auto_approved',
            'description' => "Travel request {$travelRequest->nomor_surat} auto-approved"
        ]);
    }

    public function test_workflow_with_conditions()
    {
        // Create travel request with specific conditions
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'submitted',
            'transportasi' => 'Pesawat',
            'tanggal_berangkat' => now()->addDays(1), // Tomorrow
            'estimasi_biaya' => 3000000
        ]);

        // Set workflow conditions
        $conditions = [
            'urgent_travel_approval' => 'immediate',
            'air_travel_approval' => 'supervisor',
            'high_cost_approval' => 'manager'
        ];

        foreach ($conditions as $key => $value) {
            Setting::factory()->create([
                'key' => $key,
                'value' => $value
            ]);
        }

        // Apply workflow conditions
        $response = $this->actingAs($this->admin)
            ->post("/travel-requests/{$travelRequest->id}/apply-workflow");

        $response->assertStatus(200);

        // Verify workflow applied
        $this->assertDatabaseHas('approvals', [
            'travel_request_id' => $travelRequest->id,
            'priority' => 'high',
            'approval_level' => 'manager'
        ]);

        // Verify notification sent
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'type' => 'workflow_applied',
            'title' => 'Workflow Applied'
        ]);
    }

    public function test_parallel_approval_workflow()
    {
        // Create travel request requiring multiple approvals
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'submitted',
            'estimasi_biaya' => 15000000
        ]);

        // Create multiple approval records
        $approvers = User::factory()->approver()->count(3)->create();
        
        foreach ($approvers as $approver) {
            Approval::factory()->create([
                'travel_request_id' => $travelRequest->id,
                'user_id' => $this->user->id,
                'approver_id' => $approver->id,
                'status' => 'pending',
                'approval_order' => 1 // Parallel approval
            ]);
        }

        // All approvers approve
        foreach ($approvers as $approver) {
            $this->actingAs($approver)
                ->put("/approvals/{$travelRequest->id}", [
                    'status' => 'approved',
                    'catatan' => 'Disetujui'
                ]);
        }

        // Verify all approvals received
        $this->assertDatabaseCount('approvals', 3);
        $this->assertDatabaseMissing('approvals', ['status' => 'pending']);

        // Verify travel request status updated
        $travelRequest->refresh();
        $this->assertEquals('approved', $travelRequest->status);

        // Verify notifications sent
        $this->assertDatabaseCount('notifications', 4); // 3 approvals + 1 final approval

        // Verify activity logged
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'parallel_approval_completed',
            'description' => "Parallel approval completed for travel request {$travelRequest->nomor_surat}"
        ]);
    }

    public function test_workflow_timeout_handling()
    {
        // Create travel request
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'submitted',
            'created_at' => now()->subDays(5) // 5 days old
        ]);

        // Create approval record
        Approval::factory()->create([
            'travel_request_id' => $travelRequest->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'created_at' => now()->subDays(5)
        ]);

        // Set timeout threshold
        Setting::factory()->create([
            'key' => 'approval_timeout_days',
            'value' => '3'
        ]);

        // System handles timeout
        $response = $this->actingAs($this->admin)
            ->post("/approvals/{$travelRequest->id}/handle-timeout");

        $response->assertStatus(200);

        // Verify timeout handled
        $this->assertDatabaseHas('approvals', [
            'travel_request_id' => $travelRequest->id,
            'status' => 'timeout',
            'timeout_at' => now()
        ]);

        // Verify notification sent
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'type' => 'approval_timeout',
            'title' => 'Approval Timeout'
        ]);

        // Verify activity logged
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'approval_timeout',
            'description' => "Approval timeout for travel request {$travelRequest->nomor_surat}"
        ]);
    }

    public function test_workflow_rollback()
    {
        // Create approved travel request
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $this->approver->id
        ]);

        // Admin rolls back approval
        $rollbackData = [
            'reason' => 'Data tidak lengkap',
            'rollback_to' => 'submitted'
        ];

        $response = $this->actingAs($this->admin)
            ->post("/travel-requests/{$travelRequest->id}/rollback", $rollbackData);

        $response->assertStatus(200);

        // Verify rollback
        $travelRequest->refresh();
        $this->assertEquals('submitted', $travelRequest->status);
        $this->assertNull($travelRequest->approved_at);
        $this->assertNull($travelRequest->approved_by);

        // Verify approval record updated
        $this->assertDatabaseHas('approvals', [
            'travel_request_id' => $travelRequest->id,
            'status' => 'pending'
        ]);

        // Verify notification sent
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'type' => 'approval_rollback',
            'title' => 'Approval Rolled Back'
        ]);

        // Verify activity logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'approval_rollback',
            'description' => "Approval rolled back for travel request {$travelRequest->nomor_surat}"
        ]);
    }
}
