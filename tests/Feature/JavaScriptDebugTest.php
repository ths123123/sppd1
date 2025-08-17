<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JavaScriptDebugTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'role' => 'kasubbag'
        ]);
    }

    /** @test */
    public function dashboard_contains_required_javascript_files()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        
        // Check if required JS files are loaded
        $response->assertSee('charts.js');
        $response->assertSee('dashboard.js');
    }

    /** @test */
    public function dashboard_contains_activity_container()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        
        // Check if activity container exists
        $response->assertSee('recent-activities-container');
        $response->assertSee('Aktivitas Terbaru');
    }

    /** @test */
    public function dashboard_contains_load_recent_activities_function()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        
        // Check if JavaScript function is defined
        $response->assertSee('loadRecentActivities');
    }
}
