<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($request->has('count')) {
            $recent = $user->notifications()->take(5)->get()->map(function ($n) {
                return [
                    'id' => $n->id,
                    'data' => $n->data,
                    'read_at' => $n->read_at,
                    'created_at' => $n->created_at->diffForHumans(),
                    'url' => route('agent.notifications.readAndRedirect', $n->id),
                ];
            });
            return response()->json([
                'unread_count' => $user->unreadNotifications->count(),
                'recent' => $recent,
            ]);
        }

        $query = $user->notifications();

        // Get regular notifications (excluding messages) - Unread first, then by date
        $notifications = $query->clone()
            ->where('type', '!=', 'App\\Notifications\\ApplicationMessageAdded')
            ->whereJsonDoesntContain('data->type', 'application_message_added')
            ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END') // Unread first (0), read second (1)
            ->orderBy('created_at', 'desc') // Then by newest first
            ->paginate(10, ['*'], 'notifications_page')
            ->withQueryString();

        // Get messages only - Unread first, then by date
        $messages = $query->clone()
            ->where(function ($q) {
                $q->where('type', 'App\\Notifications\\ApplicationMessageAdded')
                    ->orWhereJsonContains('data->type', 'application_message_added');
            })
            ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END') // Unread first (0), read second (1)
            ->orderBy('created_at', 'desc') // Then by newest first
            ->paginate(5, ['*'], 'messages_page')
            ->withQueryString();

        return view('agent.notifications', compact('notifications', 'messages'));
    }

    public function markAll(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAsUnread($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->update(['read_at' => null]);

        return back()->with('success', 'Notification marked as unread.');
    }

    public function delete($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        return back()->with('success', 'Notification deleted successfully.');
    }

    public function deleteAll(Request $request)
    {
        $type = $request->get('type', 'all');

        $query = Auth::user()->notifications();

        if ($type === 'messages') {
            $query->where(function ($q) {
                $q->where('type', 'App\\Notifications\\ApplicationMessageAdded')
                    ->orWhereJsonContains('data->type', 'application_message_added');
            });
        } elseif ($type === 'notifications') {
            $query->where('type', '!=', 'App\\Notifications\\ApplicationMessageAdded')
                ->whereJsonDoesntContain('data->type', 'application_message_added');
        }

        $query->delete();

        $message = $type === 'messages' ? 'All messages deleted successfully.' : ($type === 'notifications' ? 'All notifications deleted successfully.' : 'All notifications and messages deleted successfully.');

        return back()->with('success', $message);
    }

    public function readAndRedirect($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $data = $notification->data;
        $url = null;

        if (!empty($data['application']['id'])) {
            $url = route('agent.applications.show', $data['application']['id']);
        } elseif (!empty($data['student']['id'])) {
            $url = route('agent.students.show', $data['student']['id']);
        }

        return redirect($url ?? route('agent.notifications'));
    }
}
