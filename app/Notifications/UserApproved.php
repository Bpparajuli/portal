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
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Account Has Been Approved')
            ->greeting('Hello ' . $notifiable->business_name . ',')
            ->line('Good news! Your account has been approved by the Idea admin.')
            ->line('You can now log in and start using the portal.')
            ->action('Login Now', route('auth.login'))
            ->line('Thank you for registering with us!');
    }
}
