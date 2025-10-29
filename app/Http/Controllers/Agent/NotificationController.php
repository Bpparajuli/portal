<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications for the authenticated agent.
     */
    public function index()
    {
        $agent = Auth::user();

        // Use property (not method)
        $notifications = $agent->notifications()->paginate(10);
        $unreadCount = $agent->unreadNotifications->count();

        return view('agent.notifications', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead($id)
    {
        $agent = Auth::user();

        $notification = $agent->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        $agent = Auth::user();

        $agent->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Read and redirect if notification has a URL.
     */
    public function readAndRedirect($id)
    {
        $agent = Auth::user();

        $notification = $agent->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();

            $data = $notification->data;
            if (isset($data['url'])) {
                return redirect($data['url']);
            }
        }

        return back();
    }
}
