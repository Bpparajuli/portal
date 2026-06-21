<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ChatMessage;
use App\Models\UserStatus;
use App\Events\MessageSent;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChatController extends Controller
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {}

    public function index()
    {
        return view('shared.chat.index');
    }

    public function usersList()
    {
        $authId = Auth::id();

        UserStatus::updateOrCreate(
            ['user_id' => $authId],
            ['is_online' => true, 'last_seen' => now(), 'updated_at' => now()]
        );

        $users = User::where('id', '!=', $authId)
            ->whereIn('role', Auth::user()->is_agent ? ['admin', 'staff'] : ['admin', 'agent', 'staff'])
            ->with('status')
            ->get()
            ->map(function ($user) use ($authId) {
                $user->is_online = $user->status
                    && Carbon::parse($user->status->updated_at)->diffInMinutes(now()) < 2;
                $user->last_seen_formatted = $user->is_online
                    ? 'Active Now'
                    : ($user->status && $user->status->last_seen
                        ? Carbon::parse($user->status->last_seen)->diffForHumans()
                        : 'Offline');
                $user->business_name = $user->business_name ?: $user->name;

                $lastMsg = ChatMessage::where(function ($q) use ($authId, $user) {
                    $q->where('sender_id', $authId)->where('receiver_id', $user->id);
                })->orWhere(function ($q) use ($authId, $user) {
                    $q->where('sender_id', $user->id)->where('receiver_id', $authId);
                })->orderByDesc('id')->first();

                $user->last_message = $lastMsg?->message ?? '';
                $user->last_message_file = $lastMsg?->file ?? null;
                $user->last_message_time = $lastMsg?->created_at ?? $user->created_at;
                $user->has_attachment = !empty($lastMsg?->file);

                $user->unread_count = ChatMessage::where('sender_id', $user->id)
                    ->where('receiver_id', $authId)
                    ->whereNull('read_at')
                    ->count();

                return $user;
            });

        $sections = [];
        $roleLabels = [
            'admin' => 'Admin Chats',
            'agent' => 'Agent Chats',
            'staff' => 'Team Member Chats',
        ];
        $roleOrder = ['admin', 'agent', 'staff'];

        foreach ($roleOrder as $role) {
            $sectionUsers = $users->where('role', $role)->values();
            if ($sectionUsers->isEmpty()) continue;

            $sectionUsers = $sectionUsers->sortByDesc(function ($u) {
                $lastSeen = $u->status && $u->status->last_seen ? strtotime($u->status->last_seen) : 0;
                $lastMsg = $u->last_message_time ? strtotime($u->last_message_time) : 0;
                $unreadBoost = $u->unread_count > 0 ? 10000000 : 0;
                $onlineBoost = $u->is_online ? 1000000 : 0;
                return $unreadBoost + $onlineBoost + max($lastMsg, $lastSeen);
            })->values();

            $sectionUnread = $sectionUsers->sum('unread_count');

            $sections[] = [
                'role' => $role,
                'label' => $roleLabels[$role] ?? ucfirst($role) . ' Chats',
                'unread_count' => $sectionUnread,
                'users' => $sectionUsers,
            ];
        }

        usort($sections, function ($a, $b) {
            return $b['unread_count'] <=> $a['unread_count'];
        });

        $totalUnread = ChatMessage::where('receiver_id', $authId)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'sections' => $sections,
            'total_unread' => $totalUnread
        ]);
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
        })->whereNull('read_at')->update([
            'read_at' => now(),
            'status' => 'read'
        ]);

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
            return response()->json(['success' => false, 'error' => 'Cannot send empty message'], 422);
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $this->fileUploadService->uploadChatAttachment($request->file('file'));
        }

        try {
            $message = ChatMessage::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $request->receiver_id,
                'message' => $request->message ?? '',
                'file' => $filePath,
                'status' => 'sent'
            ]);

            try {
                broadcast(new \App\Events\MessageSent($message))->toOthers();
            } catch (\Exception $e) {
                // Pusher/broadcast may not be configured; message is saved either way
            }

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
            if ($msg->file) {
                Storage::disk('public')->delete($msg->file);
            }
            $msg->delete();
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error'], 403);
    }

    public function clear($receiverId)
    {
        $messages = ChatMessage::where('sender_id', Auth::id())
            ->where('receiver_id', $receiverId)
            ->get();

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
