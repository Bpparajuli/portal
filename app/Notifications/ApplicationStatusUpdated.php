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

        $introLines = [
            "The application for student <strong>{$student->first_name} {$student->last_name}</strong> has been updated by <strong>{$updatedByName}</strong>.",
            "New Status: <strong>{$app->application_status}</strong>"
        ];

        return (new MailMessage)
            ->subject("Application #{$app->application_number} Status Updated")
            ->view('emails.layout', [
                'subject'    => "Application Status Updated",
                'greeting'   => "Hello {$notifiable->name},",
                'introLines' => $introLines,
                'actionText' => 'View Application',
                'actionUrl'  => $this->getActivityLink($notifiable, 'application_status_updated', $app),
                'outroLines' => ['Please review the updated status and take any necessary actions.']
            ]);
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
