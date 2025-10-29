<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Auth::user()
            ->notifications()
            ->latest()
            ->paginate(12);

        return view('admin.notifications', compact('notifications'));
    }

    public function markRead($id)
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            return redirect()->back()->with('status', 'Notification marked as read.');
        }

        return redirect()->back()->with('status', 'Notification not found.');
    }

    public function markUnread($id)
    {
        $notification = DatabaseNotification::find($id);

        if ($notification && $notification->notifiable_id === Auth::id()) {
            $notification->forceFill(['read_at' => null])->save();
            return redirect()->back()->with('status', 'Notification marked as unread.');
        }

        return redirect()->back()->with('status', 'Notification not found.');
    }

    public function markAll()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
        return redirect()->back()->with('status', 'All notifications marked as read.');
    }
}
