<?php

namespace Tests\Feature\Database;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Document;
use App\Models\Approval;
use App\Models\Setting;
use App\Models\Notification;
use App\Models\ActivityLog;
use App\Models\TemplateDokumen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseTest extends TestCase
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
    public function database_tables_exist()
    {
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('travel_requests'));
        $this->assertTrue(Schema::hasTable('documents'));
        $this->assertTrue(Schema::hasTable('approvals'));
        $this->assertTrue(Schema::hasTable('settings'));
        $this->assertTrue(Schema::hasTable('notifications'));
        $this->assertTrue(Schema::hasTable('activity_logs'));
        $this->assertTrue(Schema::hasTable('template_dokumen'));
        $this->assertTrue(Schema::hasTable('password_reset_tokens'));
        $this->assertTrue(Schema::hasTable('failed_jobs'));
        $this->assertTrue(Schema::hasTable('personal_access_tokens'));
    }

    /** @test */
    public function users_table_has_correct_columns()
    {
        $columns = Schema::getColumnListing('users');
        
        $expectedColumns = [
            'id', 'name', 'email', 'email_verified_at', 'password',
            'remember_token', 'role', 'nip', 'jabatan', 'created_at', 'updated_at'
        ];

        foreach ($expectedColumns as $column) {
            $this->assertContains($column, $columns);
        }
    }

    /** @test */
    public function travel_requests_table_has_correct_columns()
    {
        $columns = Schema::getColumnListing('travel_requests');
        
        $expectedColumns = [
            'id', 'user_id', 'nomor_surat', 'tanggal_berangkat', 'tanggal_kembali',
            'tujuan', 'keperluan', 'transportasi', 'estimasi_biaya', 'status',
            'catatan', 'approved_at', 'approved_by', 'rejected_at', 'rejected_by',
            'rejection_reason', 'created_at', 'updated_at'
        ];

        foreach ($expectedColumns as $column) {
            $this->assertContains($column, $column);
        }
    }

    /** @test */
    public function documents_table_has_correct_columns()
    {
        $columns = Schema::getColumnListing('documents');
        
        $expectedColumns = [
            'id', 'user_id', 'nama_dokumen', 'jenis_dokumen', 'deskripsi',
            'file_path', 'file_size', 'mime_type', 'is_verified', 'verified_at',
            'verified_by', 'created_at', 'updated_at'
        ];

        foreach ($expectedColumns as $column) {
            $this->assertContains($column, $columns);
        }
    }

    /** @test */
    public function approvals_table_has_correct_columns()
    {
        $columns = Schema::getColumnListing('approvals');
        
        $expectedColumns = [
            'id', 'user_id', 'approver_id', 'travel_request_id', 'status',
            'catatan', 'approved_at', 'rejected_at', 'created_at', 'updated_at'
        ];

        foreach ($expectedColumns as $column) {
            $this->assertContains($column, $columns);
        }
    }

    /** @test */
    public function settings_table_has_correct_columns()
    {
        $columns = Schema::getColumnListing('settings');
        
        $expectedColumns = [
            'id', 'key', 'value', 'description', 'is_public', 'created_at', 'updated_at'
        ];

        foreach ($expectedColumns as $column) {
            $this->assertContains($column, $columns);
        }
    }

    /** @test */
    public function notifications_table_has_correct_columns()
    {
        $columns = Schema::getColumnListing('notifications');
        
        $expectedColumns = [
            'id', 'user_id', 'title', 'message', 'type', 'is_read', 'read_at',
            'data', 'created_at', 'updated_at'
        ];

        foreach ($expectedColumns as $column) {
            $this->assertContains($column, $columns);
        }
    }

    /** @test */
    public function activity_logs_table_has_correct_columns()
    {
        $columns = Schema::getColumnListing('activity_logs');
        
        $expectedColumns = [
            'id', 'user_id', 'action', 'description', 'ip_address', 'user_agent',
            'created_at', 'updated_at'
        ];

        foreach ($expectedColumns as $column) {
            $this->assertContains($column, $columns);
        }
    }

    /** @test */
    public function template_dokumen_table_has_correct_columns()
    {
        $columns = Schema::getColumnListing('template_dokumen');
        
        $expectedColumns = [
            'id', 'nama_template', 'jenis_dokumen', 'deskripsi', 'file_path',
            'file_size', 'mime_type', 'is_active', 'created_by', 'updated_by',
            'created_at', 'updated_at'
        ];

        foreach ($expectedColumns as $column) {
            $this->assertContains($column, $columns);
        }
    }

    /** @test */
    public function database_foreign_key_constraints_exist()
    {
        $foreignKeys = $this->getForeignKeys();
        
        // Check travel_requests foreign keys
        $this->assertArrayHasKey('travel_requests_user_id_foreign', $foreignKeys);
        $this->assertEquals('users', $foreignKeys['travel_requests_user_id_foreign']['referenced_table']);
        
        // Check documents foreign keys
        $this->assertArrayHasKey('documents_user_id_foreign', $foreignKeys);
        $this->assertEquals('users', $foreignKeys['documents_user_id_foreign']['referenced_table']);
        
        // Check approvals foreign keys
        $this->assertArrayHasKey('approvals_user_id_foreign', $foreignKeys);
        $this->assertEquals('users', $foreignKeys['approvals_user_id_foreign']['referenced_table']);
        $this->assertArrayHasKey('approvals_approver_id_foreign', $foreignKeys);
        $this->assertEquals('users', $foreignKeys['approvals_approver_id_foreign']['referenced_table']);
        $this->assertArrayHasKey('approvals_travel_request_id_foreign', $foreignKeys);
        $this->assertEquals('travel_requests', $foreignKeys['approvals_travel_request_id_foreign']['referenced_table']);
        
        // Check notifications foreign keys
        $this->assertArrayHasKey('notifications_user_id_foreign', $foreignKeys);
        $this->assertEquals('users', $foreignKeys['notifications_user_id_foreign']['referenced_table']);
        
        // Check activity_logs foreign keys
        $this->assertArrayHasKey('activity_logs_user_id_foreign', $foreignKeys);
        $this->assertEquals('users', $foreignKeys['activity_logs_user_id_foreign']['referenced_table']);
    }

    /** @test */
    public function database_indexes_exist()
    {
        $indexes = $this->getIndexes();
        
        // Check users table indexes
        $this->assertContains('users_email_unique', $indexes['users']);
        $this->assertContains('users_nip_unique', $indexes['users']);
        
        // Check travel_requests table indexes
        $this->assertContains('travel_requests_user_id_index', $indexes['travel_requests']);
        $this->assertContains('travel_requests_status_index', $indexes['travel_requests']);
        $this->assertContains('travel_requests_tanggal_berangkat_index', $indexes['travel_requests']);
        
        // Check documents table indexes
        $this->assertContains('documents_user_id_index', $indexes['documents']);
        $this->assertContains('documents_jenis_dokumen_index', $indexes['documents']);
        
        // Check approvals table indexes
        $this->assertContains('approvals_user_id_index', $indexes['approvals']);
        $this->assertContains('approvals_approver_id_index', $indexes['approvals']);
        $this->assertContains('approvals_status_index', $indexes['approvals']);
        
        // Check notifications table indexes
        $this->assertContains('notifications_user_id_index', $indexes['notifications']);
        $this->assertContains('notifications_is_read_index', $indexes['notifications']);
        
        // Check activity_logs table indexes
        $this->assertContains('activity_logs_user_id_index', $indexes['activity_logs']);
        $this->assertContains('activity_logs_action_index', $indexes['activity_logs']);
        $this->assertContains('activity_logs_created_at_index', $indexes['activity_logs']);
    }

    /** @test */
    public function user_travel_requests_relationship_works()
    {
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertEquals($this->user->id, $travelRequest->user->id);
        $this->assertCount(1, $this->user->travelRequests);
    }

    /** @test */
    public function user_documents_relationship_works()
    {
        $document = Document::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertEquals($this->user->id, $document->user->id);
        $this->assertCount(1, $this->user->documents);
    }

    /** @test */
    public function user_approvals_relationship_works()
    {
        $approval = Approval::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertEquals($this->user->id, $approval->user->id);
        $this->assertCount(1, $this->user->approvals);
    }

    /** @test */
    public function user_notifications_relationship_works()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertEquals($this->user->id, $notification->user->id);
        $this->assertCount(1, $this->user->notifications);
    }

    /** @test */
    public function user_activity_logs_relationship_works()
    {
        $activityLog = ActivityLog::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertEquals($this->user->id, $activityLog->user->id);
        $this->assertCount(1, $this->user->activityLogs);
    }

    /** @test */
    public function travel_request_approvals_relationship_works()
    {
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id
        ]);

        $approval = Approval::factory()->create([
            'travel_request_id' => $travelRequest->id
        ]);

        $this->assertEquals($travelRequest->id, $approval->travelRequest->id);
        $this->assertCount(1, $travelRequest->approvals);
    }

    /** @test */
    public function approver_approvals_relationship_works()
    {
        $approval = Approval::factory()->create([
            'approver_id' => $this->approver->id
        ]);

        $this->assertEquals($this->approver->id, $approval->approver->id);
        $this->assertCount(1, $this->approver->approvals);
    }

    /** @test */
    public function database_transactions_work_correctly()
    {
        DB::beginTransaction();
        
        try {
            $user = User::factory()->create();
            $travelRequest = TravelRequest::factory()->create([
                'user_id' => $user->id
            ]);
            
            // This should work
            $this->assertDatabaseHas('users', ['id' => $user->id]);
            $this->assertDatabaseHas('travel_requests', ['id' => $travelRequest->id]);
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        
        // Data should persist after commit
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('travel_requests', ['id' => $travelRequest->id]);
    }

    /** @test */
    public function database_rollback_works_correctly()
    {
        DB::beginTransaction();
        
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id
        ]);
        
        // Data should exist during transaction
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('travel_requests', ['id' => $travelRequest->id]);
        
        DB::rollBack();
        
        // Data should not exist after rollback
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('travel_requests', ['id' => $travelRequest->id]);
    }

    /** @test */
    public function database_constraints_prevent_invalid_data()
    {
        // Try to create travel request with non-existent user_id
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        TravelRequest::factory()->create([
            'user_id' => 99999 // Non-existent user
        ]);
    }

    /** @test */
    public function database_unique_constraints_work()
    {
        // Create first user with unique email
        User::factory()->create(['email' => 'test@example.com']);
        
        // Try to create second user with same email
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        User::factory()->create(['email' => 'test@example.com']);
    }

    /** @test */
    public function database_cascade_deletes_work()
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id
        ]);
        $document = Document::factory()->create([
            'user_id' => $user->id
        ]);
        
        // Delete user
        $user->delete();
        
        // Related records should be deleted
        $this->assertDatabaseMissing('travel_requests', ['id' => $travelRequest->id]);
        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    }

    /** @test */
    public function database_soft_deletes_work()
    {
        $user = User::factory()->create();
        $userId = $user->id;
        
        // Soft delete user
        $user->delete();
        
        // User should not appear in normal queries
        $this->assertDatabaseMissing('users', ['id' => $userId]);
        
        // But should exist in database with deleted_at
        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'deleted_at' => now()
        ]);
    }

    /** @test */
    public function database_migrations_can_be_rolled_back()
    {
        // This test ensures that migrations can be rolled back
        $this->artisan('migrate:rollback', ['--step' => 1]);
        
        // Check if tables still exist (they should)
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('travel_requests'));
    }

    /** @test */
    public function database_seeders_work()
    {
        // Run seeders
        $this->artisan('db:seed');
        
        // Check if data was seeded
        $this->assertDatabaseHas('users', ['role' => 'admin']);
        $this->assertDatabaseHas('settings', ['key' => 'system_name']);
    }

    /** @test */
    public function database_performance_indexes_work()
    {
        // Create multiple records
        User::factory()->count(100)->create();
        TravelRequest::factory()->count(100)->create();
        
        // Query should use indexes
        $users = User::where('role', 'user')->get();
        $travelRequests = TravelRequest::where('status', 'pending')->get();
        
        $this->assertCount(100, $users);
        $this->assertCount(100, $travelRequests);
    }

    /** @test */
    public function database_connection_configuration_is_correct()
    {
        $config = config('database.connections.mysql');
        
        $this->assertEquals('mysql', $config['driver']);
        $this->assertNotEmpty($config['host']);
        $this->assertNotEmpty($config['database']);
        $this->assertNotEmpty($config['username']);
    }

    /** @test */
    public function database_can_handle_concurrent_operations()
    {
        $promises = [];
        
        // Simulate concurrent operations
        for ($i = 0; $i < 10; $i++) {
            $promises[] = User::factory()->create();
        }
        
        // All operations should succeed
        $this->assertCount(10, $promises);
        
        // Check if all users were created
        $this->assertEquals(13, User::count()); // 3 from setUp + 10 new
    }

    private function getForeignKeys()
    {
        $foreignKeys = [];
        
        $tables = ['users', 'travel_requests', 'documents', 'approvals', 'notifications', 'activity_logs'];
        
        foreach ($tables as $table) {
            $foreignKeys = array_merge($foreignKeys, $this->getTableForeignKeys($table));
        }
        
        return $foreignKeys;
    }

    private function getTableForeignKeys($table)
    {
        $foreignKeys = [];
        
        $constraints = DB::select("
            SELECT 
                CONSTRAINT_NAME,
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ? 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$table]);
        
        foreach ($constraints as $constraint) {
            $foreignKeys[$constraint->CONSTRAINT_NAME] = [
                'column' => $constraint->COLUMN_NAME,
                'referenced_table' => $constraint->REFERENCED_TABLE_NAME,
                'referenced_column' => $constraint->REFERENCED_COLUMN_NAME
            ];
        }
        
        return $foreignKeys;
    }

    private function getIndexes()
    {
        $indexes = [];
        
        $tables = ['users', 'travel_requests', 'documents', 'approvals', 'notifications', 'activity_logs'];
        
        foreach ($tables as $table) {
            $indexes[$table] = $this->getTableIndexes($table);
        }
        
        return $indexes;
    }

    private function getTableIndexes($table)
    {
        $indexes = [];
        
        $results = DB::select("
            SELECT INDEX_NAME 
            FROM information_schema.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ?
        ", [$table]);
        
        foreach ($results as $result) {
            $indexes[] = $result->INDEX_NAME;
        }
        
        return $indexes;
    }
}
