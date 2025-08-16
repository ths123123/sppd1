<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_be_created()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
    }

    /** @test */
    public function user_has_default_role()
    {
        $user = User::factory()->create();

        $this->assertEquals('staff', $user->role);
    }

    /** @test */
    public function user_can_have_admin_role()
    {
        $user = User::factory()->admin()->create();

        $this->assertEquals('admin', $user->role);
    }

    /** @test */
    public function user_can_have_approver_role()
    {
        $user = User::factory()->approver()->create();

        $this->assertEquals('ppk', $user->role);
    }

    /** @test */
    public function user_can_check_if_is_admin()
    {
        $admin = User::factory()->admin()->create();
        $ppk = User::factory()->approver()->create();
        $regularUser = User::factory()->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($ppk->isAdmin()); // PPK juga dianggap admin
        $this->assertFalse($regularUser->isAdmin());
    }

    /** @test */
    public function user_can_check_if_can_approve()
    {
        $admin = User::factory()->admin()->create();
        $ppk = User::factory()->approver()->create();
        $regularUser = User::factory()->create();

        $this->assertTrue($admin->canApprove());
        $this->assertTrue($ppk->canApprove());
        $this->assertFalse($regularUser->canApprove());
    }

    /** @test */
    public function user_can_have_valid_roles()
    {
        $validRoles = ['admin', 'staff', 'kasubbag', 'sekretaris', 'ppk'];
        
        foreach ($validRoles as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->assertEquals($role, $user->role);
        }
    }

    /** @test */
    public function user_can_get_role_display_name()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->assertEquals('Admin', $user->getRoleDisplayName());

        $user = User::factory()->create(['role' => 'staff']);
        $this->assertEquals('Staff', $user->getRoleDisplayName());

        $user = User::factory()->create(['role' => 'ppk']);
        $this->assertEquals('PPK', $user->getRoleDisplayName());
    }
}
