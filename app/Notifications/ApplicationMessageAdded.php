<?php

namespace App\Notifications;

use App\Models\Application;
use App\Models\ApplicationMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\ActivityLogger;
use App\Helpers\HasActivityLink;

class ApplicationMessageAdded extends Notification
{
    use Queueable, HasActivityLink;

    public $application;
    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Application $application, ApplicationMessage $message)
    {
        $this->application = $application;
        $this->message = $message;
    }

    /**
     * Get the notification delivery channels.
     */
    public function via($notifiable)
    {
        // Send to database and optionally email
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $app = $this->application;
        $msg = $this->message;
        $addedBy = $msg->user->business_name ?? $msg->user->username ?? $msg->user->name;

        $introLines = [
            "A new message has been added to application <strong>#{$app->application_number}</strong> by <strong>{$addedBy}</strong>.",
            "Message: \"{$msg->message}\""
        ];

        return (new MailMessage)
            ->subject("New Message on Application #{$app->application_number}")
            ->view('emails.layout', [
                'subject'    => "New Message on Application #{$app->application_number}",
                'greeting'   => "Hello {$notifiable->name},",
                'introLines' => $introLines,
                'actionText' => 'View Application',
                'actionUrl'  => $this->getActivityLink($notifiable, 'application_message_added', $msg),
                'outroLines' => ['Please review and respond if needed.']
            ]);
    }

    /**
     * Get the array representation of the notification (for database storage).
     */
    public function toArray($notifiable)
    {
        $app = $this->application;
        $msg = $this->message;
        $student = $app->student;

        $link = $this->getActivityLink($notifiable, 'application_message_added', $msg);

        ActivityLogger::log(
            'application_message_added',
            "💬 New message for {$student->first_name} {$student->last_name} by {$msg->user->name}",
            $app->id,
            $link,
            $msg->user->id
        );

        return [
            'type' => 'application_message_added',
            'message' => "💬 New message for {$student->first_name} {$student->last_name} by {$msg->user->name}.",
            'application' => [
                'id' => $app->id,
                'number' => $app->application_number,
            ],
            'student' => [
                'id' => $student->id,
                'name' => "{$student->first_name} {$student->last_name}",
            ],
            'added_by' => [
                'id' => $msg->user->id,
                'name' => $msg->user->business_name,
                'type' => $msg->type ?? null,
            ],
            'message_text' => $msg->message,
            'link' => $link,
        ];
    }
}
