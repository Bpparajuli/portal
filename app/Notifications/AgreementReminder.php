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

        // Create custom HTML content
        $customContent = '
    
        <div>
            <p>Dear <strong>' . e($userName) . '</strong>,</p>
            
            <p>This is a friendly reminder to submit your agreement document to complete your registration process.</p>
            
            <div style="background:#fff3cd; border-left:4px solid #ffc107; padding:15px; margin:20px 0;">
                <strong>⚠️ Important:</strong>
                <p style="margin:5px 0 0 0;">Your account will not be fully activated until the agreement is verified.</p>
            </div>
            
            <div style="text-align:center; margin-top:25px;">
                <a href="' . route('home') . '" style="background:#1a0262; color:white; padding:12px 30px; text-decoration:none; border-radius:5px;">
                    Login to Upload Agreement
                </a>
            </div>
        </div>
        
        <div style="margin-top:30px; padding-top:20px; border-top:1px solid #eee;">
            <p>Best regards,<br><strong>' . e(config('app.name')) . ' Team</strong></p>
        </div>';

        return (new MailMessage)
            ->subject('Reminder: Upload Your Agreement')
            ->view('emails.layout', [
                'subject' => 'Agreement Upload Reminder',
                'customContent' => $customContent
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
