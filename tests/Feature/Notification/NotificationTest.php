<?php

namespace Tests\Feature\Notification;

use Tests\TestCase;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class NotificationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function user_can_view_notifications()
    {
        $response = $this->actingAs($this->user)
            ->get('/notifications');

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_mark_notification_as_read()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => false
        ]);

        $response = $this->actingAs($this->user)
            ->patch("/notifications/{$notification->id}/read");

        $response->assertRedirect();
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true
        ]);
    }

    /** @test */
    public function user_can_mark_all_notifications_as_read()
    {
        // Create multiple unread notifications
        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'is_read' => false
        ]);

        $response = $this->actingAs($this->user)
            ->patch('/notifications/mark-all-read');

        $response->assertRedirect();
        
        // Check that all notifications are marked as read
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->user->id,
            'is_read' => false
        ]);
    }

    /** @test */
    public function user_can_delete_notification()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/notifications/{$notification->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id
        ]);
    }

    /** @test */
    public function user_cannot_access_other_user_notifications()
    {
        $otherUser = User::factory()->create();
        $notification = Notification::factory()->create([
            'user_id' => $otherUser->id
        ]);

        $response = $this->actingAs($this->user)
            ->get("/notifications/{$notification->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_notification_routes()
    {
        $response = $this->get('/notifications');
        $response->assertRedirect('/login');
    }
}
