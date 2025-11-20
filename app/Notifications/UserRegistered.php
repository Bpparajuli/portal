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

    public function __construct(User $newUser)
    {
        $this->newUser = $newUser;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $displayName = $this->newUser->business_name ?? $this->newUser->username ?? $this->newUser->name;

        return (new MailMessage)
            ->subject('New User Registration Pending Approval')
            ->greeting('Hello Admin!')
            ->line("A new user **{$displayName}** has registered and is awaiting approval.")
            ->line("**Email:** {$this->newUser->email}")
            ->action('View Pending Users', route('admin.users.waiting'))
            ->line('Please review and approve the user to activate their account.');
    }

    public function toArray($notifiable)
    {
        $displayName = $this->newUser->business_name ?? $this->newUser->username ?? $this->newUser->name;
        $link = route('admin.users.waiting');

        ActivityLogger::log(
            'user_registered',
            "🧾 New user registered: {$displayName}",
            $this->newUser->id,
            $link,
            $this->newUser->id
        );

        return [
            'type' => 'user_registered',
            'message' => "New user **{$displayName}** registered and is awaiting admin approval.",
            'user' => [
                'id' => $this->newUser->id,
                'name' => $displayName,
                'email' => $this->newUser->email,
            ],
            'link' => $link,
        ];
    }
}
