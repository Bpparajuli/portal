<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(ChatMessage $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        // We broadcast to the RECEIVER'S private channel
        return new PrivateChannel('chat.' . $this->message->receiver_id);
    }

    // This allows us to access $message in Javascript directly
    public function broadcastWith()
    {
        $sender = $this->message->sender;

        $name = $sender?->name ?? '';
        $business = $sender?->business_name ?? '';
        $display = $name ?: ($business ?: 'Someone');
        if ($name && $business && $business !== $name) {
            $display = $name . ' (' . $business . ')';
        }

        return [
            'message' => $this->message,
            'sender_name' => $display,
        ];
    }
}
