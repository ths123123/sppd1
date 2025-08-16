<?php

namespace Tests\Feature\Search;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Document;
use App\Models\Approval;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class SearchTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->admin = User::factory()->admin()->create();
    }

    /** @test */
    public function user_can_search_travel_requests()
    {
        // Create travel requests with different data
        $request1 = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'tujuan' => 'Jakarta',
            'keperluan' => 'Meeting dengan klien'
        ]);

        $request2 = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'tujuan' => 'Bandung',
            'keperluan' => 'Workshop'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/travel-requests/search?q=Jakarta');

        $response->assertStatus(200);
        $response->assertSee($request1->tujuan);
        $response->assertDontSee($request2->tujuan);
    }

    /** @test */
    public function user_can_search_travel_requests_by_status()
    {
        $request1 = TravelRequest::factory()->submitted()->create([
            'user_id' => $this->user->id
        ]);

        $request2 = TravelRequest::factory()->approved()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->get('/travel-requests/search?status=submitted');

        $response->assertStatus(200);
        $response->assertSee($request1->id);
        $response->assertDontSee($request2->id);
    }

    /** @test */
    public function user_can_search_travel_requests_by_date_range()
    {
        $request1 = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'tanggal_berangkat' => '2024-01-15'
        ]);

        $request2 = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'tanggal_berangkat' => '2024-02-15'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/travel-requests/search?start_date=2024-01-01&end_date=2024-01-31');

        $response->assertStatus(200);
        $response->assertSee($request1->id);
        $response->assertDontSee($request2->id);
    }

    /** @test */
    public function user_can_search_travel_requests_by_transportation()
    {
        $request1 = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'transportasi' => 'Pesawat'
        ]);

        $request2 = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'transportasi' => 'Kereta'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/travel-requests/search?transportasi=Pesawat');

        $response->assertStatus(200);
        $response->assertSee($request1->id);
        $response->assertDontSee($request2->id);
    }

    /** @test */
    public function user_can_search_travel_requests_by_cost_range()
    {
        $request1 = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'estimasi_biaya' => 1000000
        ]);

        $request2 = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'estimasi_biaya' => 5000000
        ]);

        $response = $this->actingAs($this->user)
            ->get('/travel-requests/search?min_cost=1000000&max_cost=2000000');

        $response->assertStatus(200);
        $response->assertSee($request1->id);
        $response->assertDontSee($request2->id);
    }

    /** @test */
    public function admin_can_search_users()
    {
        $user1 = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $user2 = User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com'
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/users/search?q=John');

        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertDontSee($user2->name);
    }

    /** @test */
    public function admin_can_search_users_by_role()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'approver']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/users/search?role=user');

        $response->assertStatus(200);
        $response->assertSee($user1->id);
        $response->assertDontSee($user2->id);
    }

    /** @test */
    public function admin_can_search_users_by_jabatan()
    {
        $user1 = User::factory()->create(['jabatan' => 'Staff']);
        $user2 = User::factory()->create(['jabatan' => 'Manager']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/users/search?jabatan=Staff');

        $response->assertStatus(200);
        $response->assertSee($user1->id);
        $response->assertDontSee($user2->id);
    }

    /** @test */
    public function user_can_search_documents()
    {
        $doc1 = Document::factory()->create([
            'nama_dokumen' => 'Surat Tugas',
            'jenis_dokumen' => 'surat_tugas'
        ]);

        $doc2 = Document::factory()->create([
            'nama_dokumen' => 'Laporan Perjalanan',
            'jenis_dokumen' => 'laporan'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/documents/search?q=Surat');

        $response->assertStatus(200);
        $response->assertSee($doc1->nama_dokumen);
        $response->assertDontSee($doc2->nama_dokumen);
    }

    /** @test */
    public function user_can_search_documents_by_type()
    {
        $doc1 = Document::factory()->suratTugas()->create();
        $doc2 = Document::factory()->laporan()->create();

        $response = $this->actingAs($this->user)
            ->get('/documents/search?jenis=surat_tugas');

        $response->assertStatus(200);
        $response->assertSee($doc1->id);
        $response->assertDontSee($doc2->id);
    }

    /** @test */
    public function user_can_search_documents_by_date()
    {
        $doc1 = Document::factory()->create([
            'tanggal_upload' => '2024-01-15'
        ]);

        $doc2 = Document::factory()->create([
            'tanggal_upload' => '2024-02-15'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/documents/search?tanggal=2024-01-15');

        $response->assertStatus(200);
        $response->assertSee($doc1->id);
        $response->assertDontSee($doc2->id);
    }

    /** @test */
    public function admin_can_search_activity_logs()
    {
        $log1 = ActivityLog::factory()->create([
            'action' => 'login',
            'description' => 'User logged in'
        ]);

        $log2 = ActivityLog::factory()->create([
            'action' => 'logout',
            'description' => 'User logged out'
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/logs/search?q=login');

        $response->assertStatus(200);
        $response->assertSee($log1->id);
        $response->assertDontSee($log2->id);
    }

    /** @test */
    public function admin_can_search_activity_logs_by_action()
    {
        $log1 = ActivityLog::factory()->login()->create();
        $log2 = ActivityLog::factory()->logout()->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/logs/search?action=login');

        $response->assertStatus(200);
        $response->assertSee($log1->id);
        $response->assertDontSee($log2->id);
    }

    /** @test */
    public function admin_can_search_activity_logs_by_user()
    {
        $log1 = ActivityLog::factory()->create([
            'user_id' => $this->user->id
        ]);

        $log2 = ActivityLog::factory()->create([
            'user_id' => $this->admin->id
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/logs/search?user_id={$this->user->id}");

        $response->assertStatus(200);
        $response->assertSee($log1->id);
        $response->assertDontSee($log2->id);
    }

    /** @test */
    public function user_can_search_approvals()
    {
        $approval1 = Approval::factory()->create([
            'status' => 'pending',
            'catatan' => 'Menunggu review'
        ]);

        $approval2 = Approval::factory()->create([
            'status' => 'approved',
            'catatan' => 'Disetujui'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/approvals/search?q=Menunggu');

        $response->assertStatus(200);
        $response->assertSee($approval1->id);
        $response->assertDontSee($approval2->id);
    }

    /** @test */
    public function user_can_search_approvals_by_status()
    {
        $approval1 = Approval::factory()->pending()->create();
        $approval2 = Approval::factory()->approved()->create();

        $response = $this->actingAs($this->user)
            ->get('/approvals/search?status=pending');

        $response->assertStatus(200);
        $response->assertSee($approval1->id);
        $response->assertDontSee($approval2->id);
    }

    /** @test */
    public function search_results_are_paginated()
    {
        // Create more than 15 travel requests
        TravelRequest::factory()->count(20)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->get('/travel-requests/search');

        $response->assertStatus(200);
        $response->assertViewHas('travelRequests');
    }

    /** @test */
    public function search_with_empty_query_returns_all_results()
    {
        $request1 = TravelRequest::factory()->create(['user_id' => $this->user->id]);
        $request2 = TravelRequest::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->get('/travel-requests/search?q=');

        $response->assertStatus(200);
        $response->assertSee($request1->id);
        $response->assertSee($request2->id);
    }

    /** @test */
    public function search_is_case_insensitive()
    {
        $request = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'tujuan' => 'Jakarta'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/travel-requests/search?q=jakarta');

        $response->assertStatus(200);
        $response->assertSee($request->id);
    }

    /** @test */
    public function search_supports_partial_matching()
    {
        $request = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'tujuan' => 'Jakarta Pusat'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/travel-requests/search?q=Jak');

        $response->assertStatus(200);
        $response->assertSee($request->id);
    }

    /** @test */
    public function search_with_multiple_filters()
    {
        $request = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'tujuan' => 'Jakarta',
            'transportasi' => 'Pesawat',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/travel-requests/search?tujuan=Jakarta&transportasi=Pesawat&status=pending');

        $response->assertStatus(200);
        $response->assertSee($request->id);
    }
}
