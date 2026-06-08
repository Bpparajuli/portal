<?php

namespace App\Models\Traits;

use App\Models\ChatMessage;

trait HasChatMessages
{
    public function chatMessages()
    {
        return ChatMessage::where('sender_id', $this->id)
            ->orWhere('receiver_id', $this->id);
    }

    public function unreadMessages()
    {
        return ChatMessage::where('receiver_id', $this->id)
            ->where('status', '!=', 'read');
    }

    public function conversationWith($otherUserId)
    {
        return ChatMessage::where(function ($q) use ($otherUserId) {
            $q->where('sender_id', $this->id)
                ->where('receiver_id', $otherUserId);
        })
            ->orWhere(function ($q) use ($otherUserId) {
                $q->where('sender_id', $otherUserId)
                    ->where('receiver_id', $this->id);
            })
            ->orderBy('created_at', 'asc');
    }

    public function unreadFrom($senderId)
    {
        return ChatMessage::where('receiver_id', $this->id)
            ->where('sender_id', $senderId)
            ->where('status', '!=', 'read');
    }
}
