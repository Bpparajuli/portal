<?php

namespace App\Actions;

use App\Events\MessageSent;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SendChatMessageAction
{
    public function execute(User $sender, User $receiver, Request $request): ChatMessage
    {
        $data = [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'message' => $request->input('message'),
            'status' => 'sent',
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('chat-attachments', 'public');
            $data['file'] = $path;
            $data['file_type'] = $file->getClientOriginalExtension();
        }

        $message = ChatMessage::create($data);

        broadcast(new MessageSent($message))->toOthers();

        return $message;
    }
}
