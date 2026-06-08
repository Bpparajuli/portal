<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
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
                    'url' => route('staff.notifications.readAndRedirect', $n->id),
                ];
            });
            return response()->json([
                'unread_count' => $user->unreadNotifications->count(),
                'recent' => $recent,
            ]);
        }

        $notifications = $user->notifications()->paginate(20);
        return view('staff.notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();
        return back();
    }

    public function markAll()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    }

    public function readAndRedirect($id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        $link = $notification->data['link'] ?? route('staff.notifications.index');
        return redirect($link);
    }

    public function delete($id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->firstOrFail();
        $notification->delete();
        return back();
    }

    public function deleteAll()
    {
        Auth::user()->notifications()->delete();
        return back()->with('success', 'All notifications deleted.');
    }
}
