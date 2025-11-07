<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\ActivityLogger;

class UserRegistered extends Notification
{
    use Queueable;

    public $newUser;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $newUser)
    {
        $this->newUser = $newUser;
    }

    /**
     * Get the notification delivery channels.
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $user = $this->newUser;
        $displayName = $user->business_name ?? $user->username ?? $user->name;

        return (new MailMessage)
            ->subject('New User Registration Pending Approval')
            ->greeting('Hello Admin!')
            ->line("A new user **{$displayName}** has registered and is awaiting approval.")
            ->line("**Email:** {$user->email}")
            ->action('View Pending Users', route('admin.users.waiting'))
            ->line('Please review and approve the user to activate their account.');
    }

    /**
     * Get the array representation of the notification (for database).
     */
    public function toArray($notifiable)
    {
        $user = $this->newUser;
        $displayName = $user->business_name ?? $user->username ?? $user->name;
        $link = route('admin.users.waiting');

        // Log activity
        ActivityLogger::log(
            'user_registered',
            "🧾 New user registered: {$displayName}",
            $user->id,
            $link,
            $user->id
        );

        return [
            'type' => 'user_registered',
            'message' => "New user **{$displayName}** registered and is awaiting admin approval.",
            'user' => [
                'id' => $user->id,
                'name' => $displayName,
                'email' => $user->email,
            ],
            'link' => $link,
        ];
    }
}
