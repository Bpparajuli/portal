<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $user->role;

        if ($request->has('count')) {
            $recent = $user->notifications()->take(5)->get()->map(function ($n) use ($role) {
                return [
                    'id' => $n->id,
                    'data' => $n->data,
                    'read_at' => $n->read_at,
                    'created_at' => $n->created_at->diffForHumans(),
                    'url' => route($role . '.notifications.readAndRedirect', $n->id),
                ];
            });
            return response()->json([
                'unread_count' => $user->unreadNotifications->count(),
                'recent' => $recent,
            ]);
        }

        if ($role === 'staff') {
            $notifications = $user->notifications()
                ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return view('shared.notifications.index', compact('notifications'));
        }

        $notifications = $user->notifications()
            ->where('type', '!=', 'App\\Notifications\\ApplicationMessageAdded')
            ->whereJsonDoesntContain('data->type', 'application_message_added')
            ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'notifications_page')
            ->withQueryString();

        $messages = $user->notifications()
            ->where(function ($q) {
                $q->where('type', 'App\\Notifications\\ApplicationMessageAdded')
                    ->orWhereJsonContains('data->type', 'application_message_added');
            })
            ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END')
            ->orderBy('created_at', 'desc')
            ->paginate(5, ['*'], 'messages_page')
            ->withQueryString();

        return view('shared.notifications.index', compact('notifications', 'messages'));
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
        $notification->forceFill(['read_at' => null])->save();
        return back()->with('success', 'Notification marked as unread.');
    }

    public function markAll()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    }

    public function delete($id)
    {
        Auth::user()->notifications()->findOrFail($id)->delete();
        return back()->with('success', 'Notification deleted.');
    }

    public function deleteAll(Request $request)
    {
        $query = Auth::user()->notifications();
        $type = $request->get('type', 'all');

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
        return back()->with('success', 'All notifications deleted.');
    }

    public function readAndRedirect($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $data = $notification->data;
        $role = Auth::user()->role;
        $url = null;

        if (!empty($data['link'])) {
            $url = $data['link'];
        } elseif (!empty($data['application']['id'])) {
            $url = route($role . '.applications.show', $data['application']['id']);
        } elseif (!empty($data['student']['id'])) {
            $url = $role === 'staff'
                ? route('staff.student.show', $data['student']['id'])
                : route($role . '.students.show', $data['student']['id']);
        } elseif (!empty($data['agent']['id'])) {
            $agent = User::find($data['agent']['id']);
            if ($agent && $role === 'admin') {
                $url = route('admin.users.show', $agent);
            }
        }

        return redirect($url ?? route($role . '.notifications.index'));
    }
}
