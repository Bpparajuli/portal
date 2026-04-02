<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ChatMessage;
use App\Models\UserStatus;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ChatController extends Controller
{
    public function usersListView()
    {
        return view('admin.chat');
    }

    public function usersList()
    {
        $authId = Auth::id();

        // Update online status
        UserStatus::updateOrCreate(
            ['user_id' => $authId],
            [
                'is_online' => true,
                'last_seen' => now(),
                'updated_at' => now()
            ]
        );

        $users = User::where('id', '!=', $authId)
            ->where(function ($q) {
                $q->where('role', 'admin')->orWhere('role', 'agent');
            })
            ->select(['id', 'business_name', 'business_logo', 'name', 'created_at'])
            ->addSelect([

                // ✅ Last message text
                'last_message' => ChatMessage::select('message')
                    ->where(function ($q) use ($authId) {
                        $q->whereColumn('sender_id', 'users.id')->where('receiver_id', $authId)
                            ->orWhereColumn('receiver_id', 'users.id')->where('sender_id', $authId);
                    })
                    ->latest()
                    ->limit(1),

                // ✅ Last message FILE (IMPORTANT)
                'last_message_file' => ChatMessage::select('file')
                    ->where(function ($q) use ($authId) {
                        $q->whereColumn('sender_id', 'users.id')->where('receiver_id', $authId)
                            ->orWhereColumn('receiver_id', 'users.id')->where('sender_id', $authId);
                    })
                    ->latest()
                    ->limit(1),

                // ✅ Last message time
                'last_message_time' => ChatMessage::select('created_at')
                    ->where(function ($q) use ($authId) {
                        $q->whereColumn('sender_id', 'users.id')->where('receiver_id', $authId)
                            ->orWhereColumn('receiver_id', 'users.id')->where('sender_id', $authId);
                    })
                    ->latest()
                    ->limit(1),

                // ✅ Unread count
                'unread_count' => ChatMessage::selectRaw('count(*)')
                    ->whereColumn('sender_id', 'users.id')
                    ->where('receiver_id', $authId)
                    ->whereNull('read_at')
            ])
            ->with('status')
            ->get()
            ->map(function ($user) {

                // ✅ Online status
                $user->is_online = $user->status
                    && Carbon::parse($user->status->updated_at)->diffInMinutes(now()) < 2;

                $user->last_seen_formatted = $user->is_online
                    ? 'Active Now'
                    : ($user->status && $user->status->last_seen
                        ? Carbon::parse($user->status->last_seen)->diffForHumans()
                        : 'Offline');

                // ✅ Fallback name
                if (!$user->business_name) {
                    $user->business_name = $user->name;
                }

                // ✅ EXTRA SAFETY: detect attachment
                $user->has_attachment = !empty($user->last_message_file);

                return $user;
            })
            ->sortByDesc(fn($u) => $u->last_message_time ?? $u->created_at)
            ->values();

        // ✅ Total unread messages
        $totalUnread = ChatMessage::where('receiver_id', $authId)
            ->where('status', '!=', 'read')
            ->count();

        return response()->json([
            'users' => $users,
            'total_unread' => $totalUnread
        ]);
    }
    public function fetchMessages($userId)
    {
        $authId = Auth::id();

        // FIX: Wrap in a parent where to ensure it only gets messages between these TWO users
        $messages = ChatMessage::where(function ($query) use ($authId, $userId) {
            $query->where(function ($q) use ($authId, $userId) {
                $q->where('sender_id', $authId)->where('receiver_id', $userId);
            })
                ->orWhere(function ($q) use ($authId, $userId) {
                    $q->where('sender_id', $userId)->where('receiver_id', $authId);
                });
        })
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark as read
        ChatMessage::where('receiver_id', $authId)
            ->where('sender_id', $userId)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'status' => 'read'
            ]);

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        // Increase max size to 10MB just in case (10240)
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx,zip|max:10240',
        ]);

        // Logic check: Must have either a message OR a file
        if (!$request->message && !$request->hasFile('file')) {
            return response()->json(['success' => false, 'error' => 'Cannot send empty message'], 422);
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            // Ensure the folder exists and is writable
            $filePath = $request->file('file')->store('chat_files', 'public');
        }

        try {
            $message = ChatMessage::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $request->receiver_id,
                'message' => $request->message ?? '', // Ensure it's not null if DB expects string
                'file' => $filePath,
                'status' => 'sent'
            ]);

            // If you use Pusher/Websockets, trigger it here:
            // broadcast(new MessageSent($message))->toOthers();

            return response()->json(['success' => true, 'data' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        $msg = ChatMessage::where('id', $id)
            ->where('sender_id', Auth::id())
            ->first();

        if ($msg) {

            // delete file also
            if ($msg->file) {
                Storage::disk('public')->delete($msg->file);
            }

            $msg->delete();
        }

        return response()->json(['status' => 'success']);
    }

    public function clear($receiverId)
    {
        $messages = ChatMessage::where(function ($q) use ($receiverId) {
            $q->where('sender_id', Auth::id())->where('receiver_id', $receiverId);
        })
            ->orWhere(function ($q) use ($receiverId) {
                $q->where('sender_id', $receiverId)->where('receiver_id', Auth::id());
            })
            ->get();

        // delete files also
        foreach ($messages as $msg) {
            if ($msg->file) {
                Storage::disk('public')->delete($msg->file);
            }
            $msg->delete();
        }

        return response()->json(['status' => 'success']);
    }

    public function deleteFile($messageId)
    {
        $msg = ChatMessage::where('id', $messageId)
            ->where('sender_id', Auth::id())
            ->first();

        if ($msg && $msg->file) {
            Storage::disk('public')->delete($msg->file);
            $msg->file = null;
            $msg->file_type = null;
            $msg->save();
        }

        return response()->json(['status' => 'success']);
    }
}
