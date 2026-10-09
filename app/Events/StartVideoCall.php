<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StartVideoCall implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $callerId;
    public $callerName;
    public $targetUserId;
    public $type;

    public function __construct(
        string $roomId,
        int $callerId,
        string $callerName,
        int $targetUserId,
        string $type = 'video'
    ) {
        $this->roomId = $roomId;
        $this->callerId = $callerId;
        $this->callerName = $callerName;
        $this->targetUserId = $targetUserId;
        $this->type = $type;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->targetUserId)];
    }

    public function broadcastAs(): string
    {
        return 'StartVideoCall';
    }

    public function broadcastWith(): array
    {
        return [
            'room_id'        => $this->roomId,
            'caller_id'      => $this->callerId,
            'caller_name'    => $this->callerName,
            'target_user_id' => $this->targetUserId,
            'type'           => $this->type,
        ];
    }
}