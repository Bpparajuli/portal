<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\ActivityLogger;

class NewApplicationSubmitted extends Notification
{
    use Queueable;

    protected $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
        ActivityLogger::log("Submitted new application for student {$this->application->student->first_name} {$this->application->student->last_name}");
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Application Submitted')
            ->line('Agent ' . $this->application->agent->username . ' has submitted a new application.')
            ->line('Student: ' . $this->application->student->first_name . ' ' . $this->application->student->last_name)
            ->line('University: ' . $this->application->university->name)
            ->action('View Application', url(route('admin.applications.show', $this->application->id)))
            ->line('Please review the application.');
    }

    public function toDatabase($notifiable)
    {

        return [
            'agent_id' => $this->application->agent_id,
            'agent_name' => $this->application->agent->username,
            'student_id' => $this->application->student_id,
            'student_name' => $this->application->student->first_name . ' ' . $this->application->student->last_name,
            'university_id' => $this->application->university_id,
            'university_name' => $this->application->university->name,
            'application_id' => $this->application->id,
            'message' => 'New application submitted for  ',
            'type' => 'Application Submitted ',
            'link' => url(route('admin.applications.index', $this->application->id)),
        ];
    }
}
