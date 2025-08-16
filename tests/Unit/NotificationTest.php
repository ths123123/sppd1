<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function notification_can_be_created()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Notification',
            'message' => 'This is a test notification'
        ]);

        $this->assertDatabaseHas('notifications', [
            'title' => 'Test Notification',
            'message' => 'This is a test notification'
        ]);
    }

    /** @test */
    public function notification_has_default_type()
    {
        $notification = Notification::factory()->create();

        $this->assertContains($notification->type, ['info', 'success', 'warning', 'error']);
    }

    /** @test */
    public function notification_is_unread_by_default()
    {
        $notification = Notification::factory()->create();

        $this->assertFalse($notification->is_read);
        $this->assertNull($notification->read_at);
    }

    /** @test */
    public function notification_can_be_marked_as_read()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => false
        ]);

        $notification->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        $this->assertTrue($notification->fresh()->is_read);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    /** @test */
    public function notification_can_have_specific_type()
    {
        $infoNotification = Notification::factory()->info()->create();
        $successNotification = Notification::factory()->success()->create();
        $warningNotification = Notification::factory()->warning()->create();
        $errorNotification = Notification::factory()->error()->create();

        $this->assertEquals('info', $infoNotification->type);
        $this->assertEquals('success', $successNotification->type);
        $this->assertEquals('warning', $warningNotification->type);
        $this->assertEquals('error', $errorNotification->type);
    }

    /** @test */
    public function notification_can_be_travel_request_approved()
    {
        $notification = Notification::factory()->travelRequestApproved()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertEquals('SPPD Disetujui', $notification->title);
        $this->assertEquals('Surat Perintah Perjalanan Dinas Anda telah disetujui.', $notification->message);
        $this->assertEquals('success', $notification->type);
    }

    /** @test */
    public function notification_can_be_travel_request_rejected()
    {
        $notification = Notification::factory()->travelRequestRejected()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertEquals('SPPD Ditolak', $notification->title);
        $this->assertEquals('Surat Perintah Perjalanan Dinas Anda telah ditolak.', $notification->message);
        $this->assertEquals('error', $notification->type);
    }

    /** @test */
    public function notification_belongs_to_user()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->assertInstanceOf(User::class, $notification->user);
        $this->assertEquals($this->user->id, $notification->user->id);
    }
}
