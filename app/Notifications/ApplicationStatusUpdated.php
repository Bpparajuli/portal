<?php

namespace App\Notifications;

use App\Models\Application;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\ActivityLogger;

class ApplicationStatusUpdated extends Notification
{
    use Queueable;

    public $application, $updatedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Application $application, User $updatedBy)
    {
        $this->application = $application;
        $this->updatedBy = $updatedBy;
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
        $student = $app->student;
        $updatedBy = $this->updatedBy->business_name ?? $this->updatedBy->username ?? $this->updatedBy->name;

        return (new MailMessage)
            ->subject('Application Status Updated')
            ->greeting('Hello!')
            ->line("The application for student {$student->first_name} {$student->last_name} has been updated by {$updatedBy}.")
            ->line("New Status: **{$app->application_status}**")
            ->action('View Application', url("/admin/applications/{$app->id}"));
    }

    /**
     * Get the array representation of the notification (for database).
     */
    public function toArray($notifiable)
    {
        $app = $this->application;
        $student = $app->student;
        $updatedBy = $this->updatedBy;

        // Determine route based on user role
        $link = route($notifiable->is_admin ? 'admin.applications.show' : 'agent.applications.show', $app->id);

        // Log the activity
        ActivityLogger::log(
            'application_status_updated',
            "📄 Application status updated to {$app->application_status} for {$student->first_name} {$student->last_name}",
            $app->id,
            $link,
            $updatedBy->id
        );

        return [
            'type' => 'application_status_updated',
            'message' => "Application status updated to {$app->application_status} for {$student->first_name} {$student->last_name} by " .
                ($updatedBy->business_name ?? $updatedBy->username ?? $updatedBy->name) . ".",
            'application' => [
                'id' => $app->id,
                'number' => $app->application_number,
                'status' => $app->application_status,
            ],
            'student' => [
                'id' => $student->id,
                'name' => "{$student->first_name} {$student->last_name}",
            ],
            'updated_by' => [
                'id' => $updatedBy->id,
                'name' => $updatedBy->business_name ?? $updatedBy->username ?? $updatedBy->name,
            ],
            'link' => $link,
        ];
    }
}
