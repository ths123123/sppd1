<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Document;
use App\Models\Approval;
use App\Models\Setting;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class ApiTest extends TestCase
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
    }

    /** @test */
    public function user_can_login_via_api()
    {
        $credentials = [
            'email' => $this->user->email,
            'password' => 'password'
        ];

        $response = $this->postJson('/api/auth/login', $credentials);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user',
                    'token'
                ]
            ]);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        $credentials = [
            'email' => $this->user->email,
            'password' => 'wrongpassword'
        ];

        $response = $this->postJson('/api/auth/login', $credentials);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ]);
    }

    /** @test */
    public function user_can_logout_via_api()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Logged out successfully'
            ]);
    }

    /** @test */
    public function user_can_get_profile_via_api()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'nip',
                    'jabatan'
                ]
            ]);
    }

    /** @test */
    public function user_can_update_profile_via_api()
    {
        Sanctum::actingAs($this->user);

        $updateData = [
            'name' => 'Updated Name',
            'jabatan' => 'Senior Staff'
        ];

        $response = $this->putJson('/api/profile', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Profile updated successfully'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'jabatan' => 'Senior Staff'
        ]);
    }

    /** @test */
    public function user_can_get_travel_requests_via_api()
    {
        Sanctum::actingAs($this->user);

        TravelRequest::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->getJson('/api/travel-requests');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'tujuan',
                            'keperluan',
                            'status',
                            'created_at'
                        ]
                    ],
                    'current_page',
                    'total'
                ]
            ]);
    }

    /** @test */
    public function user_can_create_travel_request_via_api()
    {
        Sanctum::actingAs($this->user);

        $requestData = [
            'tujuan' => 'Jakarta',
            'keperluan' => 'Meeting dengan klien',
            'tanggal_berangkat' => '2024-02-01',
            'tanggal_kembali' => '2024-02-03',
            'transportasi' => 'Pesawat',
            'estimasi_biaya' => 2000000
        ];

        $response = $this->postJson('/api/travel-requests', $requestData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Travel request created successfully'
            ]);

        $this->assertDatabaseHas('travel_requests', [
            'user_id' => $this->user->id,
            'tujuan' => 'Jakarta',
            'keperluan' => 'Meeting dengan klien'
        ]);
    }

    /** @test */
    public function user_can_get_single_travel_request_via_api()
    {
        Sanctum::actingAs($this->user);

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->getJson("/api/travel-requests/{$travelRequest->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                    'tujuan',
                    'keperluan',
                    'status',
                    'created_at'
                ]
            ]);
    }

    /** @test */
    public function user_can_update_travel_request_via_api()
    {
        Sanctum::actingAs($this->user);

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'draft'
        ]);

        $updateData = [
            'tujuan' => 'Bandung',
            'keperluan' => 'Workshop'
        ];

        $response = $this->putJson("/api/travel-requests/{$travelRequest->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Travel request updated successfully'
            ]);

        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'tujuan' => 'Bandung',
            'keperluan' => 'Workshop'
        ]);
    }

    /** @test */
    public function user_can_delete_travel_request_via_api()
    {
        Sanctum::actingAs($this->user);

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'draft'
        ]);

        $response = $this->deleteJson("/api/travel-requests/{$travelRequest->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Travel request deleted successfully'
            ]);

        $this->assertDatabaseMissing('travel_requests', [
            'id' => $travelRequest->id
        ]);
    }

    /** @test */
    public function approver_can_get_pending_approvals_via_api()
    {
        Sanctum::actingAs($this->approver);

        Approval::factory()->count(3)->pending()->create([
            'approver_id' => $this->approver->id
        ]);

        $response = $this->getJson('/api/approvals/pending');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'travel_request',
                        'status',
                        'created_at'
                    ]
                ]
            ]);
    }

    /** @test */
    public function approver_can_approve_travel_request_via_api()
    {
        Sanctum::actingAs($this->approver);

        $approval = Approval::factory()->pending()->create([
            'approver_id' => $this->approver->id
        ]);

        $approvalData = [
            'status' => 'approved',
            'catatan' => 'Disetujui'
        ];

        $response = $this->putJson("/api/approvals/{$approval->id}", $approvalData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Approval updated successfully'
            ]);

        $this->assertDatabaseHas('approvals', [
            'id' => $approval->id,
            'status' => 'approved'
        ]);
    }

    /** @test */
    public function user_can_get_documents_via_api()
    {
        Sanctum::actingAs($this->user);

        Document::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->getJson('/api/documents');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'nama_dokumen',
                            'jenis_dokumen',
                            'file_path',
                            'created_at'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function user_can_upload_document_via_api()
    {
        Sanctum::actingAs($this->user);

        $documentData = [
            'nama_dokumen' => 'Test Document',
            'jenis_dokumen' => 'surat_tugas',
            'deskripsi' => 'Test document description'
        ];

        $response = $this->postJson('/api/documents', $documentData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Document uploaded successfully'
            ]);
    }

    /** @test */
    public function admin_can_get_all_users_via_api()
    {
        Sanctum::actingAs($this->admin);

        User::factory()->count(3)->create();

        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'role',
                            'nip',
                            'jabatan'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function admin_can_create_user_via_api()
    {
        Sanctum::actingAs($this->admin);

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'user',
            'nip' => '123456789',
            'jabatan' => 'Staff'
        ];

        $response = $this->postJson('/api/admin/users', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'User created successfully'
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => 'user'
        ]);
    }

    /** @test */
    public function admin_can_get_system_settings_via_api()
    {
        Sanctum::actingAs($this->admin);

        Setting::factory()->count(3)->create();

        $response = $this->getJson('/api/admin/settings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'key',
                        'value',
                        'description'
                    ]
                ]
            ]);
    }

    /** @test */
    public function admin_can_update_system_setting_via_api()
    {
        Sanctum::actingAs($this->admin);

        $setting = Setting::factory()->create();

        $updateData = [
            'value' => 'Updated Value',
            'description' => 'Updated description'
        ];

        $response = $this->putJson("/api/admin/settings/{$setting->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Setting updated successfully'
            ]);

        $this->assertDatabaseHas('settings', [
            'id' => $setting->id,
            'value' => 'Updated Value'
        ]);
    }

    /** @test */
    public function user_can_get_notifications_via_api()
    {
        Sanctum::actingAs($this->user);

        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'message',
                            'type',
                            'is_read',
                            'created_at'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function user_can_mark_notification_as_read_via_api()
    {
        Sanctum::actingAs($this->user);

        $notification = Notification::factory()->unread()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->putJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Notification marked as read'
            ]);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true
        ]);
    }

    /** @test */
    public function user_can_get_dashboard_data_via_api()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'total_travel_requests',
                    'pending_approvals',
                    'approved_requests',
                    'rejected_requests',
                    'recent_activities'
                ]
            ]);
    }

    /** @test */
    public function api_returns_validation_errors()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/travel-requests', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tujuan', 'keperluan', 'tanggal_berangkat']);
    }

    /** @test */
    public function api_returns_unauthorized_for_unauthenticated_requests()
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthenticated'
            ]);
    }

    /** @test */
    public function api_returns_forbidden_for_unauthorized_actions()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Forbidden'
            ]);
    }

    /** @test */
    public function api_supports_pagination()
    {
        Sanctum::actingAs($this->user);

        TravelRequest::factory()->count(25)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->getJson('/api/travel-requests?page=2&per_page=10');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'current_page' => 2,
                    'per_page' => 10,
                    'total' => 25
                ]
            ]);
    }
}
