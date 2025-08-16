<?php

namespace Tests\Feature\Role;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class RoleTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $approver;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->approver = User::factory()->approver()->create();
        $this->regularUser = User::factory()->create();
    }

    /** @test */
    public function admin_has_admin_role()
    {
        $this->assertTrue($this->admin->isAdmin());
        $this->assertFalse($this->admin->isApprover());
    }

    /** @test */
    public function approver_has_approver_role()
    {
        $this->assertTrue($this->approver->isApprover());
        $this->assertFalse($this->approver->isAdmin());
    }

    /** @test */
    public function regular_user_has_user_role()
    {
        $this->assertFalse($this->regularUser->isAdmin());
        $this->assertFalse($this->regularUser->isApprover());
    }

    /** @test */
    public function admin_can_access_admin_routes()
    {
        $response = $this->actingAs($this->admin)
            ->get('/user-management');

        $response->assertStatus(200);
    }

    /** @test */
    public function approver_can_access_approval_routes()
    {
        $response = $this->actingAs($this->approver)
            ->get('/approvals');

        $response->assertStatus(200);
    }

    /** @test */
    public function regular_user_cannot_access_admin_routes()
    {
        $response = $this->actingAs($this->regularUser)
            ->get('/user-management');

        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_access_approval_routes()
    {
        $response = $this->actingAs($this->regularUser)
            ->get('/approvals');

        $response->assertStatus(403);
    }
}
