<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Get paginated notifications for the authenticated user.
     *
     * @param  int  $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getNotifications(int $perPage = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Auth::user()->notifications()->paginate($perPage);
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
     * Mark a single notification as unread.
     */
    public function markAsUnread(string $notificationId): void
    {
        $notification = Auth::user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();
        // Re-mark as unread
        Auth::user()->notifications()
            ->where('id', $notificationId)
            ->update(['read_at' => null]);
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    /**
     * Delete a single notification.
     */
    public function delete(string $notificationId): void
    {
        Auth::user()->notifications()->where('id', $notificationId)->delete();
    }

    /**
     * Delete all notifications for the authenticated user.
     */
    public function deleteAll(): void
    {
        Auth::user()->notifications()->delete();
    }

    /**
     * Find a notification by ID and redirect based on its type/link.
     *
     * @return array  ['notification' => ..., 'route' => ...]
     */
    public function readAndRedirect(string $notificationId): array
    {
        $notification = Auth::user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        $route = '#';
        if (isset($notification->data['link'])) {
            $route = $notification->data['link'];
        }

        return ['notification' => $notification, 'route' => $route];
    }
}
