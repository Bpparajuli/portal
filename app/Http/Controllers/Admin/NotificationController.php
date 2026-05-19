<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    // List all notifications with messages separated
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get regular notifications (excluding messages)
        $notifications = $user->notifications()
            ->where('type', '!=', 'App\\Notifications\\ApplicationMessageAdded')
            ->whereJsonDoesntContain('data->type', 'application_message_added')
            ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END') // Unread first
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'notifications_page')
            ->withQueryString();

        // Get messages only (same as agent version)
        $messages = $user->notifications()
            ->where(function ($q) {
                $q->where('type', 'App\\Notifications\\ApplicationMessageAdded')
                    ->orWhereJsonContains('data->type', 'application_message_added');
            })
            ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END') // Unread first
            ->orderBy('created_at', 'desc')
            ->paginate(5, ['*'], 'messages_page')
            ->withQueryString();

        return view('admin.notifications', compact('notifications', 'messages'));
    }

    // Mark a single notification as read
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    // Mark a single notification as unread
    public function markAsUnread($id)
    {
        $notification = DatabaseNotification::findOrFail($id);
        if ($notification->notifiable_id === Auth::id()) {
            $notification->forceFill(['read_at' => null])->save();
            return back()->with('success', 'Notification marked as unread.');
        }

        return back()->with('error', 'Notification not found.');
    }

    // Mark all notifications as read
    public function markAll(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    }

    // Delete a single notification
    public function delete($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        return back()->with('success', 'Notification deleted successfully.');
    }

    // Delete all notifications by type
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

        $deletedCount = $query->delete();

        $message = $type === 'messages'
            ? "All messages ($deletedCount) deleted successfully."
            : ($type === 'notifications'
                ? "All regular notifications ($deletedCount) deleted successfully."
                : "All notifications and messages ($deletedCount) deleted successfully.");

        return back()->with('success', $message);
    }

    // Read notification and redirect
    public function readAndRedirect($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);

        // Mark as read if not already
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        $data = $notification->data;
        $url = null;

        // Try to get URL from various possible data structures
        if (!empty($data['link'])) {
            $url = $data['link'];
        } elseif (!empty($data['application']['id'])) {
            $url = route('admin.applications.show', $data['application']['id']);
        } elseif (!empty($data['student']['id'])) {
            $url = route('admin.students.show', $data['student']['id']);
        } elseif (!empty($data['agent']['id'])) {
            $url = route('admin.agents.show', $data['agent']['id']);
        }

        return redirect($url ?? route('admin.notifications'));
    }
}
