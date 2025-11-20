<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    // List all notifications
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->latest()->paginate(12);
        $unreadCount = $user->unreadNotifications()->count();

        return view('admin.notifications', compact('notifications', 'unreadCount'));
    }

    // Mark a single notification as read
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect()->back()->with('status', 'Notification marked as read.');
    }

    // Mark a single notification as unread
    public function markAsUnread($id)
    {
        $notification = DatabaseNotification::findOrFail($id);
        if ($notification->notifiable_id === Auth::id()) {
            $notification->forceFill(['read_at' => null])->save();
            return redirect()->back()->with('status', 'Notification marked as unread.');
        }

        return redirect()->back()->with('status', 'Notification not found.');
    }

    // Mark all notifications as read
    public function markAll()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return redirect()->back()->with('status', 'All notifications marked as read.');
    }
    public function readAndRedirect($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $link = $notification->data['link'] ?? route('admin.notifications');
        return redirect($link);
    }
}
