<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ChatMessage;
use App\Models\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ChatController extends Controller
{
    public function usersListView()
    {
        return view('agent.chat');
    }

    public function usersList()
    {
        $authId = Auth::id();

        // Update Agent Online Status
        UserStatus::updateOrCreate(
            ['user_id' => $authId],
            ['is_online' => true, 'last_seen' => now(), 'updated_at' => now()]
        );

        // AGENT LOGIC: Only fetch users with 'admin' role
        $users = User::whereIn('role', ['admin', 'staff'])
            ->select(['id', 'business_name', 'business_logo', 'name', 'created_at'])
            ->addSelect([
                'last_message' => ChatMessage::select('message')
                    ->where(function ($q) use ($authId) {
                        $q->whereColumn('sender_id', 'users.id')->where('receiver_id', $authId)
                            ->orWhereColumn('receiver_id', 'users.id')->where('sender_id', $authId);
                    })->latest()->limit(1),
                'last_message_file' => ChatMessage::select('file')
                    ->where(function ($q) use ($authId) {
                        $q->whereColumn('sender_id', 'users.id')->where('receiver_id', $authId)
                            ->orWhereColumn('receiver_id', 'users.id')->where('sender_id', $authId);
                    })->latest()->limit(1),
                'last_message_time' => ChatMessage::select('created_at')
                    ->where(function ($q) use ($authId) {
                        $q->whereColumn('sender_id', 'users.id')->where('receiver_id', $authId)
                            ->orWhereColumn('receiver_id', 'users.id')->where('sender_id', $authId);
                    })->latest()->limit(1),
                'unread_count' => ChatMessage::selectRaw('count(*)')
                    ->whereColumn('sender_id', 'users.id')
                    ->where('receiver_id', $authId)
                    ->whereNull('read_at')
            ])
            ->with('status')
            ->get()
            ->map(function ($user) {
                $user->is_online = $user->status && Carbon::parse($user->status->updated_at)->diffInMinutes(now()) < 2;
                $user->business_name = $user->business_name ?: $user->name;
                $user->has_attachment = !empty($user->last_message_file);
                return $user;
            })
            ->sortByDesc(fn($u) => $u->last_message_time ?? $u->created_at)
            ->values();

        return response()->json([
            'users' => $users,
            'total_unread' => ChatMessage::where('receiver_id', $authId)->whereNull('read_at')->count()
        ]);
    }

    public function fetchMessages($userId)
    {
        $authId = Auth::id();
        $messages = ChatMessage::where(function ($query) use ($authId, $userId) {
            $query->where(function ($q) use ($authId, $userId) {
                $q->where('sender_id', $authId)->where('receiver_id', $userId);
            })->orWhere(function ($q) use ($authId, $userId) {
                $q->where('sender_id', $userId)->where('receiver_id', $authId);
            });
        })->orderBy('created_at', 'asc')->get();

        ChatMessage::where('receiver_id', $authId)->where('sender_id', $userId)
            ->whereNull('read_at')->update(['read_at' => now(), 'status' => 'read']);

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx,zip|max:10240',
        ]);

        if (!$request->message && !$request->hasFile('file')) {
            return response()->json(['success' => false, 'error' => 'Empty message'], 422);
        }

        $filePath = $request->hasFile('file') ? $request->file('file')->store('chat_files', 'public') : null;

        $message = ChatMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message ?? '',
            'file' => $filePath,
            'status' => 'sent'
        ]);

        return response()->json(['success' => true, 'data' => $message]);
    }

    public function delete($id)
    {
        // SECURITY: Agent can ONLY delete their own messages
        $msg = ChatMessage::where('id', $id)
            ->where('sender_id', Auth::id())
            ->first();

        if ($msg) {
            if ($msg->file) Storage::disk('public')->delete($msg->file);
            $msg->delete();
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'error'], 403);
    }

    public function clear($receiverId)
    {
        // For agents, "Clear" only deletes messages they sent to that admin
        $messages = ChatMessage::where('sender_id', Auth::id())
            ->where('receiver_id', $receiverId)
            ->get();

        foreach ($messages as $msg) {
            if ($msg->file) Storage::disk('public')->delete($msg->file);
            $msg->delete();
        }
        return response()->json(['status' => 'success']);
    }
}
