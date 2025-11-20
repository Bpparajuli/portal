<?php

namespace App\Notifications;

use App\Models\Application;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\ActivityLogger;
use App\Helpers\HasActivityLink;

class ApplicationStatusUpdated extends Notification
{
    use Queueable, HasActivityLink;

    public $application, $updatedBy;

    public function __construct(Application $application, User $updatedBy)
    {
        $this->application = $application;
        $this->updatedBy = $updatedBy;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $app = $this->application;
        $student = $app->student;
        $updatedByName = $this->updatedBy->business_name ?? $this->updatedBy->username ?? $this->updatedBy->name;

        return (new MailMessage)
            ->subject('Application Status Updated')
            ->greeting('Hello!')
            ->line("The application for student {$student->first_name} {$student->last_name} has been updated by {$updatedByName}.")
            ->line("New Status: **{$app->application_status}**")
            ->action('View Application', $this->getActivityLink($notifiable, 'application_status_updated', $app));
    }

    public function toArray($notifiable)
    {
        $app = $this->application;
        $student = $app->student;

        $link = $this->getActivityLink($notifiable, 'application_status_updated', $app);

        ActivityLogger::log(
            'application_status_updated',
            "📄 Status updated to {$app->application_status} for {$student->first_name} {$student->last_name} by {$this->updatedBy->name}.",
            $app->id,
            $link,
            $this->updatedBy->id
        );

        return [
            'type' => 'application_status_updated',
            'message' => "📄 Status updated to {$app->application_status} for {$student->first_name} {$student->last_name} by {$this->updatedBy->name}.",
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
                'id' => $this->updatedBy->id,
                'name' => $this->updatedBy->business_name ?? $this->updatedBy->username ?? $this->updatedBy->name,
            ],
            'link' => $link,
        ];
    }
}
