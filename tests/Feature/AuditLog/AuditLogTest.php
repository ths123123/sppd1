<?php

namespace Tests\Feature\AuditLog;

use Tests\TestCase;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AuditLogTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    /** @test */
    public function admin_can_view_audit_logs()
    {
        $response = $this->actingAs($this->admin)
            ->get('/audit-logs');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_filter_audit_logs_by_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get('/audit-logs?user_id=' . $user->id);

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_filter_audit_logs_by_action()
    {
        $response = $this->actingAs($this->admin)
            ->get('/audit-logs?action=login');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_filter_audit_logs_by_date_range()
    {
        $response = $this->actingAs($this->admin)
            ->get('/audit-logs?start_date=2024-01-01&end_date=2024-12-31');

        $response->assertStatus(200);
    }

    /** @test */
    public function regular_user_cannot_access_audit_logs()
    {
        $regularUser = User::factory()->create();

        $response = $this->actingAs($regularUser)
            ->get('/audit-logs');

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_audit_logs()
    {
        $response = $this->get('/audit-logs');
        $response->assertRedirect('/login');
    }
}
