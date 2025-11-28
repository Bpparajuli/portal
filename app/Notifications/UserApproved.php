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
     * Get the notification delivery channels.
     */
    public function via($notifiable)
    {
        // Only mail — no database notification
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $displayName = $notifiable->business_name ?? $notifiable->username ?? $notifiable->name;

        return (new MailMessage)
            ->subject('🎉 Your Account Has Been Approved!')
            ->view('emails.layout', [
                'subject'    => 'Your Account Has Been Approved',
                'greeting'   => "Hello {$displayName},",
                'introLines' => [
                    "Good news! Your account has been approved by the admin.",
                    "You can now log in and start using the portal."
                ],
                'actionText' => 'Login Now',
                'actionUrl'  => route('auth.login'),
                'outroLines' => [
                    "Thank you for registering with us — we’re excited to have you onboard!"
                ]
            ]);
    }

    /**
     * Log the activity without creating a notification.
     */
    public function toArray($notifiable)
    {
        $displayName = $notifiable->business_name ?? $notifiable->username ?? $notifiable->name;
        $link = route('auth.login');

        // Log activity for admin/internal tracking
        ActivityLogger::log(
            'user_approved',
            "✅ User approved: {$displayName}",
            $notifiable->id,
            $link,
            $notifiable->id
        );

        // Return array is optional, mainly for internal use
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
