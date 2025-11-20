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
     *
     * @param Application $application
     * @param ApplicationMessage $message
     */
    public function __construct(Application $application, ApplicationMessage $message)
    {
        $this->application = $application;
        $this->message = $message;
    }

    /**
     * Get the notification delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $app = $this->application;
        $msg = $this->message;
        $addedBy = $msg->user->business_name ?? $msg->user->username ?? $msg->user->name;

        return (new MailMessage)
            ->subject("New Message on Application #{$app->application_number}")
            ->greeting('Hello!')
            ->line("A new message has been added to application **#{$app->application_number}** by {$addedBy}.")
            ->line("Message: \"{$msg->message}\"")
            ->action('View Application', $this->getActivityLink($notifiable, 'application_message_added', $msg))
            ->line('Please review and respond if needed.');
    }

    /**
     * Get the array representation of the notification (for database storage).
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $app = $this->application;
        $msg = $this->message;
        $student = $app->student;

        // Generate correct link for agent/admin
        $link = $this->getActivityLink($notifiable, 'application_message_added', $msg);

        // Log activity
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
                'name' => $msg->user->business_name ?? $msg->user->username ?? $msg->user->name,
                'type' => $msg->type ?? null,
            ],
            'message_text' => $msg->message,
            'link' => $link,
        ];
    }
}
