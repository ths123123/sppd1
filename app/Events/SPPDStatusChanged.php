<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\TravelRequest;

class SPPDStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $travelRequest;
    public $oldStatus;
    public $newStatus;
    public $notes;

    /**
     * Create a new event instance.
     */
    public function __construct(TravelRequest $travelRequest, string $oldStatus, string $newStatus, ?string $notes = null)
    {
        $this->travelRequest = $travelRequest;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->notes = $notes;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
