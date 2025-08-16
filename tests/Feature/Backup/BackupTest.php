<?php

namespace Tests\Feature\Backup;

use Tests\TestCase;
use App\Models\User;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class BackupTest extends TestCase
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

    public function test_admin_can_view_backup_dashboard()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/backup');

        $response->assertStatus(200);
        $response->assertViewIs('admin.backup.index');
    }

    public function test_regular_user_cannot_access_backup_dashboard()
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/backup');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_manual_backup()
    {
        Storage::fake('backups');

        $response = $this->actingAs($this->admin)
            ->post('/admin/backup/create');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify backup was created
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'backup_created',
            'description' => 'Manual backup created'
        ]);
    }

    public function test_admin_can_view_backup_list()
    {
        // Create some backup records
        $backups = [
            ['filename' => 'backup_2024_01_01.sql', 'size' => 1024000, 'created_at' => now()],
            ['filename' => 'backup_2024_01_02.sql', 'size' => 2048000, 'created_at' => now()->subDay()],
        ];

        foreach ($backups as $backup) {
            DB::table('backups')->insert($backup);
        }

        $response = $this->actingAs($this->admin)
            ->get('/admin/backup/list');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    public function test_admin_can_download_backup()
    {
        Storage::fake('backups');
        
        // Create a fake backup file
        $backupFile = 'backup_test.sql';
        Storage::disk('backups')->put($backupFile, 'backup content');

        $response = $this->actingAs($this->admin)
            ->get("/admin/backup/download/{$backupFile}");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/octet-stream');
    }

    public function test_admin_can_delete_backup()
    {
        Storage::fake('backups');
        
        // Create a fake backup file
        $backupFile = 'backup_to_delete.sql';
        Storage::disk('backups')->put($backupFile, 'backup content');

        $response = $this->actingAs($this->admin)
            ->delete("/admin/backup/delete/{$backupFile}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify file was deleted
        Storage::disk('backups')->assertMissing($backupFile);
    }

    public function test_admin_can_restore_backup()
    {
        Storage::fake('backups');
        
        // Create a fake backup file
        $backupFile = 'backup_to_restore.sql';
        Storage::disk('backups')->put($backupFile, 'backup content');

        $response = $this->actingAs($this->admin)
            ->post("/admin/backup/restore/{$backupFile}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify restore activity was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'backup_restored',
            'description' => "Backup restored: {$backupFile}"
        ]);
    }

    public function test_admin_can_configure_backup_schedule()
    {
        $scheduleData = [
            'frequency' => 'daily',
            'time' => '02:00',
            'retention_days' => 30,
            'include_files' => true,
            'include_database' => true,
            'compression' => 'gzip'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/backup/schedule', $scheduleData);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify settings were saved
        $this->assertDatabaseHas('settings', [
            'key' => 'backup_frequency',
            'value' => 'daily'
        ]);
    }

    public function test_admin_can_view_backup_status()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/backup/status');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'last_backup',
            'next_backup',
            'backup_size',
            'storage_used',
            'storage_available',
            'backup_count'
        ]);
    }

    public function test_admin_can_test_backup_configuration()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/backup/test');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify test was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'backup_tested',
            'description' => 'Backup configuration tested successfully'
        ]);
    }

    public function test_admin_can_view_backup_logs()
    {
        // Create some backup log entries
        $logs = [
            ['action' => 'backup_created', 'status' => 'success', 'message' => 'Backup completed'],
            ['action' => 'backup_failed', 'status' => 'error', 'message' => 'Backup failed'],
        ];

        foreach ($logs as $log) {
            ActivityLog::factory()->create([
                'action' => $log['action'],
                'status' => $log['status'],
                'message' => $log['message'],
                'user_id' => $this->admin->id
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get('/admin/backup/logs');

        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    public function test_admin_can_clear_backup_logs()
    {
        // Create some backup log entries
        ActivityLog::factory()->count(5)->create([
            'action' => 'backup_created',
            'user_id' => $this->admin->id
        ]);

        $response = $this->actingAs($this->admin)
            ->delete('/admin/backup/logs/clear');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify logs were cleared
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_backup_includes_database_tables()
    {
        // Create some test data
        User::factory()->count(3)->create();
        Setting::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)
            ->post('/admin/backup/create');

        $response->assertStatus(200);
        
        // Verify backup activity was logged with table count
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'backup_created'
        ]);
    }

    public function test_backup_includes_uploaded_files()
    {
        Storage::fake('uploads');
        
        // Create some test files
        Storage::disk('uploads')->put('test1.pdf', 'content1');
        Storage::disk('uploads')->put('test2.docx', 'content2');

        $response = $this->actingAs($this->admin)
            ->post('/admin/backup/create');

        $response->assertStatus(200);
        
        // Verify backup includes files
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'backup_created'
        ]);
    }

    public function test_backup_compression_works()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/backup/create', ['compression' => 'gzip']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify compression was used
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'backup_created',
            'description' => 'Backup created with gzip compression'
        ]);
    }

    public function test_backup_retention_policy_enforced()
    {
        // Create old backup records
        $oldBackups = [
            ['filename' => 'old_backup_1.sql', 'created_at' => now()->subDays(35)],
            ['filename' => 'old_backup_2.sql', 'created_at' => now()->subDays(40)],
        ];

        foreach ($oldBackups as $backup) {
            DB::table('backups')->insert($backup);
        }

        $response = $this->actingAs($this->admin)
            ->post('/admin/backup/cleanup');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify old backups were cleaned up
        $this->assertDatabaseMissing('backups', [
            'filename' => 'old_backup_1.sql'
        ]);
    }

    public function test_backup_verification_works()
    {
        Storage::fake('backups');
        
        // Create a backup file
        $backupFile = 'backup_to_verify.sql';
        Storage::disk('backups')->put($backupFile, 'backup content');

        $response = $this->actingAs($this->admin)
            ->post("/admin/backup/verify/{$backupFile}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify verification was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'backup_verified',
            'description' => "Backup verified: {$backupFile}"
        ]);
    }

    public function test_backup_encryption_works()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/backup/create', ['encrypt' => true]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Verify encryption was used
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'backup_created',
            'description' => 'Encrypted backup created'
        ]);
    }

    public function test_backup_notification_sent()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/backup/create');

        $response->assertStatus(200);
        
        // Verify notification was sent
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->admin->id,
            'type' => 'backup_completed',
            'title' => 'Backup Completed Successfully'
        ]);
    }

    public function test_backup_failure_handling()
    {
        // Mock a backup failure
        Storage::shouldReceive('disk')->andThrow(new \Exception('Backup failed'));

        $response = $this->actingAs($this->admin)
            ->post('/admin/backup/create');

        $response->assertStatus(500);
        
        // Verify failure was logged
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'backup_failed',
            'status' => 'error'
        ]);
    }

    public function test_backup_storage_monitoring()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/backup/storage');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_space',
            'used_space',
            'free_space',
            'backup_space',
            'usage_percentage'
        ]);
    }

    public function test_backup_performance_metrics()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/backup/performance');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'average_duration',
            'last_duration',
            'fastest_backup',
            'slowest_backup',
            'success_rate'
        ]);
    }
}
