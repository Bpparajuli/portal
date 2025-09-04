<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\ActivityLogger;

class NewUserRegistered extends Notification
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
        ActivityLogger::log("New user registered: {$this->newUser->business_name}");

        return [
            'user_id' => $this->newUser->id,
            'name'    => $this->newUser->name,
            'email'   => $this->newUser->email,
            'message' => 'New user registered and awaiting approval.',
            'type' => 'user_registered', // <- important!
            'link' => route('admin.users.waiting'), // <- link to the admin pending users page

        ];
    }
}
