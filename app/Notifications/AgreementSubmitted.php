<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AgreementSubmitted extends Notification
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
        $agentName = $this->user->business_name
            ?? $this->user->username
            ?? $this->user->name;

        $introLines = [
            "{$this->user->business_name} Uploaded their agreement document.",
        ];

        return (new MailMessage)
            ->subject('Agreement File Uploaded ')
            ->view('emails.layout', [
                'subject'    => 'Agreement Uploaded',
                'greeting'   => "Hello Admin,",
                'introLines' => $introLines,
                'actionText' => 'View Agent',
                'actionUrl'  => route('agent.users.show', $this->user->business_name_slug),
                'outroLines' => [
                    'Please review the agreement file attached by the user.',
                ]
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "{$this->user->business_name} updated their agreement document.",
            'user_id' => $this->user->id,
            'url' => route('admin.users.show', $this->user->business_name_slug),
        ];
    }
}
