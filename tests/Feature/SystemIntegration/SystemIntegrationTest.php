<?php

namespace Tests\Feature\SystemIntegration;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Document;
use App\Models\Approval;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class SystemIntegrationTest extends TestCase
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
        
        // Create users with different roles
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->kasubbag = User::factory()->create(['role' => 'kasubbag']);
        $this->sekretaris = User::factory()->create(['role' => 'sekretaris']);
        $this->ppk = User::factory()->create(['role' => 'ppk']);
        $this->regularUser = User::factory()->create(['role' => 'user']);
        
        // Create basic settings
        Setting::create(['key' => 'system_name', 'value' => 'SPPD KPU Cirebon']);
        Setting::create(['key' => 'system_version', 'value' => '1.0.0']);
    }

    /** @test */
    public function admin_can_access_all_navbar_features()
    {
        $this->actingAs($this->admin);

        // Dashboard
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // SPPD Management
        $response = $this->get(route('travel-requests.index'));
        $response->assertStatus(200);

        $response = $this->get(route('travel-requests.create'));
        $response->assertStatus(200);

        // Analytics & Reports
        $response = $this->get(route('analytics.index'));
        $response->assertStatus(200);

        $response = $this->get(route('laporan.daftar'));
        $response->assertStatus(200);

        // Document Management
        $response = $this->get(route('documents.index'));
        $response->assertStatus(200);

        $response = $this->get(route('templates.index'));
        $response->assertStatus(200);

        // User Management
        $response = $this->get(route('users.index'));
        $response->assertStatus(200);

        // Settings
        $response = $this->get(route('settings.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function kasubbag_can_access_appropriate_features()
    {
        $this->actingAs($this->kasubbag);

        // Dashboard
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // SPPD Creation
        $response = $this->get(route('travel-requests.create'));
        $response->assertStatus(200);

        // SPPD List
        $response = $this->get(route('travel-requests.index'));
        $response->assertStatus(200);

        // Analytics & Reports
        $response = $this->get(route('analytics.index'));
        $response->assertStatus(200);

        $response = $this->get(route('laporan.daftar'));
        $response->assertStatus(200);

        // Document Management
        $response = $this->get(route('documents.index'));
        $response->assertStatus(200);

        $response = $this->get(route('templates.index'));
        $response->assertStatus(200);

        // User Management
        $response = $this->get(route('users.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function sekretaris_can_access_approval_and_management_features()
    {
        $this->actingAs($this->sekretaris);

        // Dashboard
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Approval
        $response = $this->get(route('approval.pimpinan.index'));
        $response->assertStatus(200);

        // SPPD List
        $response = $this->get(route('travel-requests.index'));
        $response->assertStatus(200);

        // Analytics & Reports
        $response = $this->get(route('analytics.index'));
        $response->assertStatus(200);

        $response = $this->get(route('laporan.daftar'));
        $response->assertStatus(200);

        // Document Management
        $response = $this->get(route('documents.index'));
        $response->assertStatus(200);

        $response = $this->get(route('templates.index'));
        $response->assertStatus(200);

        // User Management
        $response = $this->get(route('users.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function ppk_can_access_approval_and_financial_features()
    {
        $this->actingAs($this->ppk);

        // Dashboard
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Approval
        $response = $this->get(route('approval.pimpinan.index'));
        $response->assertStatus(200);

        // SPPD List
        $response = $this->get(route('travel-requests.index'));
        $response->assertStatus(200);

        // Analytics & Reports
        $response = $this->get(route('analytics.index'));
        $response->assertStatus(200);

        $response = $this->get(route('laporan.daftar'));
        $response->assertStatus(200);

        // Document Management
        $response = $this->get(route('documents.index'));
        $response->assertStatus(200);

        $response = $this->get(route('templates.index'));
        $response->assertStatus(200);

        // User Management
        $response = $this->get(route('users.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function regular_user_can_access_basic_features()
    {
        $this->actingAs($this->regularUser);

        // Dashboard
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // My SPPD
        $response = $this->get(route('my-travel-requests.index'));
        $response->assertStatus(200);

        // My Documents
        $response = $this->get(route('documents.index'));
        $response->assertStatus(200);

        // Profile
        $response = $this->get(route('profile.show'));
        $response->assertStatus(200);
    }

    /** @test */
    public function complete_sppd_workflow_integration()
    {
        $this->actingAs($this->kasubbag);

        // 1. Create SPPD
        $sppdData = [
            'title' => 'Test SPPD',
            'destination' => 'Jakarta',
            'purpose' => 'Meeting',
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
            'budget' => 5000000,
            'participants' => [$this->regularUser->id],
        ];

        $response = $this->post(route('travel-requests.store'), $sppdData);
        $response->assertRedirect();

        $sppd = TravelRequest::where('title', 'Test SPPD')->first();
        $this->assertNotNull($sppd);

        // 2. Upload Document
        Storage::fake('documents');
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->post(route('documents.store'), [
            'travel_request_id' => $sppd->id,
            'document' => $file,
            'type' => 'surat_tugas',
        ]);

        $this->assertDatabaseHas('documents', [
            'travel_request_id' => $sppd->id,
            'type' => 'surat_tugas',
        ]);

        // 3. Submit for Approval
        $response = $this->patch(route('travel-requests.update', $sppd->id), [
            'status' => 'submitted',
        ]);

        // 4. Check Approval Process
        $this->actingAs($this->sekretaris);
        $response = $this->get(route('approval.pimpinan.index'));
        $response->assertStatus(200);
        $response->assertSee('Test SPPD');

        // 5. Approve SPPD
        $response = $this->patch(route('approval.pimpinan.approve', $sppd->id), [
            'status' => 'approved',
            'notes' => 'Approved by sekretaris',
        ]);

        // 6. Check Final Status
        $sppd->refresh();
        $this->assertEquals('approved', $sppd->status);
    }

    /** @test */
    public function notification_system_integration()
    {
        $this->actingAs($this->kasubbag);

        // Create SPPD
        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
            'status' => 'submitted',
        ]);

        // Check if notification was created
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->sekretaris->id,
            'type' => 'sppd_submitted',
        ]);

        // Check notification count
        $this->actingAs($this->sekretaris);
        $response = $this->get(route('notifications.index'));
        $response->assertStatus(200);
        $response->assertSee('1'); // Notification count
    }

    /** @test */
    public function analytics_and_reporting_integration()
    {
        $this->actingAs($this->admin);

        // Create some test data
        TravelRequest::factory()->count(5)->create(['status' => 'approved']);
        TravelRequest::factory()->count(3)->create(['status' => 'pending']);
        TravelRequest::factory()->count(2)->create(['status' => 'rejected']);

        // Test Analytics Dashboard
        $response = $this->get(route('analytics.index'));
        $response->assertStatus(200);
        $response->assertSee('5'); // Approved count
        $response->assertSee('3'); // Pending count
        $response->assertSee('2'); // Rejected count

        // Test Reports
        $response = $this->get(route('laporan.daftar'));
        $response->assertStatus(200);
    }

    /** @test */
    public function document_management_integration()
    {
        $this->actingAs($this->kasubbag);

        // Create SPPD
        $sppd = TravelRequest::factory()->create([
            'user_id' => $this->kasubbag->id,
        ]);

        // Upload multiple documents
        Storage::fake('documents');
        
        $documents = [
            UploadedFile::fake()->create('surat_tugas.pdf', 100),
            UploadedFile::fake()->create('laporan.pdf', 150),
        ];

        foreach ($documents as $document) {
            $response = $this->post(route('documents.store'), [
                'travel_request_id' => $sppd->id,
                'document' => $document,
                'type' => 'other',
            ]);
        }

        // Check documents were created
        $this->assertDatabaseCount('documents', 2);

        // Test document listing
        $response = $this->get(route('documents.index'));
        $response->assertStatus(200);
        $response->assertSee('surat_tugas.pdf');
        $response->assertSee('laporan.pdf');
    }

    /** @test */
    public function user_management_integration()
    {
        $this->actingAs($this->admin);

        // Create new user
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post(route('users.store'), $userData);
        $response->assertRedirect();

        // Check user was created
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
        ]);

        // Test user listing
        $response = $this->get(route('users.index'));
        $response->assertStatus(200);
        $response->assertSee('Test User');
    }

    /** @test */
    public function settings_and_configuration_integration()
    {
        $this->actingAs($this->admin);

        // Update system settings
        $response = $this->patch(route('settings.update'), [
            'system_name' => 'SPPD KPU Cirebon Updated',
            'system_version' => '2.0.0',
        ]);

        // Check settings were updated
        $this->assertDatabaseHas('settings', [
            'key' => 'system_name',
            'value' => 'SPPD KPU Cirebon Updated',
        ]);

        // Test settings page
        $response = $this->get(route('settings.index'));
        $response->assertStatus(200);
        $response->assertSee('SPPD KPU Cirebon Updated');
    }

    /** @test */
    public function profile_management_integration()
    {
        $this->actingAs($this->regularUser);

        // Update profile
        $response = $this->patch(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        // Check profile was updated
        $this->assertDatabaseHas('users', [
            'id' => $this->regularUser->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        // Test profile page
        $response = $this->get(route('profile.show'));
        $response->assertStatus(200);
        $response->assertSee('Updated Name');
    }

    /** @test */
    public function search_and_filter_integration()
    {
        $this->actingAs($this->admin);

        // Create test data
        TravelRequest::factory()->create(['title' => 'Jakarta Meeting']);
        TravelRequest::factory()->create(['title' => 'Bandung Conference']);
        TravelRequest::factory()->create(['title' => 'Surabaya Workshop']);

        // Test search functionality
        $response = $this->get(route('travel-requests.index', ['search' => 'Jakarta']));
        $response->assertStatus(200);
        $response->assertSee('Jakarta Meeting');
        $response->assertDontSee('Bandung Conference');
        $response->assertDontSee('Surabaya Workshop');

        // Test filter functionality
        $response = $this->get(route('travel-requests.index', ['status' => 'pending']));
        $response->assertStatus(200);
    }

    /** @test */
    public function export_functionality_integration()
    {
        $this->actingAs($this->admin);

        // Create test data
        TravelRequest::factory()->count(3)->create();

        // Test PDF export
        $response = $this->get(route('laporan.export.pdf'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        // Test Excel export
        $response = $this->get(route('laporan.export.excel'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function mobile_responsiveness_integration()
    {
        $this->actingAs($this->regularUser);

        // Test mobile menu accessibility
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('mobile-menu'); // Check mobile menu component exists

        // Test responsive navigation
        $response = $this->get(route('travel-requests.create'));
        $response->assertStatus(200);
        $response->assertSee('mobile-offcanvas'); // Check mobile offcanvas exists
    }

    /** @test */
    public function error_handling_and_validation_integration()
    {
        $this->actingAs($this->kasubbag);

        // Test validation errors
        $response = $this->post(route('travel-requests.store'), []);
        $response->assertSessionHasErrors(['title', 'destination', 'purpose']);

        // Test unauthorized access
        $this->actingAs($this->regularUser);
        $response = $this->get(route('users.index'));
        $response->assertStatus(403);

        // Test not found pages
        $response = $this->get('/nonexistent-page');
        $response->assertStatus(404);
    }

    /** @test */
    public function audit_logging_integration()
    {
        $this->actingAs($this->admin);

        // Perform some actions
        $this->get(route('dashboard'));
        $this->get(route('users.index'));
        $this->get(route('settings.index'));

        // Check audit logs were created
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'viewed_dashboard',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'viewed_users',
        ]);
    }

    /** @test */
    public function performance_and_caching_integration()
    {
        $this->actingAs($this->admin);

        // First request
        $startTime = microtime(true);
        $response = $this->get(route('dashboard'));
        $firstRequestTime = microtime(true) - $startTime;

        // Second request (should be faster due to caching)
        $startTime = microtime(true);
        $response = $this->get(route('dashboard'));
        $secondRequestTime = microtime(true) - $startTime;

        // Second request should be faster
        $this->assertLessThan($firstRequestTime, $secondRequestTime);
    }
}
