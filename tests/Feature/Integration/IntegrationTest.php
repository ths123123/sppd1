<?php

namespace Tests\Feature\Integration;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Document;
use App\Models\Approval;
use App\Models\Setting;
use App\Models\Notification;
use App\Models\ActivityLog;
use App\Models\TemplateDokumen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

class IntegrationTest extends TestCase
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
        
        // Disable mail and queue for testing
        Mail::fake();
        Queue::fake();
    }

    /** @test */
    public function complete_travel_request_workflow()
    {
        // 1. User creates travel request
        $travelRequestData = [
            'tujuan' => 'Jakarta',
            'keperluan' => 'Meeting dengan klien',
            'tanggal_berangkat' => '2024-02-01',
            'tanggal_kembali' => '2024-02-03',
            'transportasi' => 'Pesawat',
            'estimasi_biaya' => 2000000
        ];

        $response = $this->actingAs($this->user)
            ->post('/travel-requests', $travelRequestData);

        $response->assertRedirect('/travel-requests');
        
        $travelRequest = TravelRequest::where('user_id', $this->user->id)
            ->where('tujuan', 'Jakarta')
            ->first();
        
        $this->assertNotNull($travelRequest);
        $this->assertEquals('draft', $travelRequest->status);

        // 2. User submits travel request
        $response = $this->actingAs($this->user)
            ->put("/travel-requests/{$travelRequest->id}/submit");

        $response->assertRedirect('/travel-requests');
        
        $travelRequest->refresh();
        $this->assertEquals('submitted', $travelRequest->status);

        // 3. System creates approval record
        $approval = Approval::where('travel_request_id', $travelRequest->id)->first();
        $this->assertNotNull($approval);
        $this->assertEquals('pending', $approval->status);

        // 4. System sends notification to approver
        $notification = Notification::where('user_id', $this->approver->id)
            ->where('type', 'travel_request_submitted')
            ->first();
        $this->assertNotNull($notification);

        // 5. Approver reviews and approves
        $response = $this->actingAs($this->approver)
            ->put("/approvals/{$approval->id}", [
                'status' => 'approved',
                'catatan' => 'Disetujui'
            ]);

        $response->assertRedirect('/approvals');
        
        $approval->refresh();
        $this->assertEquals('approved', $approval->status);
        
        $travelRequest->refresh();
        $this->assertEquals('approved', $travelRequest->status);

        // 6. System sends notification to user
        $userNotification = Notification::where('user_id', $this->user->id)
            ->where('type', 'travel_request_approved')
            ->first();
        $this->assertNotNull($userNotification);

        // 7. System logs activity
        $activityLog = ActivityLog::where('user_id', $this->approver->id)
            ->where('action', 'approve_travel_request')
            ->first();
        $this->assertNotNull($activityLog);
    }

    /** @test */
    public function complete_document_management_workflow()
    {
        // 1. User uploads document
        $file = UploadedFile::fake()->create('document.pdf', 100);
        
        $response = $this->actingAs($this->user)
            ->post('/documents', [
                'nama_dokumen' => 'Surat Tugas',
                'jenis_dokumen' => 'surat_tugas',
                'deskripsi' => 'Surat tugas untuk perjalanan dinas',
                'file' => $file
            ]);

        $response->assertRedirect('/documents');
        
        $document = Document::where('user_id', $this->user->id)
            ->where('nama_dokumen', 'Surat Tugas')
            ->first();
        
        $this->assertNotNull($document);
        $this->assertEquals('surat_tugas', $document->jenis_dokumen);

        // 2. Admin verifies document
        $response = $this->actingAs($this->admin)
            ->put("/admin/documents/{$document->id}/verify");

        $response->assertRedirect('/admin/documents');
        
        $document->refresh();
        $this->assertTrue($document->is_verified);
        $this->assertNotNull($document->verified_at);
        $this->assertEquals($this->admin->id, $document->verified_by);

        // 3. System sends notification to user
        $notification = Notification::where('user_id', $this->user->id)
            ->where('type', 'document_verified')
            ->first();
        $this->assertNotNull($notification);

        // 4. System logs activity
        $activityLog = ActivityLog::where('user_id', $this->admin->id)
            ->where('action', 'verify_document')
            ->first();
        $this->assertNotNull($activityLog);
    }

    /** @test */
    public function complete_user_management_workflow()
    {
        // 1. Admin creates new user
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'user',
            'nip' => '123456789',
            'jabatan' => 'Staff'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/users', $userData);

        $response->assertRedirect('/admin/users');
        
        $newUser = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('user', $newUser->role);

        // 2. Admin updates user role
        $response = $this->actingAs($this->admin)
            ->put("/admin/users/{$newUser->id}", [
                'role' => 'approver'
            ]);

        $response->assertRedirect('/admin/users');
        
        $newUser->refresh();
        $this->assertEquals('approver', $newUser->role);

        // 3. System logs activity
        $activityLog = ActivityLog::where('user_id', $this->admin->id)
            ->where('action', 'update_user')
            ->first();
        $this->assertNotNull($activityLog);

        // 4. New user can login with new role
        $response = $this->post('/login', [
            'email' => 'newuser@example.com',
            'password' => 'password'
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($newUser);

        // 5. New user can access approver functionality
        $response = $this->actingAs($newUser)
            ->get('/approver/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function complete_reporting_workflow()
    {
        // 1. Create test data
        $travelRequests = TravelRequest::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'status' => 'approved'
        ]);

        $documents = Document::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'is_verified' => true
        ]);

        // 2. Admin generates report
        $response = $this->actingAs($this->admin)
            ->post('/admin/reports/generate', [
                'type' => 'travel_requests',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'format' => 'excel'
            ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // 3. System logs report generation
        $activityLog = ActivityLog::where('user_id', $this->admin->id)
            ->where('action', 'generate_report')
            ->first();
        $this->assertNotNull($activityLog);

        // 4. Admin exports data
        $response = $this->actingAs($this->admin)
            ->get('/admin/travel-requests/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function complete_notification_workflow()
    {
        // 1. System creates notification
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Notification',
            'message' => 'This is a test notification',
            'type' => 'info'
        ]);

        // 2. User views notifications
        $response = $this->actingAs($this->user)
            ->get('/notifications');

        $response->assertStatus(200);
        $response->assertSee($notification->title);

        // 3. User marks notification as read
        $response = $this->actingAs($this->user)
            ->put("/notifications/{$notification->id}/read");

        $response->assertRedirect('/notifications');
        
        $notification->refresh();
        $this->assertTrue($notification->is_read);
        $this->assertNotNull($notification->read_at);

        // 4. System logs activity
        $activityLog = ActivityLog::where('user_id', $this->user->id)
            ->where('action', 'read_notification')
            ->first();
        $this->assertNotNull($activityLog);
    }

    /** @test */
    public function complete_settings_management_workflow()
    {
        // 1. Admin views system settings
        $response = $this->actingAs($this->admin)
            ->get('/admin/settings');

        $response->assertStatus(200);

        // 2. Admin creates new setting
        $settingData = [
            'key' => 'maintenance_mode',
            'value' => 'false',
            'description' => 'System maintenance mode',
            'is_public' => true
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/settings', $settingData);

        $response->assertRedirect('/admin/settings');
        
        $setting = Setting::where('key', 'maintenance_mode')->first();
        $this->assertNotNull($setting);
        $this->assertEquals('false', $setting->value);

        // 3. Admin updates setting
        $response = $this->actingAs($this->admin)
            ->put("/admin/settings/{$setting->id}", [
                'value' => 'true'
            ]);

        $response->assertRedirect('/admin/settings');
        
        $setting->refresh();
        $this->assertEquals('true', $setting->value);

        // 4. System logs activity
        $activityLog = ActivityLog::where('user_id', $this->admin->id)
            ->where('action', 'update_setting')
            ->first();
        $this->assertNotNull($activityLog);
    }

    /** @test */
    public function complete_template_document_workflow()
    {
        // 1. Admin uploads template document
        $file = UploadedFile::fake()->create('template.docx', 100);
        
        $response = $this->actingAs($this->admin)
            ->post('/admin/template-dokumen', [
                'nama_template' => 'Template SPPD',
                'jenis_dokumen' => 'sppd',
                'deskripsi' => 'Template untuk Surat Perintah Perjalanan Dinas',
                'file' => $file,
                'is_active' => true
            ]);

        $response->assertRedirect('/admin/template-dokumen');
        
        $template = TemplateDokumen::where('nama_template', 'Template SPPD')->first();
        $this->assertNotNull($template);
        $this->assertEquals('sppd', $template->jenis_dokumen);

        // 2. User downloads template
        $response = $this->actingAs($this->user)
            ->get("/template-dokumen/{$template->id}/download");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        // 3. System logs download activity
        $activityLog = ActivityLog::where('user_id', $this->user->id)
            ->where('action', 'download_template')
            ->first();
        $this->assertNotNull($activityLog);
    }

    /** @test */
    public function complete_audit_log_workflow()
    {
        // 1. Perform various actions to generate logs
        $this->actingAs($this->user);
        
        // Create travel request
        $this->post('/travel-requests', [
            'tujuan' => 'Test Destination',
            'keperluan' => 'Test Purpose',
            'tanggal_berangkat' => '2024-02-01',
            'tanggal_kembali' => '2024-02-03',
            'transportasi' => 'Pesawat',
            'estimasi_biaya' => 2000000
        ]);

        // Update profile
        $this->put('/profile', [
            'name' => 'Updated Name'
        ]);

        // 2. Admin views audit logs
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit-trail');

        $response->assertStatus(200);

        // 3. Admin searches audit logs
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit-trail/search?user_id=' . $this->user->id);

        $response->assertStatus(200);

        // 4. Admin exports audit logs
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit-trail/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function complete_search_and_filter_workflow()
    {
        // 1. Create test data
        $travelRequests = TravelRequest::factory()->count(20)->create([
            'user_id' => $this->user->id
        ]);

        // 2. User searches travel requests
        $response = $this->actingAs($this->user)
            ->get('/travel-requests/search?q=Jakarta');

        $response->assertStatus(200);

        // 3. User filters by status
        $response = $this->actingAs($this->user)
            ->get('/travel-requests/search?status=pending');

        $response->assertStatus(200);

        // 4. User filters by date range
        $response = $this->actingAs($this->user)
            ->get('/travel-requests/search?start_date=2024-01-01&end_date=2024-12-31');

        $response->assertStatus(200);

        // 5. User uses multiple filters
        $response = $this->actingAs($this->user)
            ->get('/travel-requests/search?q=Jakarta&status=pending&transportasi=Pesawat');

        $response->assertStatus(200);
    }

    /** @test */
    public function complete_import_export_workflow()
    {
        // 1. Admin exports users
        $response = $this->actingAs($this->admin)
            ->get('/admin/users/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // 2. Admin imports users
        $file = UploadedFile::fake()->create('users.xlsx', 100);
        
        $response = $this->actingAs($this->admin)
            ->post('/admin/users/import', [
                'file' => $file
            ]);

        $response->assertRedirect('/admin/users');

        // 3. System logs import activity
        $activityLog = ActivityLog::where('user_id', $this->admin->id)
            ->where('action', 'import_users')
            ->first();
        $this->assertNotNull($activityLog);
    }

    /** @test */
    public function complete_backup_restore_workflow()
    {
        // 1. Admin creates backup
        $response = $this->actingAs($this->admin)
            ->post('/admin/backup');

        $response->assertRedirect('/admin/backup');
        $response->assertSessionHas('success');

        // 2. Admin views backup status
        $response = $this->actingAs($this->admin)
            ->get('/admin/backup');

        $response->assertStatus(200);

        // 3. System logs backup activity
        $activityLog = ActivityLog::where('user_id', $this->admin->id)
            ->where('action', 'create_backup')
            ->first();
        $this->assertNotNull($activityLog);
    }

    /** @test */
    public function complete_maintenance_workflow()
    {
        // 1. Admin enables maintenance mode
        $response = $this->actingAs($this->admin)
            ->post('/admin/maintenance/enable');

        $response->assertRedirect('/admin/maintenance');
        $response->assertSessionHas('success');

        // 2. System shows maintenance page
        $response = $this->get('/');
        $response->assertStatus(503);

        // 3. Admin disables maintenance mode
        $response = $this->actingAs($this->admin)
            ->post('/admin/maintenance/disable');

        $response->assertRedirect('/admin/maintenance');
        $response->assertSessionHas('success');

        // 4. System is accessible again
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /** @test */
    public function complete_error_handling_workflow()
    {
        // 1. Try to access non-existent resource
        $response = $this->actingAs($this->user)
            ->get('/travel-requests/99999');

        $response->assertStatus(404);

        // 2. Try to access unauthorized resource
        $otherUser = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $otherUser->id
        ]);

        $response = $this->actingAs($this->user)
            ->get("/travel-requests/{$travelRequest->id}");

        $response->assertStatus(403);

        // 3. Try to submit invalid data
        $response = $this->actingAs($this->user)
            ->post('/travel-requests', []);

        $response->assertSessionHasErrors();

        // 4. System handles errors gracefully
        $this->assertDatabaseMissing('travel_requests', [
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function complete_performance_workflow()
    {
        // 1. Create large dataset
        $users = User::factory()->count(100)->create();
        $travelRequests = TravelRequest::factory()->count(500)->create();

        // 2. Test search performance
        $startTime = microtime(true);
        
        $response = $this->actingAs($this->user)
            ->get('/travel-requests/search?q=Jakarta');
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(2.0, $executionTime); // Should complete within 2 seconds

        // 3. Test pagination performance
        $startTime = microtime(true);
        
        $response = $this->actingAs($this->user)
            ->get('/travel-requests?page=1&per_page=50');
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(1.0, $executionTime); // Should complete within 1 second

        // 4. Test export performance
        $startTime = microtime(true);
        
        $response = $this->actingAs($this->admin)
            ->get('/admin/travel-requests/export');
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(5.0, $executionTime); // Should complete within 5 seconds
    }
}
