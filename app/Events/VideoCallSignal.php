<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoCallSignal implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $signal;
    public $senderId;

    public function __construct(string $roomId, array $signal, int $senderId)
    {
        $this->roomId = $roomId;
        $this->signal = $signal;
        $this->senderId = $senderId;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('video-call.' . $this->roomId)];
    }

    public function broadcastAs(): string
    {
        return 'VideoCallSignal';
    }

    public function broadcastWith(): array
    {
        return [
            'room_id'   => $this->roomId,
            'signal'    => $this->signal,
            'sender_id' => $this->senderId,
        ];
    }
}