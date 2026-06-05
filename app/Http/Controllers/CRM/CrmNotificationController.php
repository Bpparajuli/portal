<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CrmNotificationController extends Controller
{
    /**
     * Get notifications for the navbar dropdown (AJAX endpoint)
     */
    public function fetch(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'unread_count' => 0,
                    'notifications' => []
                ]);
            }

            // Get unread count for CRM notifications only
            $unreadCount = DatabaseNotification::where('notifiable_id', $user->id)
                ->where('notifiable_type', get_class($user))
                ->whereNull('read_at')
                ->where('type', 'App\\Notifications\\CrmTaskNotification')
                ->count();

            // Get recent notifications (last 20)
            $notifications = DatabaseNotification::where('notifiable_id', $user->id)
                ->where('notifiable_type', get_class($user))
                ->where('type', 'App\\Notifications\\CrmTaskNotification')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            return response()->json([
                'unread_count' => $unreadCount,
                'notifications' => $notifications->map(function ($notification) {
                    $data = $notification->data;

                    // Ensure message exists
                    if (!isset($data['message']) && isset($data['task_title'])) {
                        $data['message'] = $data['task_title'];
                    }

                    return [
                        'id' => $notification->id,
                        'read_at' => $notification->read_at,
                        'data' => $data,
                        'created_at' => $notification->created_at->toIso8601String(),
                    ];
                })
            ]);
        } catch (\Exception $e) {
            Log::error('Fetch notifications error: ' . $e->getMessage());
            return response()->json([
                'unread_count' => 0,
                'notifications' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Mark single notification as read and redirect
     */
    public function markAsReadAndRedirect($id)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return redirect()->route('login');
            }

            $notification = DatabaseNotification::where('id', $id)
                ->where('notifiable_id', $user->id)
                ->where('notifiable_type', get_class($user))
                ->first();

            if (!$notification) {
                return redirect()->route('crm.dashboard')->with('error', 'Notification not found');
            }

            // Mark as read if not already
            if (is_null($notification->read_at)) {
                $notification->read_at = now();
                $notification->save();
            }

            // Get redirect URL from notification data
            $data = $notification->data;

            // Fix: Check both 'link' and 'url' keys
            $url = $data['link'] ?? $data['url'] ?? route('crm.dashboard');

            // Fix: If URL is relative, make it absolute
            if (strpos($url, '/') === 0) {
                $url = url($url);
            }

            return redirect($url);
        } catch (\Exception $e) {
            Log::error('Mark as read and redirect error: ' . $e->getMessage());
            return redirect()->route('crm.dashboard')->with('error', 'Unable to process notification');
        }
    }

    /**
     * Mark single notification as read (AJAX)
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['success' => false, 'error' => 'User not authenticated'], 401);
            }

            $notification = DatabaseNotification::where('id', $id)
                ->where('notifiable_id', $user->id)
                ->where('notifiable_type', get_class($user))
                ->first();

            if (!$notification) {
                return response()->json(['success' => false, 'error' => 'Notification not found'], 404);
            }

            $notification->read_at = now();
            $notification->save();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Mark as read error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark all CRM notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['success' => false, 'error' => 'User not authenticated'], 401);
            }

            // Update all unread CRM notifications
            DatabaseNotification::where('notifiable_id', $user->id)
                ->where('notifiable_type', get_class($user))
                ->whereNull('read_at')
                ->where('type', 'App\\Notifications\\CrmTaskNotification')
                ->update(['read_at' => now()]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'All notifications marked as read']);
            }

            return back()->with('success', 'All notifications marked as read.');
        } catch (\Exception $e) {
            Log::error('Mark all as read error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete all read notifications
     */
    public function destroyRead(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'error' => 'User not authenticated'], 401);
                }
                return redirect()->route('login');
            }

            // Delete only read notifications
            $count = DatabaseNotification::where('notifiable_id', $user->id)
                ->where('notifiable_type', get_class($user))
                ->whereNotNull('read_at')
                ->where('type', 'App\\Notifications\\CrmTaskNotification')
                ->delete();

            Log::info('Read notifications cleared', [
                'user_id' => $user->id,
                'count' => $count
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$count} read notifications cleared successfully",
                    'count' => $count
                ]);
            }

            return back()->with('success', "{$count} read notifications cleared.");
        } catch (\Exception $e) {
            Log::error('Destroy read error: ' . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Failed to clear notifications'], 500);
            }

            return back()->with('error', 'Failed to clear notifications: ' . $e->getMessage());
        }
    }

    /**
     * Delete a notification via AJAX
     */
    public function deleteNotification(Request $request, $id)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['success' => false, 'error' => 'Unauthenticated'], 401);
            }

            $notification = DatabaseNotification::where('id', $id)
                ->where('notifiable_id', $user->id)
                ->where('notifiable_type', get_class($user))
                ->first();

            if (!$notification) {
                return response()->json(['success' => false, 'error' => 'Notification not found'], 404);
            }

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Delete notification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show notification settings page
     */
    public function settings()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return redirect()->route('login');
            }

            // Get current preferences or set defaults
            $preferences = $user->crm_notification_preferences ?? [
                'task_assigned' => true,
                'task_due_today' => true,
                'task_upcoming' => true,
                'task_overdue' => true,
                'email_notifications' => false,
            ];

            return view('crm.notifications.settings', compact('preferences'));
        } catch (\Exception $e) {
            Log::error('Settings page error: ' . $e->getMessage());
            return redirect()->route('crm.dashboard')->with('error', 'Unable to load settings');
        }
    }

    /**
     * Update notification preferences
     */
    public function updateSettings(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return redirect()->route('login');
            }

            // Convert checkbox values (they come as 'on' or null)
            $preferences = [
                'task_assigned' => $request->has('task_assigned'),
                'task_due_today' => $request->has('task_due_today'),
                'task_upcoming' => $request->has('task_upcoming'),
                'task_overdue' => $request->has('task_overdue'),
                'email_notifications' => $request->has('email_notifications'),
            ];

            $user->crm_notification_preferences = $preferences;
            $user->save();

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Preferences updated']);
            }

            return redirect()->back()->with('success', 'Notification preferences updated successfully.');
        } catch (\Exception $e) {
            Log::error('Update settings error: ' . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()]);
            }

            return redirect()->back()->with('error', 'Failed to save preferences: ' . $e->getMessage());
        }
    }

    /**
     * Get all notifications (combined) with pagination
     */
    public function all(Request $request)
    {
        $user = Auth::user();
        $filter = $request->get('filter', 'all');

        $query = $user->notifications();

        // Apply filter
        if ($filter !== 'all') {
            switch ($filter) {
                case 'crm_task':
                    $query->where('type', 'App\\Notifications\\CrmTaskNotification');
                    break;
                case 'student_added':
                    $query->whereJsonContains('data->type', 'student_added');
                    break;
                case 'application':
                    $query->whereJsonContains('data->type', 'application_%');
                    break;
                case 'user_registered':
                    $query->whereJsonContains('data->type', 'user_registered');
                    break;
            }
        }

        $notifications = $query->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        if ($request->wantsJson()) {
            return response()->json($notifications);
        }

        return view('crm.notifications.index');
    }
}
