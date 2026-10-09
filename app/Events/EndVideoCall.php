<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EndVideoCall implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $participants;

    public function __construct(string $roomId, array $participants = [])
    {
        $this->roomId = $roomId;
        $this->participants = array_values(
            array_unique(array_map('intval', $participants))
        );
    }

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('video-call.' . $this->roomId)];

        foreach ($this->participants as $participant) {
            if ($participant > 0) {
                $channels[] = new PrivateChannel('user.' . $participant);
            }
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'EndVideoCall';
    }

    public function broadcastWith(): array
    {
        return ['room_id' => $this->roomId];
    }
}