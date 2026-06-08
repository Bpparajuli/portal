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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ChatController extends Controller
{
    public function usersListView()
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

        $subLastMessage = ChatMessage::select('message')
            ->where(function ($q) use ($authId) {
                $q->where(function ($q2) use ($authId) {
                    $q2->where('sender_id', $authId)->whereColumn('receiver_id', 'users.id');
                })->orWhere(function ($q2) use ($authId) {
                    $q2->whereColumn('sender_id', 'users.id')->where('receiver_id', $authId);
                });
            })
            ->orderByDesc('id')
            ->limit(1);

        $subLastFile = ChatMessage::select('file')
            ->where(function ($q) use ($authId) {
                $q->where(function ($q2) use ($authId) {
                    $q2->where('sender_id', $authId)->whereColumn('receiver_id', 'users.id');
                })->orWhere(function ($q2) use ($authId) {
                    $q2->whereColumn('sender_id', 'users.id')->where('receiver_id', $authId);
                });
            })
            ->orderByDesc('id')
            ->limit(1);

        $subLastTime = ChatMessage::select('created_at')
            ->where(function ($q) use ($authId) {
                $q->where(function ($q2) use ($authId) {
                    $q2->where('sender_id', $authId)->whereColumn('receiver_id', 'users.id');
                })->orWhere(function ($q2) use ($authId) {
                    $q2->whereColumn('sender_id', 'users.id')->where('receiver_id', $authId);
                });
            })
            ->orderByDesc('id')
            ->limit(1);

        $subUnread = ChatMessage::selectRaw('IFNULL(COUNT(*),0)')
            ->whereColumn('sender_id', 'users.id')
            ->where('receiver_id', $authId)
            ->whereNull('read_at');

        $users = User::where('id', '!=', $authId)
            ->whereIn('role', ['admin', 'agent', 'staff'])
            ->select(['id', 'business_name', 'business_logo', 'name', 'role', 'created_at'])
            ->selectSub($subLastMessage, 'last_message')
            ->selectSub($subLastFile, 'last_message_file')
            ->selectSub($subLastTime, 'last_message_time')
            ->selectSub($subUnread, 'unread_count')
            ->with('status')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($user) {
                $user->is_online = $user->status
                    && Carbon::parse($user->status->updated_at)->diffInMinutes(now()) < 2;
                $user->last_seen_formatted = $user->is_online
                    ? 'Active Now'
                    : ($user->status && $user->status->last_seen
                        ? Carbon::parse($user->status->last_seen)->diffForHumans()
                        : 'Offline');
                if (!$user->business_name) {
                    $user->business_name = $user->name;
                }
                $user->has_attachment = !empty($user->last_message_file);
                return $user;
            })
            ->sortByDesc(function ($u) {
                $lastSeen = $u->status && $u->status->last_seen ? strtotime($u->status->last_seen) : 0;
                $lastMsg = $u->last_message_time ? strtotime($u->last_message_time) : 0;
                return sprintf('%d%d%020d', $u->unread_count > 0 ? 1 : 0, $u->is_online ? 1 : 0, max($lastMsg, $lastSeen));
            })
            ->values();

        $totalUnread = ChatMessage::where('receiver_id', $authId)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'users' => $users,
            'total_unread' => $totalUnread
        ]);
    }
    public function fetchMessages($userId)
    {
        $authId = Auth::id();

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
        $msg = ChatMessage::find($id);

        if ($msg) {
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
