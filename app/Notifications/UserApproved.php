<?php

namespace App\Notifications;

use App\Helpers\ActivityLogger;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class UserApproved extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Account Has Been Approved')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Good news! Your account has been approved by the admin.')
            ->line('You can now log in and start using the portal.')
            ->action('Login Now', route('login'))
            ->line('Thank you for registering with us!');
    }

    public function toDatabase(object $notifiable): array
    {
        ActivityLogger::log("Approved user: {$notifiable->business_name}");

        return [
            'message' => 'Your account has been approved. You can now log in.',
        ];
    }
}
