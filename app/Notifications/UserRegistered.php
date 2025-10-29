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

    protected $newUser;

    public function __construct(User $newUser)
    {
        $this->newUser = $newUser;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New User Registration Pending Approval')
            ->line('A new user has registered and is awaiting approval.')
            ->line('Name: ' . $this->newUser->name)
            ->line('Email: ' . $this->newUser->email)
            ->action('View Pending Users', route('admin.users.waiting'))
            ->line('Please approve the user to activate their account.');
    }

    public function toDatabase(object $notifiable): array
    {
        // Log activity safely, user_id null is allowed
        ActivityLogger::log(
            'user_registered', // type
            "New user registered: {$this->newUser->business_name}", // description
            null,               // notifiable_id (optional)
            route('admin.users.waiting'), // link (optional)
            null                // user_id (optional; defaults to Auth::id())
        );


        return [
            'user_id' => $this->newUser->id,
            'name'    => $this->newUser->name,
            'email'   => $this->newUser->email,
            'message' => 'New user registered and awaiting approval.',
            'type' => 'user_registered',
            'link' => route('admin.users.waiting'),
        ];
    }
}
