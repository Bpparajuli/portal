<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\ActivityLogger;

class UserApproved extends Notification
{
    use Queueable;

    /**
     * Get the delivery channels for the notification.
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
        $displayName = $notifiable->business_name ?? $notifiable->username ?? $notifiable->name;

        return (new MailMessage)
            ->subject('🎉 Your Account Has Been Approved!')
            ->greeting("Hello {$displayName},")
            ->line('Good news! Your account has been approved by the admin.')
            ->line('You can now log in and start using the portal.')
            ->action('Login Now', route('auth.login'))
            ->line('Thank you for registering with us — we’re excited to have you onboard!');
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toArray($notifiable)
    {
        $displayName = $notifiable->business_name ?? $notifiable->username ?? $notifiable->name;
        $link = route('auth.login');

        // Log activity
        ActivityLogger::log(
            'user_approved',
            "✅ User approved: {$displayName}",
            $notifiable->id,
            $link,
            $notifiable->id
        );

        return [
            'type' => 'user_approved',
            'message' => "Your account (**{$displayName}**) has been approved by the admin. You can now log in.",
            'user' => [
                'id' => $notifiable->id,
                'name' => $displayName,
                'email' => $notifiable->email,
            ],
            'link' => $link,
        ];
    }
}
