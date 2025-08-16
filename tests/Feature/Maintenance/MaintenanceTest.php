<?php

namespace Tests\Feature\Maintenance;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Document;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class MaintenanceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->admin()->create();
    }

    /** @test */
    public function admin_can_enable_maintenance_mode()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/maintenance/enable');

        $response->assertRedirect('/admin/maintenance');
        $response->assertSessionHas('success');

        // Check if maintenance mode is enabled
        $setting = Setting::where('key', 'maintenance_mode')->first();
        $this->assertNotNull($setting);
        $this->assertEquals('true', $setting->value);
    }

    /** @test */
    public function admin_can_disable_maintenance_mode()
    {
        // Enable maintenance mode first
        $this->actingAs($this->admin)
            ->post('/admin/maintenance/enable');

        // Disable maintenance mode
        $response = $this->actingAs($this->admin)
            ->post('/admin/maintenance/disable');

        $response->assertRedirect('/admin/maintenance');
        $response->assertSessionHas('success');

        // Check if maintenance mode is disabled
        $setting = Setting::where('key', 'maintenance_mode')->first();
        $this->assertNotNull($setting);
        $this->assertEquals('false', $setting->value);
    }

    /** @test */
    public function maintenance_mode_blocks_regular_users()
    {
        // Enable maintenance mode
        $this->actingAs($this->admin)
            ->post('/admin/maintenance/enable');

        // Try to access application as regular user
        $response = $this->get('/');
        $response->assertStatus(503); // Service Unavailable

        // Check if maintenance page is shown
        $response->assertViewIs('maintenance');
    }

    /** @test */
    public function maintenance_mode_allows_admin_access()
    {
        // Enable maintenance mode
        $this->actingAs($this->admin)
            ->post('/admin/maintenance/enable');

        // Admin should still be able to access admin area
        $response = $this->actingAs($this->admin)
            ->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function maintenance_mode_logs_activity()
    {
        // Enable maintenance mode
        $this->actingAs($this->admin)
            ->post('/admin/maintenance/enable');

        // Check if activity is logged
        $activityLog = ActivityLog::where('user_id', $this->admin->id)
            ->where('action', 'enable_maintenance')
            ->first();

        $this->assertNotNull($activityLog);
    }

    /** @test */
    public function admin_can_view_maintenance_status()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/maintenance');

        $response->assertStatus(200);
        $response->assertViewIs('admin.maintenance.index');
    }

    /** @test */
    public function admin_can_schedule_maintenance()
    {
        $maintenanceData = [
            'scheduled_at' => now()->addHour(),
            'duration' => 60, // minutes
            'reason' => 'System update and maintenance'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/maintenance/schedule', $maintenanceData);

        $response->assertRedirect('/admin/maintenance');
        $response->assertSessionHas('success');

        // Check if maintenance is scheduled
        $setting = Setting::where('key', 'maintenance_scheduled_at')->first();
        $this->assertNotNull($setting);
    }

    /** @test */
    public function admin_can_cancel_scheduled_maintenance()
    {
        // Schedule maintenance first
        $this->actingAs($this->admin)
            ->post('/admin/maintenance/schedule', [
                'scheduled_at' => now()->addHour(),
                'duration' => 60,
                'reason' => 'System update'
            ]);

        // Cancel scheduled maintenance
        $response = $this->actingAs($this->admin)
            ->post('/admin/maintenance/cancel');

        $response->assertRedirect('/admin/maintenance');
        $response->assertSessionHas('success');

        // Check if maintenance is cancelled
        $setting = Setting::where('key', 'maintenance_scheduled_at')->first();
        $this->assertNull($setting);
    }

    /** @test */
    public function admin_can_create_system_backup()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/backup');

        $response->assertRedirect('/admin/backup');
        $response->assertSessionHas('success');

        // Check if backup activity is logged
        $activityLog = ActivityLog::where('user_id', $this->admin->id)
            ->where('action', 'create_backup')
            ->first();

        $this->assertNotNull($activityLog);
    }

    /** @test */
    public function admin_can_view_backup_status()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/backup');

        $response->assertStatus(200);
        $response->assertViewIs('admin.backup.index');
    }

    /** @test */
    public function admin_can_download_backup()
    {
        // Create backup first
        $this->actingAs($this->admin)
            ->post('/admin/backup');

        // Download backup
        $response = $this->actingAs($this->admin)
            ->get('/admin/backup/download/latest');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/zip');
    }

    /** @test */
    public function admin_can_restore_from_backup()
    {
        // Create backup first
        $this->actingAs($this->admin)
            ->post('/admin/backup');

        // Restore from backup
        $response = $this->actingAs($this->admin)
            ->post('/admin/backup/restore', [
                'backup_file' => 'backup_2024_01_01.zip'
            ]);

        $response->assertRedirect('/admin/backup');
        $response->assertSessionHas('success');

        // Check if restore activity is logged
        $activityLog = ActivityLog::where('user_id', $this->admin->id)
            ->where('action', 'restore_backup')
            ->first();

        $this->assertNotNull($activityLog);
    }

    /** @test */
    public function admin_can_delete_old_backups()
    {
        // Create multiple backups
        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($this->admin)
                ->post('/admin/backup');
        }

        // Delete old backups
        $response = $this->actingAs($this->admin)
            ->delete('/admin/backup/cleanup');

        $response->assertRedirect('/admin/backup');
        $response->assertSessionHas('success');
    }

    /** @test */
    public function admin_can_view_system_health()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/system/health');

        $response->assertStatus(200);
        $response->assertViewIs('admin.system.health');
    }

    /** @test */
    public function admin_can_view_database_status()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/system/database');

        $response->assertStatus(200);
        $response->assertViewIs('admin.system.database');
    }

    /** @test */
    public function admin_can_optimize_database()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/system/database/optimize');

        $response->assertRedirect('/admin/system/database');
        $response->assertSessionHas('success');

        // Check if optimization activity is logged
        $activityLog = ActivityLog::where('user_id', $this->admin->id)
            ->where('action', 'optimize_database')
            ->first();

        $this->assertNotNull($activityLog);
    }

    /** @test */
    public function admin_can_repair_database()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/system/database/repair');

        $response->assertRedirect('/admin/system/database');
        $response->assertSessionHas('success');

        // Check if repair activity is logged
        $activityLog = ActivityLog::where('user_id', $this->admin->id)
            ->where('action', 'repair_database')
            ->first();

        $this->assertNotNull($activityLog);
    }

    /** @test */
    public function admin_can_view_storage_status()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/system/storage');

        $response->assertStatus(200);
        $response->assertViewIs('admin.system.storage');
    }

    /** @test */
    public function admin_can_cleanup_storage()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/system/storage/cleanup');

        $response->assertRedirect('/admin/system/storage');
        $response->assertSessionHas('success');

        // Check if cleanup activity is logged
        $activityLog = ActivityLog::where('user_id', $this->admin->id)
            ->where('action', 'cleanup_storage')
            ->first();

        $this->assertNotNull($activityLog);
    }

    /** @test */
    public function admin_can_view_log_files()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/system/logs');

        $response->assertStatus(200);
        $response->assertViewIs('admin.system.logs');
    }

    /** @test */
    public function admin_can_clear_log_files()
    {
        $response = $this->actingAs($this->admin)
            ->delete('/admin/system/logs/clear');

        $response->assertRedirect('/admin/system/logs');
        $response->assertSessionHas('success');

        // Check if log clearing activity is logged
        $activityLog = ActivityLog::where('user_id', $this->admin->id)
            ->where('action', 'clear_logs')
            ->first();

        $this->assertNotNull($activityLog);
    }

    /** @test */
    public function admin_can_view_cache_status()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/system/cache');

        $response->assertStatus(200);
        $response->assertViewIs('admin.system.cache');
    }

    /** @test */
    public function admin_can_clear_cache()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/system/cache/clear');

        $response->assertRedirect('/admin/system/cache');
        $response->assertSessionHas('success');

        // Check if cache clearing activity is logged
        $activityLog = ActivityLog::where('user_id', $this->admin->id)
            ->where('action', 'clear_cache')
            ->first();

        $this->assertNotNull($activityLog);
    }

    /** @test */
    public function admin_can_view_queue_status()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/system/queue');

        $response->assertStatus(200);
        $response->assertViewIs('admin.system.queue');
    }

    /** @test */
    public function admin_can_restart_queue_workers()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/system/queue/restart');

        $response->assertRedirect('/admin/system/queue');
        $response->assertSessionHas('success');

        // Check if queue restart activity is logged
        $activityLog = ActivityLog::where('user_id', $this->admin->id)
            ->where('action', 'restart_queue')
            ->first();

        $this->assertNotNull($activityLog);
    }

    /** @test */
    public function admin_can_view_system_performance()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/system/performance');

        $response->assertStatus(200);
        $response->assertViewIs('admin.system.performance');
    }

    /** @test */
    public function admin_can_run_system_diagnostics()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/system/diagnostics');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'database',
            'storage',
            'cache',
            'queue',
            'mail',
            'overall_status'
        ]);

        // Check if diagnostics activity is logged
        $activityLog = ActivityLog::where('user_id', $this->admin->id)
            ->where('action', 'run_diagnostics')
            ->first();

        $this->assertNotNull($activityLog);
    }

    /** @test */
    public function admin_can_view_system_updates()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/system/updates');

        $response->assertStatus(200);
        $response->assertViewIs('admin.system.updates');
    }

    /** @test */
    public function admin_can_check_for_updates()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/system/updates/check');

        $response->assertRedirect('/admin/system/updates');
        $response->assertSessionHas('success');

        // Check if update check activity is logged
        $activityLog = ActivityLog::where('user_id', $this->admin->id)
            ->where('action', 'check_updates')
            ->first();

        $this->assertNotNull($activityLog);
    }

    /** @test */
    public function admin_can_view_system_configuration()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/system/configuration');

        $response->assertStatus(200);
        $response->assertViewIs('admin.system.configuration');
    }

    /** @test */
    public function admin_can_export_system_configuration()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/system/configuration/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
    }

    /** @test */
    public function admin_can_import_system_configuration()
    {
        $configData = [
            'app_name' => 'SPPD System',
            'app_env' => 'testing',
            'app_debug' => false
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/system/configuration/import', [
                'configuration' => json_encode($configData)
            ]);

        $response->assertRedirect('/admin/system/configuration');
        $response->assertSessionHas('success');
    }

    /** @test */
    public function admin_can_view_system_monitoring()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/system/monitoring');

        $response->assertStatus(200);
        $response->assertViewIs('admin.system.monitoring');
    }

    /** @test */
    public function admin_can_set_system_alerts()
    {
        $alertData = [
            'type' => 'disk_space',
            'threshold' => 80,
            'enabled' => true
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/system/alerts', $alertData);

        $response->assertRedirect('/admin/system/monitoring');
        $response->assertSessionHas('success');

        // Check if alert setting is saved
        $setting = Setting::where('key', 'alert_disk_space_threshold')->first();
        $this->assertNotNull($setting);
        $this->assertEquals('80', $setting->value);
    }

    /** @test */
    public function admin_can_view_system_reports()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/system/reports');

        $response->assertStatus(200);
        $response->assertViewIs('admin.system.reports');
    }

    /** @test */
    public function admin_can_generate_system_report()
    {
        $reportData = [
            'type' => 'system_health',
            'period' => 'monthly',
            'format' => 'pdf'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/system/reports/generate', $reportData);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');

        // Check if report generation is logged
        $activityLog = ActivityLog::where('user_id', $this->admin->id)
            ->where('action', 'generate_system_report')
            ->first();

        $this->assertNotNull($activityLog);
    }

    /** @test */
    public function admin_can_view_backup_schedule()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/backup/schedule');

        $response->assertStatus(200);
        $response->assertViewIs('admin.backup.schedule');
    }

    /** @test */
    public function admin_can_set_backup_schedule()
    {
        $scheduleData = [
            'frequency' => 'daily',
            'time' => '02:00',
            'retention_days' => 30,
            'enabled' => true
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/backup/schedule', $scheduleData);

        $response->assertRedirect('/admin/backup/schedule');
        $response->assertSessionHas('success');

        // Check if backup schedule is saved
        $setting = Setting::where('key', 'backup_schedule_frequency')->first();
        $this->assertNotNull($setting);
        $this->assertEquals('daily', $setting->value);
    }

    /** @test */
    public function admin_can_test_backup_configuration()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/backup/test');

        $response->assertRedirect('/admin/backup');
        $response->assertSessionHas('success');

        // Check if backup test is logged
        $activityLog = ActivityLog::where('user_id', $this->admin->id)
            ->where('action', 'test_backup')
            ->first();

        $this->assertNotNull($activityLog);
    }

    /** @test */
    public function admin_can_view_backup_logs()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/backup/logs');

        $response->assertStatus(200);
        $response->assertViewIs('admin.backup.logs');
    }

    /** @test */
    public function admin_can_restore_specific_backup()
    {
        // Create backup first
        $this->actingAs($this->admin)
            ->post('/admin/backup');

        // Get backup list
        $response = $this->actingAs($this->admin)
            ->get('/admin/backup/list');

        $response->assertStatus(200);
        $response->assertJsonStructure(['backups']);

        // Restore specific backup
        $backupFile = 'backup_2024_01_01.zip';
        $response = $this->actingAs($this->admin)
            ->post('/admin/backup/restore', [
                'backup_file' => $backupFile
            ]);

        $response->assertRedirect('/admin/backup');
        $response->assertSessionHas('success');
    }

    /** @test */
    public function admin_can_verify_backup_integrity()
    {
        // Create backup first
        $this->actingAs($this->admin)
            ->post('/admin/backup');

        // Verify backup integrity
        $response = $this->actingAs($this->admin)
            ->post('/admin/backup/verify', [
                'backup_file' => 'backup_2024_01_01.zip'
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['integrity_check', 'status']);

        // Check if verification is logged
        $activityLog = ActivityLog::where('user_id', $this->admin->id)
            ->where('action', 'verify_backup')
            ->first();

        $this->assertNotNull($activityLog);
    }
}
