<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Setting;
use App\Models\TravelRequest;
use App\Models\Approval;
use App\Models\Document;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $user;
    protected $approver;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::factory()->admin()->create();
        
        // Create regular user
        $this->user = User::factory()->create();
        
        // Create approver user
        $this->approver = User::factory()->approver()->create();
    }

    /** @test */
    public function admin_can_access_admin_dashboard()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    /** @test */
    public function non_admin_users_cannot_access_admin_dashboard()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_view_all_users()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/users');

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
    }

    /** @test */
    public function admin_can_create_new_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'user',
            'nip' => '123456789',
            'jabatan' => 'Staff'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/users', $userData);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user'
        ]);
    }

    /** @test */
    public function admin_can_edit_user()
    {
        $userData = [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'role' => 'approver',
            'nip' => '987654321',
            'jabatan' => 'Manager'
        ];

        $response = $this->actingAs($this->admin)
            ->put("/admin/users/{$this->user->id}", $userData);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated User',
            'role' => 'approver'
        ]);
    }

    /** @test */
    public function admin_can_delete_user()
    {
        $response = $this->actingAs($this->admin)
            ->delete("/admin/users/{$this->user->id}");

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseMissing('users', ['id' => $this->user->id]);
    }

    /** @test */
    public function admin_can_view_system_settings()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/settings');

        $response->assertStatus(200);
        $response->assertViewIs('admin.settings.index');
    }

    /** @test */
    public function admin_can_update_system_settings()
    {
        $setting = Setting::factory()->create();

        $settingData = [
            'value' => 'Updated System Name',
            'description' => 'Updated description'
        ];

        $response = $this->actingAs($this->admin)
            ->put("/admin/settings/{$setting->id}", $settingData);

        $response->assertRedirect('/admin/settings');
        $this->assertDatabaseHas('settings', [
            'id' => $setting->id,
            'value' => 'Updated System Name'
        ]);
    }

    /** @test */
    public function admin_can_view_system_logs()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/logs');

        $response->assertStatus(200);
        $response->assertViewIs('admin.logs.index');
    }

    /** @test */
    public function admin_can_export_system_logs()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/logs/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function admin_can_view_system_statistics()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/statistics');

        $response->assertStatus(200);
        $response->assertViewIs('admin.statistics.index');
    }

    /** @test */
    public function admin_can_manage_user_roles()
    {
        $roleData = [
            'role' => 'approver'
        ];

        $response = $this->actingAs($this->admin)
            ->put("/admin/users/{$this->user->id}/role", $roleData);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'role' => 'approver'
        ]);
    }

    /** @test */
    public function admin_can_view_all_travel_requests()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/travel-requests');

        $response->assertStatus(200);
        $response->assertViewIs('admin.travel-requests.index');
    }

    /** @test */
    public function admin_can_override_approval_decisions()
    {
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'rejected'
        ]);

        $approvalData = [
            'status' => 'approved',
            'catatan' => 'Admin override'
        ];

        $response = $this->actingAs($this->admin)
            ->put("/admin/travel-requests/{$travelRequest->id}/approve", $approvalData);

        $response->assertRedirect('/admin/travel-requests');
        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'approved'
        ]);
    }

    /** @test */
    public function admin_can_bulk_approve_travel_requests()
    {
        $travelRequests = TravelRequest::factory()->count(3)->create([
            'status' => 'pending'
        ]);

        $requestIds = $travelRequests->pluck('id')->toArray();

        $response = $this->actingAs($this->admin)
            ->post('/admin/travel-requests/bulk-approve', [
                'request_ids' => $requestIds
            ]);

        $response->assertRedirect('/admin/travel-requests');
        
        foreach ($travelRequests as $request) {
            $this->assertDatabaseHas('travel_requests', [
                'id' => $request->id,
                'status' => 'approved'
            ]);
        }
    }

    /** @test */
    public function admin_can_view_system_backup_status()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/backup');

        $response->assertStatus(200);
        $response->assertViewIs('admin.backup.index');
    }

    /** @test */
    public function admin_can_create_system_backup()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/backup');

        $response->assertRedirect('/admin/backup');
        $response->assertSessionHas('success');
    }

    /** @test */
    public function admin_can_view_audit_trail()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audit-trail');

        $response->assertStatus(200);
        $response->assertViewIs('admin.audit-trail.index');
    }

    /** @test */
    public function admin_can_clear_old_logs()
    {
        $response = $this->actingAs($this->admin)
            ->delete('/admin/logs/clear');

        $response->assertRedirect('/admin/logs');
        $response->assertSessionHas('success');
    }

    /** @test */
    public function admin_can_view_database_statistics()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/database/stats');

        $response->assertStatus(200);
        $response->assertViewIs('admin.database.stats');
    }

    /** @test */
    public function admin_can_optimize_database()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/database/optimize');

        $response->assertRedirect('/admin/database/stats');
        $response->assertSessionHas('success');
    }
}
