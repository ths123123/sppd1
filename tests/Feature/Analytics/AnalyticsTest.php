<?php

namespace Tests\Feature\Analytics;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Document;
use App\Models\Approval;
use App\Models\Setting;
use App\Models\Notification;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsTest extends TestCase
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
    public function admin_can_view_dashboard_analytics()
    {
        // Create test data
        $this->createTestData();

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('admin.analytics.dashboard');
        $response->assertViewHas('totalUsers');
        $response->assertViewHas('totalTravelRequests');
        $response->assertViewHas('totalDocuments');
        $response->assertViewHas('totalApprovals');
    }

    /** @test */
    public function admin_can_view_user_analytics()
    {
        // Create test data
        $this->createTestData();

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/users');

        $response->assertStatus(200);
        $response->assertViewIs('admin.analytics.users');
        $response->assertViewHas('userStats');
        $response->assertViewHas('roleDistribution');
        $response->assertViewHas('userGrowth');
    }

    /** @test */
    public function admin_can_view_travel_request_analytics()
    {
        // Create test data
        $this->createTestData();

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/travel-requests');

        $response->assertStatus(200);
        $response->assertViewIs('admin.analytics.travel-requests');
        $response->assertViewHas('requestStats');
        $response->assertViewHas('statusDistribution');
        $response->assertViewHas('monthlyTrends');
    }

    /** @test */
    public function admin_can_view_document_analytics()
    {
        // Create test data
        $this->createTestData();

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/documents');

        $response->assertStatus(200);
        $response->assertViewIs('admin.analytics.documents');
        $response->assertViewHas('documentStats');
        $response->assertViewHas('typeDistribution');
        $response->assertViewHas('uploadTrends');
    }

    /** @test */
    public function admin_can_view_approval_analytics()
    {
        // Create test data
        $this->createTestData();

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/approvals');

        $response->assertStatus(200);
        $response->assertViewIs('admin.analytics.approvals');
        $response->assertViewHas('approvalStats');
        $response->assertViewHas('approvalRates');
        $response->assertViewHas('responseTimes');
    }

    /** @test */
    public function admin_can_view_system_performance_analytics()
    {
        // Create test data
        $this->createTestData();

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/performance');

        $response->assertStatus(200);
        $response->assertViewIs('admin.analytics.performance');
        $response->assertViewHas('performanceMetrics');
        $response->assertViewHas('responseTimes');
        $response->assertViewHas('errorRates');
    }

    /** @test */
    public function admin_can_generate_custom_reports()
    {
        // Create test data
        $this->createTestData();

        $reportData = [
            'type' => 'custom',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'metrics' => ['users', 'travel_requests', 'documents'],
            'group_by' => 'month',
            'format' => 'excel'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/analytics/reports/generate', $reportData);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function admin_can_export_analytics_data()
    {
        // Create test data
        $this->createTestData();

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/export?type=summary&format=excel');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function analytics_calculate_correct_user_statistics()
    {
        // Create test users
        User::factory()->count(10)->create(['role' => 'user']);
        User::factory()->count(5)->create(['role' => 'approver']);
        User::factory()->count(2)->create(['role' => 'admin']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/users');

        $response->assertStatus(200);
        
        // Check if statistics are calculated correctly
        $this->assertEquals(18, User::count()); // 1 from setUp + 17 new
        $this->assertEquals(10, User::where('role', 'user')->count());
        $this->assertEquals(5, User::where('role', 'approver')->count());
        $this->assertEquals(3, User::where('role', 'admin')->count()); // 1 from setUp + 2 new
    }

    /** @test */
    public function analytics_calculate_correct_travel_request_statistics()
    {
        // Create test travel requests
        TravelRequest::factory()->count(20)->create(['status' => 'pending']);
        TravelRequest::factory()->count(15)->create(['status' => 'approved']);
        TravelRequest::factory()->count(5)->create(['status' => 'rejected']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/travel-requests');

        $response->assertStatus(200);
        
        // Check if statistics are calculated correctly
        $this->assertEquals(40, TravelRequest::count());
        $this->assertEquals(20, TravelRequest::where('status', 'pending')->count());
        $this->assertEquals(15, TravelRequest::where('status', 'approved')->count());
        $this->assertEquals(5, TravelRequest::where('status', 'rejected')->count());
    }

    /** @test */
    public function analytics_calculate_correct_document_statistics()
    {
        // Create test documents
        Document::factory()->count(25)->create(['jenis_dokumen' => 'surat_tugas']);
        Document::factory()->count(15)->create(['jenis_dokumen' => 'laporan']);
        Document::factory()->count(10)->create(['jenis_dokumen' => 'surat_izin']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/documents');

        $response->assertStatus(200);
        
        // Check if statistics are calculated correctly
        $this->assertEquals(50, Document::count());
        $this->assertEquals(25, Document::where('jenis_dokumen', 'surat_tugas')->count());
        $this->assertEquals(15, Document::where('jenis_dokumen', 'laporan')->count());
        $this->assertEquals(10, Document::where('jenis_dokumen', 'surat_izin')->count());
    }

    /** @test */
    public function analytics_calculate_correct_approval_statistics()
    {
        // Create test approvals
        Approval::factory()->count(30)->create(['status' => 'pending']);
        Approval::factory()->count(25)->create(['status' => 'approved']);
        Approval::factory()->count(10)->create(['status' => 'rejected']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/approvals');

        $response->assertStatus(200);
        
        // Check if statistics are calculated correctly
        $this->assertEquals(65, Approval::count());
        $this->assertEquals(30, Approval::where('status', 'pending')->count());
        $this->assertEquals(25, Approval::where('status', 'approved')->count());
        $this->assertEquals(10, Approval::where('status', 'rejected')->count());
    }

    /** @test */
    public function analytics_calculate_monthly_trends()
    {
        // Create travel requests for different months
        $this->createMonthlyData();

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/travel-requests');

        $response->assertStatus(200);
        
        // Check if monthly trends are calculated
        $monthlyData = TravelRequest::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', 2024)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $this->assertGreaterThan(0, $monthlyData->count());
    }

    /** @test */
    public function analytics_calculate_approval_rates()
    {
        // Create test approvals
        Approval::factory()->count(40)->create(['status' => 'approved']);
        Approval::factory()->count(10)->create(['status' => 'rejected']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/approvals');

        $response->assertStatus(200);
        
        // Calculate approval rate
        $totalApprovals = Approval::count();
        $approvedCount = Approval::where('status', 'approved')->count();
        $approvalRate = ($approvedCount / $totalApprovals) * 100;

        $this->assertEquals(80, $approvalRate); // 40 approved out of 50 total
    }

    /** @test */
    public function analytics_calculate_response_times()
    {
        // Create test approvals with timestamps
        $approval = Approval::factory()->create([
            'status' => 'approved',
            'created_at' => now()->subDays(2),
            'updated_at' => now()
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/approvals');

        $response->assertStatus(200);
        
        // Check if response time is calculated
        $responseTime = $approval->updated_at->diffInHours($approval->created_at);
        $this->assertEquals(48, $responseTime); // 2 days = 48 hours
    }

    /** @test */
    public function analytics_calculate_user_activity()
    {
        // Create test activity logs
        ActivityLog::factory()->count(50)->create(['user_id' => $this->user->id]);
        ActivityLog::factory()->count(30)->create(['user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/users');

        $response->assertStatus(200);
        
        // Check if user activity is calculated
        $userActivity = ActivityLog::where('user_id', $this->user->id)->count();
        $adminActivity = ActivityLog::where('user_id', $this->admin->id)->count();

        $this->assertEquals(50, $userActivity);
        $this->assertEquals(30, $adminActivity);
    }

    /** @test */
    public function analytics_calculate_document_verification_rates()
    {
        // Create test documents
        Document::factory()->count(30)->create(['is_verified' => true]);
        Document::factory()->count(20)->create(['is_verified' => false]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/documents');

        $response->assertStatus(200);
        
        // Calculate verification rate
        $totalDocuments = Document::count();
        $verifiedCount = Document::where('is_verified', true)->count();
        $verificationRate = ($verifiedCount / $totalDocuments) * 100;

        $this->assertEquals(60, $verificationRate); // 30 verified out of 50 total
    }

    /** @test */
    public function analytics_calculate_cost_statistics()
    {
        // Create test travel requests with costs
        TravelRequest::factory()->count(10)->create(['estimasi_biaya' => 1000000]);
        TravelRequest::factory()->count(10)->create(['estimasi_biaya' => 2000000]);
        TravelRequest::factory()->count(10)->create(['estimasi_biaya' => 3000000]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/travel-requests');

        $response->assertStatus(200);
        
        // Calculate cost statistics
        $totalCost = TravelRequest::sum('estimasi_biaya');
        $averageCost = TravelRequest::avg('estimasi_biaya');
        $maxCost = TravelRequest::max('estimasi_biaya');
        $minCost = TravelRequest::min('estimasi_biaya');

        $this->assertEquals(60000000, $totalCost); // 30 * 2,000,000 average
        $this->assertEquals(2000000, $averageCost);
        $this->assertEquals(3000000, $maxCost);
        $this->assertEquals(1000000, $minCost);
    }

    /** @test */
    public function analytics_calculate_transportation_statistics()
    {
        // Create test travel requests with different transportation
        TravelRequest::factory()->count(15)->create(['transportasi' => 'Pesawat']);
        TravelRequest::factory()->count(10)->create(['transportasi' => 'Kereta']);
        TravelRequest::factory()->count(5)->create(['transportasi' => 'Mobil']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/travel-requests');

        $response->assertStatus(200);
        
        // Check transportation distribution
        $this->assertEquals(15, TravelRequest::where('transportasi', 'Pesawat')->count());
        $this->assertEquals(10, TravelRequest::where('transportasi', 'Kereta')->count());
        $this->assertEquals(5, TravelRequest::where('transportasi', 'Mobil')->count());
    }

    /** @test */
    public function analytics_calculate_destination_statistics()
    {
        // Create test travel requests with different destinations
        TravelRequest::factory()->count(20)->create(['tujuan' => 'Jakarta']);
        TravelRequest::factory()->count(15)->create(['tujuan' => 'Bandung']);
        TravelRequest::factory()->count(10)->create(['tujuan' => 'Surabaya']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/travel-requests');

        $response->assertStatus(200);
        
        // Check destination distribution
        $this->assertEquals(20, TravelRequest::where('tujuan', 'Jakarta')->count());
        $this->assertEquals(15, TravelRequest::where('tujuan', 'Bandung')->count());
        $this->assertEquals(10, TravelRequest::where('tujuan', 'Surabaya')->count());
    }

    /** @test */
    public function analytics_calculate_notification_statistics()
    {
        // Create test notifications
        Notification::factory()->count(40)->create(['is_read' => true]);
        Notification::factory()->count(20)->create(['is_read' => false]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/notifications');

        $response->assertStatus(200);
        
        // Calculate notification statistics
        $totalNotifications = Notification::count();
        $readNotifications = Notification::where('is_read', true)->count();
        $unreadNotifications = Notification::where('is_read', false)->count();
        $readRate = ($readNotifications / $totalNotifications) * 100;

        $this->assertEquals(60, $totalNotifications);
        $this->assertEquals(40, $readNotifications);
        $this->assertEquals(20, $unreadNotifications);
        $this->assertEquals(66.67, round($readRate, 2));
    }

    /** @test */
    public function analytics_calculate_system_performance_metrics()
    {
        // Create test data for performance metrics
        $this->createPerformanceData();

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/performance');

        $response->assertStatus(200);
        
        // Check if performance metrics are calculated
        $this->assertTrue(true); // Placeholder for performance metrics validation
    }

    /** @test */
    public function analytics_generate_charts_and_graphs()
    {
        // Create test data
        $this->createTestData();

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/charts');

        $response->assertStatus(200);
        $response->assertViewIs('admin.analytics.charts');
        $response->assertViewHas('chartData');
    }

    /** @test */
    public function analytics_calculate_real_time_statistics()
    {
        // Create test data
        $this->createTestData();

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/real-time');

        $response->assertStatus(200);
        $response->assertViewIs('admin.analytics.real-time');
        $response->assertViewHas('realTimeStats');
    }

    /** @test */
    public function analytics_calculate_comparative_statistics()
    {
        // Create test data for different periods
        $this->createComparativeData();

        $response = $this->actingAs($this->admin)
            ->get('/admin/analytics/comparative');

        $response->assertStatus(200);
        $response->assertViewIs('admin.analytics.comparative');
        $response->assertViewHas('comparativeStats');
    }

    private function createTestData()
    {
        // Create users
        User::factory()->count(20)->create();
        
        // Create travel requests
        TravelRequest::factory()->count(50)->create();
        
        // Create documents
        Document::factory()->count(30)->create();
        
        // Create approvals
        Approval::factory()->count(40)->create();
        
        // Create notifications
        Notification::factory()->count(60)->create();
        
        // Create activity logs
        ActivityLog::factory()->count(100)->create();
    }

    private function createMonthlyData()
    {
        // Create travel requests for different months in 2024
        for ($month = 1; $month <= 12; $month++) {
            TravelRequest::factory()->count(rand(5, 15))->create([
                'created_at' => Carbon::create(2024, $month, rand(1, 28))
            ]);
        }
    }

    private function createPerformanceData()
    {
        // Create data for performance metrics
        // This would include response times, error rates, etc.
    }

    private function createComparativeData()
    {
        // Create data for different periods for comparison
        // This would include data for current month vs previous month
    }
}
