<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AgreementVerified extends Notification
{
    use Queueable;

    public $user; // The agent whose agreement was verified

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $agentName = $this->user->business_name
            ?? $this->user->username
            ?? $this->user->name;

        $introLines = [
            "Your uploaded agreement has been verified by Idea Admin.",
            "You can now proceed to the login.",
            "Verified for: <strong>{$agentName}</strong>",
        ];

        return (new MailMessage)
            ->subject('🎉  Your Agreement is Verified')
            ->view('emails.layout', [
                'subject'    => 'Agreement Verified',
                'greeting'   => "Hello {$notifiable->business_name},",
                'introLines' => $introLines,
                'actionText' => 'View Agreement',
                'actionUrl'  => route('auth.login'),
                'outroLines' => [
                    'Thank you for completing your agreement.',
                ]
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'type'    => 'agreement_verified',
            'message' => "Your agreement has been verified by admin.",
            'agent' => [
                'id'   => $this->user->id,
                'name' => $this->user->business_name ?? $this->user->username ?? $this->user->name,
            ],
            'link'   => route('agent.users.show', $this->user->business_name_slug),
        ];
    }
}
