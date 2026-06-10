<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class CrmNotificationService
{
    /**
     * Fetch recent unread notifications for the CRM header.
     *
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    public function fetchRecent(int $limit = 10): \Illuminate\Support\Collection
    {
        return Auth::user()->notifications()
            ->latest()
            ->take($limit)
            ->get()
            ->map(function ($n) {
                return [
                    'id'        => $n->id,
                    'type'      => $n->type,
                    'data'      => $n->data,
                    'read_at'   => $n->read_at,
                    'created_at'=> $n->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(string $notificationId): void
    {
        $notification = Auth::user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    /**
     * Mark as read and return the redirect URL from notification data.
     */
    public function markAsReadAndRedirect(string $notificationId): string
    {
        $notification = Auth::user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();
        return $notification->data['link'] ?? '#';
    }

    /**
     * Delete all read notifications.
     */
    public function destroyRead(): void
    {
        Auth::user()->notifications()->whereNotNull('read_at')->delete();
    }

    /**
     * Delete a single notification.
     */
    public function deleteNotification(string $notificationId): void
    {
        Auth::user()->notifications()->where('id', $notificationId)->delete();
    }

    /**
     * Get paginated list of all notifications.
     */
    public function getAll(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Auth::user()->notifications()->paginate(20);
    }

    /**
     * Get the user's CRM notification preferences.
     */
    public function getSettings(): array
    {
        return Auth::user()->crm_notification_preferences ?? [];
    }

    /**
     * Update CRM notification preferences.
     */
    public function updateSettings(array $preferences): void
    {
        $user = Auth::user();
        $user->crm_notification_preferences = $preferences;
        $user->save();
    }
}
