<?php

namespace Tests\Feature\Monitoring;

use Tests\TestCase;
use App\Models\User;
use App\Models\Setting;
use App\Models\ActivityLog;
use App\Models\TravelRequest;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class MonitoringTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->admin()->create();
        $this->user = User::factory()->create();
    }

    public function test_admin_can_view_system_dashboard()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring');

        $response->assertStatus(200);
        $response->assertViewIs('admin.monitoring.index');
    }

    public function test_regular_user_cannot_access_monitoring()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/monitoring');

        $response->assertStatus(403);
    }

    public function test_admin_can_view_system_health()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/health');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'database',
            'cache',
            'storage',
            'queue',
            'mail',
            'last_check'
        ]);
    }

    public function test_admin_can_view_database_status()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/database');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'connection',
            'tables',
            'size',
            'connections',
            'slow_queries',
            'performance'
        ]);
    }

    public function test_admin_can_view_storage_status()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/storage');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_space',
            'used_space',
            'free_space',
            'uploads_size',
            'backups_size',
            'logs_size',
            'temp_size'
        ]);
    }

    public function test_admin_can_view_cache_status()
    {
        // Set some test cache data
        Cache::put('test_key', 'test_value', 60);
        Cache::put('another_key', 'another_value', 60);

        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/cache');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'driver',
            'keys_count',
            'memory_usage',
            'hit_rate',
            'miss_rate'
        ]);
    }

    public function test_admin_can_view_queue_status()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/queue');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'driver',
            'jobs_count',
            'failed_jobs',
            'workers',
            'throughput'
        ]);
    }

    public function test_admin_can_view_mail_status()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/mail');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'driver',
            'sent_count',
            'failed_count',
            'queue_size',
            'last_sent'
        ]);
    }

    public function test_admin_can_view_system_performance()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/performance');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'cpu_usage',
            'memory_usage',
            'disk_io',
            'network_io',
            'response_time',
            'throughput'
        ]);
    }

    public function test_admin_can_view_user_activity()
    {
        // Create some test activity
        ActivityLog::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'action' => 'login'
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/user-activity');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'active_users',
            'total_logins',
            'last_activity',
            'top_actions',
            'user_sessions'
        ]);
    }

    public function test_admin_can_view_error_logs()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/errors');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'error_count',
            'error_types',
            'recent_errors',
            'error_trends'
        ]);
    }

    public function test_admin_can_view_security_status()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/security');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'failed_logins',
            'suspicious_activity',
            'blocked_ips',
            'security_events',
            'vulnerability_scan'
        ]);
    }

    public function test_admin_can_view_backup_status()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/backup-status');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'last_backup',
            'backup_size',
            'backup_count',
            'backup_health',
            'next_scheduled'
        ]);
    }

    public function test_admin_can_view_api_status()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/api');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'endpoints',
            'requests_count',
            'response_time',
            'error_rate',
            'rate_limits'
        ]);
    }

    public function test_admin_can_view_external_services()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/external-services');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'database',
            'cache',
            'mail',
            'storage',
            'third_party_apis'
        ]);
    }

    public function test_admin_can_run_system_diagnostics()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/monitoring/diagnostics');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify diagnostics were logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'system_diagnostics_run'
        ]);
    }

    public function test_admin_can_clear_cache()
    {
        // Set some test cache data
        Cache::put('test_key', 'test_value', 60);

        $response = $this->actingAs($this->admin)
            ->post('/admin/monitoring/clear-cache');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify cache was cleared
        $this->assertNull(Cache::get('test_key'));
    }

    public function test_admin_can_clear_logs()
    {
        // Create some test logs
        ActivityLog::factory()->count(10)->create();

        $response = $this->actingAs($this->admin)
            ->post('/admin/monitoring/clear-logs');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify logs were cleared
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_admin_can_restart_services()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/monitoring/restart-services');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify restart was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'services_restarted'
        ]);
    }

    public function test_admin_can_view_system_alerts()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/alerts');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'critical_alerts',
            'warning_alerts',
            'info_alerts',
            'resolved_alerts'
        ]);
    }

    public function test_admin_can_set_alert_thresholds()
    {
        $thresholds = [
            'cpu_threshold' => 80,
            'memory_threshold' => 85,
            'disk_threshold' => 90,
            'error_threshold' => 5
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/monitoring/alert-thresholds', $thresholds);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify thresholds were saved
        foreach ($thresholds as $key => $value) {
            $this->assertDatabaseHas('settings', [
                'key' => $key,
                'value' => (string) $value
            ]);
        }
    }

    public function test_admin_can_view_system_reports()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/reports');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'daily_reports',
            'weekly_reports',
            'monthly_reports',
            'custom_reports'
        ]);
    }

    public function test_admin_can_generate_system_report()
    {
        $reportData = [
            'type' => 'daily',
            'date' => now()->format('Y-m-d'),
            'include_performance' => true,
            'include_errors' => true,
            'include_security' => true
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/monitoring/generate-report', $reportData);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify report generation was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'system_report_generated'
        ]);
    }

    public function test_admin_can_view_system_metrics()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/metrics');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'uptime',
            'response_time',
            'throughput',
            'error_rate',
            'user_satisfaction'
        ]);
    }

    public function test_admin_can_view_network_status()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/network');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'bandwidth_usage',
            'connection_count',
            'latency',
            'packet_loss',
            'network_errors'
        ]);
    }

    public function test_admin_can_view_database_performance()
    {
        // Create some test data to generate queries
        User::factory()->count(5)->create();
        TravelRequest::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/database-performance');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'query_count',
            'slow_queries',
            'connection_pool',
            'index_usage',
            'table_sizes'
        ]);
    }

    public function test_admin_can_optimize_database()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/monitoring/optimize-database');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify optimization was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'database_optimized'
        ]);
    }

    public function test_admin_can_view_file_system_status()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/file-system');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'disk_usage',
            'file_count',
            'largest_files',
            'oldest_files',
            'file_types'
        ]);
    }

    public function test_admin_can_cleanup_temp_files()
    {
        Storage::fake('temp');
        
        // Create some temp files
        Storage::disk('temp')->put('temp1.txt', 'content1');
        Storage::disk('temp')->put('temp2.txt', 'content2');

        $response = $this->actingAs($this->admin)
            ->post('/admin/monitoring/cleanup-temp');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify cleanup was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'temp_files_cleaned'
        ]);
    }

    public function test_admin_can_view_system_updates()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/updates');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current_version',
            'latest_version',
            'update_available',
            'last_check',
            'changelog'
        ]);
    }

    public function test_admin_can_check_for_updates()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/monitoring/check-updates');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify update check was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'update_check_performed'
        ]);
    }

    public function test_admin_can_view_system_configuration()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/configuration');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'environment',
            'debug_mode',
            'cache_driver',
            'session_driver',
            'queue_driver',
            'mail_driver'
        ]);
    }

    public function test_admin_can_export_system_configuration()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monitoring/export-config');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        
        // Verify export was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'configuration_exported'
        ]);
    }

    public function test_admin_can_import_system_configuration()
    {
        $configData = [
            'cache_driver' => 'redis',
            'session_driver' => 'redis',
            'queue_driver' => 'redis'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/monitoring/import-config', $configData);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify import was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'configuration_imported'
        ]);
    }
}
