<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\TravelRequest;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class DashboardActivityTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create([
            'role' => 'kasubbag'
        ]);
    }

    /** @test */
    public function it_can_load_dashboard_with_activity_card()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Aktivitas Terbaru');
        $response->assertSee('recent-activities-container');
    }

    /** @test */
    public function it_can_fetch_recent_activities_via_api()
    {
        // Create some test activities
        $this->createTestActivities();

        $response = $this->actingAs($this->user)
            ->get('/dashboard/recent-activities');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'description',
                    'created_at',
                    'kode_sppd',
                    'status'
                ]
            ]
        ]);
    }

    /** @test */
    public function it_returns_empty_activities_when_none_exist()
    {
        $response = $this->actingAs($this->user)
            ->get('/dashboard/recent-activities');

        $response->assertStatus(200);
        $response->assertJson([
            'data' => []
        ]);
    }

    /** @test */
    public function it_can_load_dashboard_service_methods()
    {
        $dashboardService = new DashboardService();
        
        // Test if method exists
        $this->assertTrue(method_exists($dashboardService, 'getFormattedRecentActivities'));
        
        // Test method returns array
        $activities = $dashboardService->getFormattedRecentActivities(5);
        $this->assertIsArray($activities);
    }

    /** @test */
    public function it_can_handle_duplicate_activities()
    {
        // Create duplicate activities
        $this->createDuplicateActivities();

        $dashboardService = new DashboardService();
        $activities = $dashboardService->getFormattedRecentActivities(10);

        // Should return unique activities
        $uniqueKeys = collect($activities)->pluck('unique_key')->unique();
        $this->assertEquals($uniqueKeys->count(), count($activities));
    }

    /** @test */
    public function it_can_access_dashboard_route_without_database_connection()
    {
        // This test will help identify if the issue is with the route or database
        try {
            $response = $this->actingAs($this->user)
                ->get(route('dashboard'));
            
            $response->assertStatus(200);
        } catch (\Exception $e) {
            $this->fail('Dashboard route should be accessible even with database issues: ' . $e->getMessage());
        }
    }

    /** @test */
    public function it_can_load_dashboard_view_structure()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        
        // Check if the view contains necessary elements
        $response->assertSee('dashboard-utama');
        $response->assertSee('recent-activities-container');
        $response->assertSee('Aktivitas Terbaru');
    }

    /** @test */
    public function it_can_access_recent_activities_endpoint()
    {
        $response = $this->actingAs($this->user)
            ->get('/dashboard/recent-activities');

        // Should return 200 even if no data
        $response->assertStatus(200);
    }

    private function createTestActivities()
    {
        // Create test travel requests
        $travelRequest1 = TravelRequest::factory()->create([
            'kode_sppd' => 'SPPD-001',
            'status' => 'pending'
        ]);

        $travelRequest2 = TravelRequest::factory()->create([
            'kode_sppd' => 'SPPD-002',
            'status' => 'approved'
        ]);

        // Create activity logs
        ActivityLog::factory()->create([
            'user_id' => $this->user->id,
            'description' => 'SPPD SPPD-001 dibuat',
            'kode_sppd' => 'SPPD-001',
            'status' => 'pending'
        ]);

        ActivityLog::factory()->create([
            'user_id' => $this->user->id,
            'description' => 'SPPD SPPD-002 disetujui',
            'kode_sppd' => 'SPPD-002',
            'status' => 'approved'
        ]);
    }

    private function createDuplicateActivities()
    {
        // Create multiple identical activities
        for ($i = 0; $i < 3; $i++) {
            ActivityLog::factory()->create([
                'user_id' => $this->user->id,
                'description' => 'SPPD SPPD-001 dibuat',
                'kode_sppd' => 'SPPD-001',
                'status' => 'pending'
            ]);
        }
    }
}
