<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ChatMessage;
use App\Models\UserStatus;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat.index');
    }

    public function usersList()
    {
        return response()->json($this->getUserListData(Auth::id()));
    }

    private function getUserListData(int $authId, array $allowedRoles = ['admin', 'agent', 'staff'])
    {
        UserStatus::updateOrCreate(
            ['user_id' => $authId],
            ['is_online' => true, 'last_seen' => now(), 'updated_at' => now()]
        );

        $users = User::where('id', '!=', $authId)
            ->whereIn('role', $allowedRoles)
            ->with('status')
            ->orderBy('role')
            ->get();

        $userIds = $users->pluck('id')->toArray();

        $allMessages = collect();
        if (!empty($userIds)) {
            $allMessages = ChatMessage::where(function ($q) use ($authId, $userIds) {
                $q->where('sender_id', $authId)->whereIn('receiver_id', $userIds);
            })->orWhere(function ($q) use ($authId, $userIds) {
                $q->whereIn('sender_id', $userIds)->where('receiver_id', $authId);
            })
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(function ($m) use ($authId) {
                return $m->sender_id == $authId ? $m->receiver_id : $m->sender_id;
            });
        }

        $unreadCounts = ChatMessage::select('sender_id', DB::raw('IFNULL(COUNT(*),0) as cnt'))
            ->whereIn('receiver_id', array_merge([$authId], $userIds))
            ->whereIn('sender_id', $userIds)
            ->whereNull('read_at')
            ->groupBy('sender_id')
            ->get()
            ->keyBy('sender_id');

        $lastMessages = [];
        foreach ($allMessages as $partnerId => $msgs) {
            $last = $msgs->first();
            $lastMessages[$partnerId] = $last;
        }

        $totalUnread = ChatMessage::where('receiver_id', $authId)
            ->whereNull('read_at')
            ->whereIn('sender_id', $userIds)
            ->count();

        $results = [];
        foreach ($users as $user) {
            $user->is_online = $user->status
                && Carbon::parse($user->status->updated_at)->diffInMinutes(now()) < 2;
            $user->business_name = $user->business_name ?: $user->name;
            $user->last_seen_formatted = $user->is_online
                ? 'Active Now'
                : (($user->status && $user->status->last_seen)
                    ? Carbon::parse($user->status->last_seen)->diffForHumans()
                    : 'Offline');
            $user->unread_count = $unreadCounts->get($user->id)?->cnt ?? 0;
            $last = $lastMessages[$user->id] ?? null;
            $user->last_message = $last?->message ?? '';
            $user->last_message_file = $last?->file ?? null;
            $user->last_message_time = $last?->created_at ?? $user->created_at;
            $user->has_attachment = !empty($last?->file);
            $results[] = $user;
        }

        usort($results, function ($a, $b) {
            $lastSeenA = $a->status && $a->status->last_seen ? strtotime($a->status->last_seen) : 0;
            $lastSeenB = $b->status && $b->status->last_seen ? strtotime($b->status->last_seen) : 0;
            $lastMsgA = $a->last_message_time ? strtotime($a->last_message_time) : 0;
            $lastMsgB = $b->last_message_time ? strtotime($b->last_message_time) : 0;
            $scoreA = ($a->unread_count > 0 ? 10000000 : 0) + ($a->is_online ? 1000000 : 0) + max($lastMsgA, $lastSeenA);
            $scoreB = ($b->unread_count > 0 ? 10000000 : 0) + ($b->is_online ? 1000000 : 0) + max($lastMsgB, $lastSeenB);
            return $scoreB <=> $scoreA;
        });

        return [
            'users' => array_values($results),
            'total_unread' => $totalUnread
        ];
    }

    private function getMessages($userId, int $authId)
    {
        return ChatMessage::where(function ($query) use ($authId, $userId) {
            $query->where(function ($q) use ($authId, $userId) {
                $q->where('sender_id', $authId)->where('receiver_id', $userId);
            })->orWhere(function ($q) use ($authId, $userId) {
                $q->where('sender_id', $userId)->where('receiver_id', $authId);
            });
        })->orderBy('created_at', 'asc')->get();
    }

    public function fetchMessages($userId)
    {
        $authId = Auth::id();
        $messages = $this->getMessages($userId, $authId);

        ChatMessage::where(function ($q) use ($authId, $userId) {
            $q->where('sender_id', $userId)->where('receiver_id', $authId);
        })->whereNull('read_at')->update(['read_at' => now(), 'status' => 'read']);

        return response()->json($messages);
    }

    public function fetchNewMessages(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'last_message_id' => 'nullable|integer|min:0',
        ]);

        $authId = Auth::id();
        $userId = (int) $request->user_id;
        $lastId = (int) ($request->last_message_id ?? 0);

        $query = ChatMessage::where(function ($q) use ($authId, $userId) {
            $q->where('sender_id', $authId)->where('receiver_id', $userId);
        })->orWhere(function ($q) use ($authId, $userId) {
            $q->where('sender_id', $userId)->where('receiver_id', $authId);
        });

        if ($lastId > 0) {
            $query->where('id', '>', $lastId);
        }

        $messages = $query->orderBy('created_at', 'asc')->get();

        if (!empty($messages)) {
            ChatMessage::whereIn('id', $messages->pluck('id'))
                ->where('receiver_id', $authId)
                ->whereNull('read_at')
                ->update(['read_at' => now(), 'status' => 'read']);
        }

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
            'status' => 'sent',
        ]);

        try {
            broadcast(new \App\Events\MessageSent($message))->toOthers();
        } catch (\Exception $e) {
            // Pusher/broadcast may not be configured; message is saved either way
        }

        return response()->json(['success' => true, 'data' => $message]);
    }

    public function delete($id)
    {
        $msg = ChatMessage::where('id', $id)->where('sender_id', Auth::id())->first();
        if ($msg) {
            if ($msg->file) Storage::disk('public')->delete($msg->file);
            $msg->delete();
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'error'], 403);
    }

    public function clear($receiverId)
    {
        $messages = ChatMessage::where('sender_id', Auth::id())
            ->where('receiver_id', $receiverId)->get();
        foreach ($messages as $msg) {
            if ($msg->file) Storage::disk('public')->delete($msg->file);
            $msg->delete();
        }
        return response()->json(['status' => 'success']);
    }

    public function typing(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'typing' => 'required|boolean',
        ]);

        try {
            broadcast(new \App\Events\UserTyping($request->receiver_id, $request->typing))->toOthers();
        } catch (\Exception $e) {
            // Pusher may not be configured, silently fail
        }

        return response()->json(['status' => 'ok']);
    }
}
