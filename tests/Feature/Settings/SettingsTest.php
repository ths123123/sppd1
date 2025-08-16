<?php

namespace Tests\Feature\Settings;

use Tests\TestCase;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class SettingsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    /** @test */
    public function admin_can_view_settings()
    {
        $response = $this->actingAs($this->admin)
            ->get('/settings');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_update_system_settings()
    {
        $setting = Setting::factory()->create([
            'key' => 'system_name',
            'value' => 'Old System Name'
        ]);

        $updateData = [
            'system_name' => 'New System Name',
            'system_description' => 'New System Description'
        ];

        $response = $this->actingAs($this->admin)
            ->patch('/settings', $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('settings', [
            'key' => 'system_name',
            'value' => 'New System Name'
        ]);
    }

    /** @test */
    public function regular_user_cannot_access_settings()
    {
        $regularUser = User::factory()->create();

        $response = $this->actingAs($regularUser)
            ->get('/settings');

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_settings()
    {
        $response = $this->get('/settings');
        $response->assertRedirect('/login');
    }
}
