<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    /**
     * Display the notifications for admins.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Example: Fetch unread notifications
        $notifications = Notification::where('is_read', false)->get();
        return view('admin.notifications', compact('notifications'));
    }
}
