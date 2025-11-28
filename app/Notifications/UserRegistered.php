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
        $businessname = $this->newUser->business_name ?? $this->newUser->owner_name ?? $this->newUser->name;
        return (new MailMessage)
            ->subject('New User Registration Pending Approval')
            ->view('emails.layout', [
                'subject'    => 'New User Registration Pending Approval',
                'greeting'   => 'Hello Admin!',
                'introLines' => [
                    "A new user <strong>{$businessname}</strong> has registered and is awaiting approval.",
                    "Owner:<strong>{$this->newUser->owner_name}</strong>",
                    "Email: <strong>{$this->newUser->email}</strong>",
                    "Contact: <strong>{$this->newUser->contact}</strong>",
                ],
                'actionText' => 'View Pending Users',
                'actionUrl'  => route('admin.users.waiting'),
                'outroLines' => [
                    "Please review and approve this user to activate their account."
                ]
            ]);
    }


    public function toArray($notifiable)
    {
        $displayName = $this->newUser->business_name ?? $this->newUser->owner_name ?? $this->newUser->name;
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
