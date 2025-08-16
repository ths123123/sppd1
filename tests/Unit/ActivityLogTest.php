<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function activity_log_can_be_created()
    {
        $activityLog = ActivityLog::factory()->create([
            'user_id' => $this->user->id,
            'action' => 'login',
            'description' => 'User logged in'
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'action' => 'login',
            'description' => 'User logged in'
        ]);
    }

    /** @test */
    public function activity_log_has_default_action()
    {
        $activityLog = ActivityLog::factory()->create();

        $this->assertContains($activityLog->action, ['login', 'logout', 'create', 'update', 'delete', 'export', 'import']);
    }

    /** @test */
    public function activity_log_can_have_login_action()
    {
        $activityLog = ActivityLog::factory()->login()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertEquals('login', $activityLog->action);
        $this->assertEquals('User logged in successfully', $activityLog->description);
        $this->assertEquals('POST', $activityLog->method);
        $this->assertEquals(200, $activityLog->status_code);
    }

    /** @test */
    public function activity_log_can_have_logout_action()
    {
        $activityLog = ActivityLog::factory()->logout()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertEquals('logout', $activityLog->action);
        $this->assertEquals('User logged out', $activityLog->description);
        $this->assertEquals('POST', $activityLog->method);
        $this->assertEquals(200, $activityLog->status_code);
    }

    /** @test */
    public function activity_log_can_have_create_action()
    {
        $activityLog = ActivityLog::factory()->create()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertEquals('create', $activityLog->action);
        $this->assertEquals('Record created successfully', $activityLog->description);
        $this->assertEquals('POST', $activityLog->method);
        $this->assertEquals(201, $activityLog->status_code);
    }

    /** @test */
    public function activity_log_can_have_update_action()
    {
        $activityLog = ActivityLog::factory()->update()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertEquals('update', $activityLog->action);
        $this->assertEquals('Record updated successfully', $activityLog->description);
        $this->assertEquals('PUT', $activityLog->method);
        $this->assertEquals(200, $activityLog->status_code);
    }

    /** @test */
    public function activity_log_can_have_delete_action()
    {
        $activityLog = ActivityLog::factory()->delete()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertEquals('delete', $activityLog->action);
        $this->assertEquals('Record deleted successfully', $activityLog->description);
        $this->assertEquals('DELETE', $activityLog->method);
        $this->assertEquals(200, $activityLog->status_code);
    }

    /** @test */
    public function activity_log_can_have_export_action()
    {
        $activityLog = ActivityLog::factory()->export()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertEquals('export', $activityLog->action);
        $this->assertEquals('Data exported successfully', $activityLog->description);
        $this->assertEquals('GET', $activityLog->method);
        $this->assertEquals(200, $activityLog->status_code);
    }

    /** @test */
    public function activity_log_can_have_import_action()
    {
        $activityLog = ActivityLog::factory()->import()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertEquals('import', $activityLog->action);
        $this->assertEquals('Data imported successfully', $activityLog->description);
        $this->assertEquals('POST', $activityLog->method);
        $this->assertEquals(200, $activityLog->status_code);
    }

    /** @test */
    public function activity_log_belongs_to_user()
    {
        $activityLog = ActivityLog::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertInstanceOf(User::class, $activityLog->user);
        $this->assertEquals($this->user->id, $activityLog->user->id);
    }
}
