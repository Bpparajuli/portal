<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Helpers\ActivityLogger;

class ApplicationStatusUpdated extends Notification // implements ShouldQueue
{
    use Queueable;

    public $application, $updatedBy;

    public function __construct(Application $application, $updatedBy)
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

        return (new MailMessage)
            ->subject('Application Status Updated')
            ->greeting('Hello!')
            ->line("The application for student {$student->first_name} {$student->last_name} has been updated.")
            ->line("New Status: **{$app->status}**")
            ->action('View Application', url("/admin/applications/{$app->id}"));
    }

    public function toArray($notifiable)
    {
        $app = $this->application;
        $student = $app->student;

        ActivityLogger::log(
            "Updated application status to {$app->status} for {$student->first_name} {$student->last_name}",
            $this->updatedBy->id
        );

        return [
            'type'        => 'application_status',
            'message'     => "Application status updated to {$app->status} for {$student->first_name} {$student->last_name}",
            'application' => [
                'id' => $app->id,
                'status' => $app->status,
            ],
            'student'     => [
                'id' => $student->id,
                'name' => $student->first_name . ' ' . $student->last_name,
            ],
            'updated_by'  => [
                'id' => $this->updatedBy->id,
                'name' => $this->updatedBy->business_name ?? $this->updatedBy->username ?? $this->updatedBy->name,
            ],
            'link' => route(
                $notifiable->is_admin ? 'admin.applications.show' : 'agent.applications.show',
                $app->id
            ),
        ];
    }
}
