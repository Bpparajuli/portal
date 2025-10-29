<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\ActivityLogger;

class ApplicationWithdrawn extends Notification
{
    use Queueable;

    protected $application;

    public function __construct(Application $application)
    {
        $this->application = $application;

        ActivityLogger::log(
            'application_withdrawn', // type
            "Withdrawn application for student {$this->application->student->first_name} {$this->application->student->last_name}", // description
            $this->application->id, // notifiable_id
            route('agent.applications.show', $this->application->id), // optional link
            $this->application->agent_id // optional user_id (agent who performed action)
        );
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Application Withdrawn')
            ->line('Agent ' . $this->application->agent->username . ' has Withdrawn a application. of ' . $this->application->student->first_name . ' ' . $this->application->student->last_name)
            ->line('Student: ' . $this->application->student->first_name . ' ' . $this->application->student->last_name)
            ->line('University: ' . $this->application->university->name)
            ->action('View Application', url(route('admin.applications.show', $this->application->id)))
            ->line('Please review the application.');
    }

    public function toDatabase($notifiable)
    {

        return [
            'agent_id' => $this->application->agent_id,
            'agent_name' => $this->application->agent->name,
            'student_id' => $this->application->student_id,
            'student_name' => $this->application->student->first_name . ' ' . $this->application->student->last_name,
            'university_id' => $this->application->university_id,
            'university_name' => $this->application->university->name,
            'application_id' => $this->application->id,
            'message' => 'Application Withdrawn for ' . $this->application->student->first_name . ' ' . $this->application->student->last_name . ' to ' . $this->application->university->name . ' by ' . $this->application->agent->name,
            'type' => 'Application Withdrawn ',
            'link' => url(route('admin.applications.index', $this->application->id)),
        ];
    }
}
