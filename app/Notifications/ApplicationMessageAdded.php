<?php

namespace App\Notifications;

use App\Models\Application;
use App\Models\ApplicationMessage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\ActivityLogger;

class ApplicationMessageAdded extends Notification
{
    use Queueable;

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
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $app = $this->application;
        $msg = $this->message;
        $addedBy = $msg->user->business_name ?? $msg->user->username ?? $msg->user->name;
        $link = $this->getApplicationUrl($notifiable);

        return (new MailMessage)
            ->subject('New Message on Application #' . $app->application_number)
            ->greeting('Hello!')
            ->line("A new message has been added to application **#{$app->application_number}** by {$addedBy}.")
            ->line("Message: \"{$msg->message}\"")
            ->action('View Application', $link)
            ->line('Please review and respond if needed.');
    }

    /**
     * Get the array representation of the notification (for database).
     */
    public function toArray($notifiable)
    {
        $app = $this->application;
        $msg = $this->message;
        $student = $app->student;
        $addedBy = $msg->user;

        $link = $this->getApplicationUrl($notifiable);

        // Log activity
        ActivityLogger::log(
            'application_message_added',
            "💬 New message added to application #{$app->application_number} by {$addedBy->name}",
            $app->id,
            $link,
            $addedBy->id
        );

        return [
            'type' => 'application_message_added',
            'message' => "New message added to application #{$app->application_number} for {$student->first_name} {$student->last_name} by " .
                ($addedBy->business_name ?? $addedBy->username ?? $addedBy->name) . ".",
            'application' => [
                'id' => $app->id,
                'number' => $app->application_number,
            ],
            'student' => [
                'id' => $student->id,
                'name' => "{$student->first_name} {$student->last_name}",
            ],
            'added_by' => [
                'id' => $addedBy->id,
                'name' => $addedBy->business_name ?? $addedBy->username ?? $addedBy->name,
                'type' => $msg->type, // 'admin' or 'agent'
            ],
            'message_text' => $msg->message,
            'link' => $link,
        ];
    }

    /**
     * Determine the correct application route based on the recipient type.
     */
    protected function getApplicationUrl($notifiable)
    {
        return $notifiable->is_admin
            ? route('admin.applications.show', $this->application->id)
            : route('agent.applications.show', $this->application->id);
    }
}
