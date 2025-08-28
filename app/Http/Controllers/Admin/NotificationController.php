<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Ensure user is logged in
    }

    public function index()
    {
        $notifications = auth()->user()->notifications()->latest()->get(); // now guaranteed
        return view('admin.notifications', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = DatabaseNotification::findOrFail($id);
        $notification->markAsRead();
        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->each(function (DatabaseNotification $notification) {
            $notification->markAsRead();
        });
        return back()->with('success', 'All notifications marked as read.');
    }

    public function readAndRedirect($id)
    {
        $notification = DatabaseNotification::findOrFail($id);
        $notification->markAsRead();

        // Example: redirect after new user registration notification
        if ($notification->type === \App\Notifications\NewUserRegistered::class) {
            $userId = $notification->data['user_id'] ?? null;
            if ($userId) {
                return redirect()->route('admin.users.show', $userId);
            }
        }

        return redirect()->route('admin.notifications');
    }
}
