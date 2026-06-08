<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\Notification;

class NotificationDispatcher
{
    public function send(User $user, Notification $notification): void
    {
        $user->notify($notification);
    }

    public function sendToAdmins(Notification $notification): void
    {
        User::admins()->each(fn($admin) => $admin->notify($notification));
    }

    public function sendToAgent(int $agentId, Notification $notification): void
    {
        User::find($agentId)?->notify($notification);
    }
}
