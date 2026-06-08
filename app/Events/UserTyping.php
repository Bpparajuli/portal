<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $typing;

    public function __construct(int $userId, bool $typing)
    {
        $this->userId = $userId;
        $this->typing = $typing;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('typing.' . $this->userId);
    }

    public function broadcastWith()
    {
        return [
            'user_id' => $this->userId,
            'typing' => $this->typing,
        ];
    }
}
