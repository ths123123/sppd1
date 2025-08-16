<?php

namespace Tests\Feature\Reporting;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Document;
use App\Models\Approval;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ReportingSystemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $kasubbag;
    protected $sekretaris;
    protected $ppk;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->kasubbag = User::factory()->create(['role' => 'kasubbag']);
        $this->sekretaris = User::factory()->create(['role' => 'sekretaris']);
        $this->ppk = User::factory()->create(['role' => 'ppk']);
        
        // Create basic settings
        Setting::create(['key' => 'system_name', 'value' => 'SPPD KPU Cirebon']);
        Setting::create(['key' => 'fiscal_year', 'value' => '2024']);
    }

    /** @test */
    public function analytics_dashboard_shows_correct_statistics()
    {
        $this->actingAs($this->admin);

        // Create test data
        TravelRequest::factory()->count(10)->create(['status' => 'approved']);
        TravelRequest::factory()->count(5)->create(['status' => 'pending']);
        TravelRequest::factory()->count(3)->create(['status' => 'rejected']);
        TravelRequest::factory()->count(2)->create(['status' => 'draft']);

        // Test analytics dashboard
        $response = $this->get(route('analytics.index'));
        $response->assertStatus(200);

        // Check statistics are displayed
        $response->assertSee('10'); // Approved count
        $response->assertSee('5');  // Pending count
        $response->assertSee('3');  // Rejected count
        $response->assertSee('2');  // Draft count
        $response->assertSee('20'); // Total count
    }

    /** @test */
    public function analytics_shows_budget_statistics()
    {
        $this->actingAs($this->admin);

        // Create SPPD with different budgets
        TravelRequest::factory()->create([
            'status' => 'approved',
            'budget' => 5000000,
        ]);
        TravelRequest::factory()->create([
            'status' => 'approved',
            'budget' => 3000000,
        ]);
        TravelRequest::factory()->create([
            'status' => 'pending',
            'budget' => 2000000,
        ]);

        $response = $this->get(route('analytics.index'));
        $response->assertStatus(200);

        // Check budget statistics
        $response->assertSee('8,000,000'); // Total approved budget
        $response->assertSee('2,000,000'); // Pending budget
        $response->assertSee('10,000,000'); // Total budget
    }

    /** @test */
    public function analytics_shows_destination_statistics()
    {
        $this->actingAs($this->admin);

        // Create SPPD with different destinations
        TravelRequest::factory()->create([
            'destination' => 'Jakarta',
            'status' => 'approved',
        ]);
        TravelRequest::factory()->create([
            'destination' => 'Jakarta',
            'status' => 'approved',
        ]);
        TravelRequest::factory()->create([
            'destination' => 'Bandung',
            'status' => 'approved',
        ]);
        TravelRequest::factory()->create([
            'destination' => 'Surabaya',
            'status' => 'pending',
        ]);

        $response = $this->get(route('analytics.index'));
        $response->assertStatus(200);

        // Check destination statistics
        $response->assertSee('Jakarta');
        $response->assertSee('Bandung');
        $response->assertSee('Surabaya');
        $response->assertSee('2'); // Jakarta count
        $response->assertSee('1'); // Bandung count
    }

    /** @test */
    public function analytics_shows_monthly_trends()
    {
        $this->actingAs($this->admin);

        // Create SPPD for different months
        TravelRequest::factory()->create([
            'created_at' => now()->subMonths(2),
            'status' => 'approved',
        ]);
        TravelRequest::factory()->create([
            'created_at' => now()->subMonth(),
            'status' => 'approved',
        ]);
        TravelRequest::factory()->create([
            'created_at' => now(),
            'status' => 'approved',
        ]);

        $response = $this->get(route('analytics.index'));
        $response->assertStatus(200);

        // Check monthly trends are displayed
        $response->assertSee('chart'); // Chart component should be present
        $response->assertSee('trend'); // Trend information should be present
    }

    /** @test */
    public function laporan_daftar_shows_all_report_types()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('laporan.daftar'));
        $response->assertStatus(200);

        // Check all report types are available
        $response->assertSee('Laporan Rekapitulasi');
        $response->assertSee('Laporan Detail');
        $response->assertSee('Laporan Anggaran');
        $response->assertSee('Laporan Dokumen');
        $response->assertSee('Laporan Pengguna');
        $response->assertSee('Laporan Analitik');
    }

    /** @test */
    public function laporan_rekapitulasi_generates_correctly()
    {
        $this->actingAs($this->admin);

        // Create test data
        TravelRequest::factory()->count(5)->create(['status' => 'approved']);
        TravelRequest::factory()->count(3)->create(['status' => 'pending']);

        $response = $this->get(route('laporan.rekapitulasi'));
        $response->assertStatus(200);

        // Check report content
        $response->assertSee('LAPORAN REKAPITULASI SPPD');
        $response->assertSee('5'); // Approved count
        $response->assertSee('3'); // Pending count
        $response->assertSee('8'); // Total count
    }

    /** @test */
    public function laporan_detail_generates_correctly()
    {
        $this->actingAs($this->admin);

        // Create test data
        $sppd = TravelRequest::factory()->create([
            'title' => 'Test SPPD Detail',
            'destination' => 'Jakarta',
            'budget' => 5000000,
            'status' => 'approved',
        ]);

        $response = $this->get(route('laporan.detail'));
        $response->assertStatus(200);

        // Check report content
        $response->assertSee('LAPORAN DETAIL SPPD');
        $response->assertSee('Test SPPD Detail');
        $response->assertSee('Jakarta');
        $response->assertSee('5,000,000');
        $response->assertSee('Approved');
    }

    /** @test */
    public function laporan_anggaran_generates_correctly()
    {
        $this->actingAs($this->admin);

        // Create test data with budgets
        TravelRequest::factory()->create([
            'budget' => 5000000,
            'status' => 'approved',
        ]);
        TravelRequest::factory()->create([
            'budget' => 3000000,
            'status' => 'approved',
        ]);
        TravelRequest::factory()->create([
            'budget' => 2000000,
            'status' => 'pending',
        ]);

        $response = $this->get(route('laporan.anggaran'));
        $response->assertStatus(200);

        // Check budget report content
        $response->assertSee('LAPORAN ANGGARAN SPPD');
        $response->assertSee('8,000,000'); // Total approved
        $response->assertSee('2,000,000'); // Pending
        $response->assertSee('10,000,000'); // Total
    }

    /** @test */
    public function laporan_dokumen_generates_correctly()
    {
        $this->actingAs($this->admin);

        // Create test data
        $sppd = TravelRequest::factory()->create();
        Document::factory()->create([
            'travel_request_id' => $sppd->id,
            'type' => 'surat_tugas',
            'filename' => 'test_document.pdf',
        ]);

        $response = $this->get(route('laporan.dokumen'));
        $response->assertStatus(200);

        // Check document report content
        $response->assertSee('LAPORAN DOKUMEN SPPD');
        $response->assertSee('test_document.pdf');
        $response->assertSee('surat_tugas');
    }

    /** @test */
    public function laporan_pengguna_generates_correctly()
    {
        $this->actingAs($this->admin);

        // Create test users
        User::factory()->create(['role' => 'kasubbag']);
        User::factory()->create(['role' => 'sekretaris']);
        User::factory()->create(['role' => 'ppk']);

        $response = $this->get(route('laporan.pengguna'));
        $response->assertStatus(200);

        // Check user report content
        $response->assertSee('LAPORAN PENGGUNA SPPD');
        $response->assertSee('kasubbag');
        $response->assertSee('sekretaris');
        $response->assertSee('ppk');
    }

    /** @test */
    public function laporan_analitik_generates_correctly()
    {
        $this->actingAs($this->admin);

        // Create test data
        TravelRequest::factory()->count(3)->create(['status' => 'approved']);
        TravelRequest::factory()->count(2)->create(['status' => 'pending']);

        $response = $this->get(route('laporan.analitik'));
        $response->assertStatus(200);

        // Check analytics report content
        $response->assertSee('LAPORAN ANALITIK SPPD');
        $response->assertSee('3'); // Approved count
        $response->assertSee('2'); // Pending count
        $response->assertSee('5'); // Total count
    }

    /** @test */
    public function pdf_export_works_for_all_reports()
    {
        $this->actingAs($this->admin);

        // Create test data
        TravelRequest::factory()->count(3)->create();

        // Test PDF export for each report type
        $reportTypes = [
            'rekapitulasi' => 'LAPORAN REKAPITULASI SPPD',
            'detail' => 'LAPORAN DETAIL SPPD',
            'anggaran' => 'LAPORAN ANGGARAN SPPD',
            'dokumen' => 'LAPORAN DOKUMEN SPPD',
            'pengguna' => 'LAPORAN PENGGUNA SPPD',
            'analitik' => 'LAPORAN ANALITIK SPPD',
        ];

        foreach ($reportTypes as $type => $expectedTitle) {
            $response = $this->get(route("laporan.export.{$type}.pdf"));
            $response->assertStatus(200);
            $response->assertHeader('content-type', 'application/pdf');
        }
    }

    /** @test */
    public function excel_export_works_for_all_reports()
    {
        $this->actingAs($this->admin);

        // Create test data
        TravelRequest::factory()->count(3)->create();

        // Test Excel export for each report type
        $reportTypes = [
            'rekapitulasi',
            'detail',
            'anggaran',
            'dokumen',
            'pengguna',
            'analitik',
        ];

        foreach ($reportTypes as $type) {
            $response = $this->get(route("laporan.export.{$type}.excel"));
            $response->assertStatus(200);
            $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        }
    }

    /** @test */
    public function reports_respect_date_filters()
    {
        $this->actingAs($this->admin);

        // Create SPPD for different dates
        TravelRequest::factory()->create([
            'created_at' => now()->subMonths(3),
            'status' => 'approved',
        ]);
        TravelRequest::factory()->create([
            'created_at' => now()->subMonth(),
            'status' => 'approved',
        ]);
        TravelRequest::factory()->create([
            'created_at' => now(),
            'status' => 'pending',
        ]);

        // Test date filter
        $response = $this->get(route('laporan.rekapitulasi', [
            'start_date' => now()->subMonths(2)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $response->assertSee('2'); // Should show 2 SPPD in date range
    }

    /** @test */
    public function reports_respect_status_filters()
    {
        $this->actingAs($this->admin);

        // Create SPPD with different statuses
        TravelRequest::factory()->create(['status' => 'approved']);
        TravelRequest::factory()->create(['status' => 'pending']);
        TravelRequest::factory()->create(['status' => 'rejected']);

        // Test status filter
        $response = $this->get(route('laporan.rekapitulasi', [
            'status' => 'approved',
        ]));

        $response->assertStatus(200);
        $response->assertSee('1'); // Should show only approved SPPD
        $response->assertDontSee('pending');
        $response->assertDontSee('rejected');
    }

    /** @test */
    public function reports_respect_user_filters()
    {
        $this->actingAs($this->admin);

        $user1 = User::factory()->create(['role' => 'kasubbag']);
        $user2 = User::factory()->create(['role' => 'sekretaris']);

        // Create SPPD for different users
        TravelRequest::factory()->create(['user_id' => $user1->id]);
        TravelRequest::factory()->create(['user_id' => $user2->id]);

        // Test user filter
        $response = $this->get(route('laporan.rekapitulasi', [
            'user_id' => $user1->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('1'); // Should show only SPPD from user1
    }

    /** @test */
    public function reports_show_correct_permissions()
    {
        // Test admin access
        $this->actingAs($this->admin);
        $response = $this->get(route('laporan.daftar'));
        $response->assertStatus(200);

        // Test kasubbag access
        $this->actingAs($this->kasubbag);
        $response = $this->get(route('laporan.daftar'));
        $response->assertStatus(200);

        // Test sekretaris access
        $this->actingAs($this->sekretaris);
        $response = $this->get(route('laporan.daftar'));
        $response->assertStatus(200);

        // Test ppk access
        $this->actingAs($this->ppk);
        $response = $this->get(route('laporan.daftar'));
        $response->assertStatus(200);
    }

    /** @test */
    public function reports_generate_with_charts_and_graphs()
    {
        $this->actingAs($this->admin);

        // Create test data for charts
        TravelRequest::factory()->count(5)->create(['status' => 'approved']);
        TravelRequest::factory()->count(3)->create(['status' => 'pending']);
        TravelRequest::factory()->count(2)->create(['status' => 'rejected']);

        $response = $this->get(route('analytics.index'));
        $response->assertStatus(200);

        // Check chart components are present
        $response->assertSee('chart-container');
        $response->assertSee('pie-chart');
        $response->assertSee('bar-chart');
        $response->assertSee('line-chart');
    }

    /** @test */
    public function reports_include_proper_summaries()
    {
        $this->actingAs($this->admin);

        // Create test data
        TravelRequest::factory()->count(3)->create([
            'status' => 'approved',
            'budget' => 1000000,
        ]);

        $response = $this->get(route('laporan.rekapitulasi'));
        $response->assertStatus(200);

        // Check summary information
        $response->assertSee('Total SPPD: 3');
        $response->assertSee('Total Anggaran: 3,000,000');
        $response->assertSee('Rata-rata Anggaran: 1,000,000');
    }

    /** @test */
    public function reports_can_be_scheduled()
    {
        $this->actingAs($this->admin);

        // Test report scheduling
        $response = $this->post(route('laporan.schedule'), [
            'report_type' => 'rekapitulasi',
            'frequency' => 'weekly',
            'email' => 'admin@example.com',
        ]);

        $response->assertRedirect();

        // Check schedule was created
        $this->assertDatabaseHas('report_schedules', [
            'report_type' => 'rekapitulasi',
            'frequency' => 'weekly',
            'email' => 'admin@example.com',
        ]);
    }

    /** @test */
    public function reports_can_be_customized()
    {
        $this->actingAs($this->admin);

        // Test report customization
        $response = $this->post(route('laporan.customize'), [
            'report_type' => 'rekapitulasi',
            'include_charts' => true,
            'include_summaries' => true,
            'custom_fields' => ['budget', 'destination', 'status'],
        ]);

        $response->assertRedirect();

        // Check customization was saved
        $this->assertDatabaseHas('report_customizations', [
            'report_type' => 'rekapitulasi',
            'include_charts' => true,
            'include_summaries' => true,
        ]);
    }

    /** @test */
    public function reports_handle_large_datasets()
    {
        $this->actingAs($this->admin);

        // Create large dataset
        TravelRequest::factory()->count(1000)->create();

        // Test report generation with large dataset
        $startTime = microtime(true);
        $response = $this->get(route('laporan.rekapitulasi'));
        $endTime = microtime(true);

        $response->assertStatus(200);
        
        // Report should generate within reasonable time (less than 5 seconds)
        $this->assertLessThan(5, $endTime - $startTime);
        
        // Should show pagination or limit results
        $response->assertSee('1000'); // Total count
    }

    /** @test */
    public function reports_include_audit_trail()
    {
        $this->actingAs($this->admin);

        // Generate a report
        $response = $this->get(route('laporan.rekapitulasi'));
        $response->assertStatus(200);

        // Check audit trail was created
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'generated_report',
            'details' => 'rekapitulasi',
        ]);
    }
}
