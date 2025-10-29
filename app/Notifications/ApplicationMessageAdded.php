<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Application;
use App\Models\ApplicationMessage;

class ApplicationMessageAdded extends Notification
{
    use Queueable;

    protected $application;
    protected $message;

    public function __construct(Application $application, ApplicationMessage $message)
    {
        $this->application = $application;
        $this->message = $message;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail']; // Both in-app + email
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Message on Application #' . $this->application->application_number)
            ->line('A new message has been added to an application.')
            ->line('Message: "' . $this->message->message . '"')
            ->action('View Application', $this->getApplicationUrl($notifiable))
            ->line('Please review it.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'application_number' => $this->application->application_number ?? null,
            'message' => $this->message->message,
            'type' => 'application_message',
            'added_by' => $this->message->type,
            'link' => $this->getApplicationUrl($notifiable),
        ];
    }

    /**
     * Determine the correct route based on the recipient type.
     */
    protected function getApplicationUrl($notifiable)
    {
        // If the recipient is an admin
        if ($notifiable->is_admin) {
            return route('admin.applications.show', $this->application->id);
        }

        // Otherwise, assume agent
        return route('agent.applications.show', $this->application->id);
    }
}
