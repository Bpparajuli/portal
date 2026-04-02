<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AgreementReminder extends Notification
{
    use Queueable;
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $userName = $this->user->business_name ?? $this->user->username ?? $this->user->name;

        $introLines = [
            "{$userName}, our records show that you have not uploaded your agreement yet.",
        ];

        return (new MailMessage)
            ->subject('Reminder: Upload Your Agreement')
            ->view('emails.layout', [
                'subject'    => 'Agreement Upload Reminder',
                'greeting'   => "Hello {$userName},",
                'introLines' => $introLines,
                'actionText' => 'Upload Agreement',
                'actionUrl'  => route('home'), // Change to your upload page route
                'outroLines' => [
                    'Please upload it as soon as possible to complete your registration process.',
                ]
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "{$this->user->business_name} has not uploaded their agreement yet.",
            'user_id' => $this->user->id,
            'url' => route('home'), // Link to upload page
        ];
    }
}
