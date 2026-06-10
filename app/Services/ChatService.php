<?php
namespace App\Services;

use App\Events\MessageSent;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;

class ChatService
{
    /**
     * Send a chat message from sender to receiver, optionally with a file attachment.
     *
     * @return ChatMessage
     */
    public function sendMessage(User $sender, User $receiver, Request $request): ChatMessage
    {
        $data = [
            'sender_id'   => $sender->id,
            'receiver_id' => $receiver->id,
            'message'     => $request->input('message'),
            'status'      => 'sent',
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file']      = $file->store('chat-attachments', 'public');
            $data['file_type'] = $file->getClientOriginalExtension();
        }

        $message = ChatMessage::create($data);
        broadcast(new MessageSent($message))->toOthers();

        return $message;
    }

    /**
     * Get users that the authenticated user can chat with.
     *
     * ROLE SCOPING:
     * - Admin: all users.
     * - Agent: admin users + their own staff.
     * - Staff: admin + their parent agent.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getChatUsers(User $auth): \Illuminate\Support\Collection
    {
        if ($auth->is_admin) {
            return User::where('id', '!=', $auth->id)
                ->select('id', 'name', 'business_name', 'role', 'business_logo', 'slug')
                ->orderBy('name')->get();
        }

        if ($auth->is_agent) {
            $adminIds = User::whereIn('role', ['superadmin', 'admin'])->pluck('id');
            $staffIds = User::where('parent_id', $auth->id)->where('role', 'staff')->pluck('id');
            return User::whereIn('id', $adminIds->merge($staffIds))
                ->where('id', '!=', $auth->id)
                ->select('id', 'name', 'business_name', 'role', 'business_logo', 'slug')
                ->orderBy('name')->get();
        }

        // Staff: see admins + their parent agent
        $adminIds = User::whereIn('role', ['superadmin', 'admin'])->pluck('id');
        return User::whereIn('id', $adminIds->push($auth->parent_id))
            ->where('id', '!=', $auth->id)
            ->select('id', 'name', 'business_name', 'role', 'business_logo', 'slug')
            ->orderBy('name')->get();
    }

    /**
     * Get paginated messages between two users.
     */
    public function getMessages(User $auth, User $other): \Illuminate\Pagination\LengthAwarePaginator
    {
        return ChatMessage::where(function ($q) use ($auth, $other) {
                $q->where('sender_id', $auth->id)->where('receiver_id', $other->id);
            })->orWhere(function ($q) use ($auth, $other) {
                $q->where('sender_id', $other->id)->where('receiver_id', $auth->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(50);
    }

    /**
     * Get only messages newer than the user's last read timestamp.
     */
    public function getNewMessages(User $auth, User $other): \Illuminate\Support\Collection
    {
        return ChatMessage::where('sender_id', $other->id)
            ->where('receiver_id', $auth->id)
            ->whereNull('read_at')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Delete a single message (only the sender can delete).
     */
    public function deleteMessage(User $auth, int $messageId): void
    {
        $message = ChatMessage::where('id', $messageId)->where('sender_id', $auth->id)->firstOrFail();
        $message->delete();
    }

    /**
     * Clear all messages between two users (marks as deleted for the viewer).
     */
    public function clearConversation(User $auth, User $other): void
    {
        ChatMessage::where(function ($q) use ($auth, $other) {
                $q->where('sender_id', $auth->id)->where('receiver_id', $other->id);
            })->orWhere(function ($q) use ($auth, $other) {
                $q->where('sender_id', $other->id)->where('receiver_id', $auth->id);
            })->delete();
    }

    /**
     * Get unread message count for the authenticated user.
     */
    public function getUnreadCount(User $user): int
    {
        return ChatMessage::where('receiver_id', $user->id)->whereNull('read_at')->count();
    }
}
