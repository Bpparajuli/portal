<?php

namespace App\Notifications;

use App\Models\Application;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\ActivityLogger;

class ApplicationSubmitted extends Notification
{
    use Queueable;

    public $application;

    /**
     * Create a new notification instance.
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
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
        $agent = $app->agent;
        $university = $app->university;

        $submittedBy = $agent->business_name ?? $agent->username ?? $agent->name;

        return (new MailMessage)
            ->subject('New Application Submitted')
            ->greeting('Hello!')
            ->line("Agent **{$submittedBy}** has submitted a new application.")
            ->line("**Student:** {$student->first_name} {$student->last_name}")
            ->line("**University:** {$university->name}")
            ->action('View Application', url(route('admin.applications.show', $app->id)))
            ->line('Please review the application details.');
    }

    /**
     * Get the array representation of the notification (for database).
     */
    public function toArray($notifiable)
    {
        $app = $this->application;
        $student = $app->student;
        $agent = $app->agent;
        $university = $app->university;

        $link = route($notifiable->is_admin ? 'admin.applications.show' : 'agent.applications.show', $app->id);

        // Log activity
        ActivityLogger::log(
            'application_submitted',
            "📨 Application submitted for {$student->first_name} {$student->last_name} to {$university->name} by {$agent->name}",
            $app->id,
            $link,
            $agent->id
        );

        return [
            'type' => 'application_submitted',
            'message' => "New application submitted for {$student->first_name} {$student->last_name} to {$university->name} by " .
                ($agent->business_name ?? $agent->username ?? $agent->name) . ".",
            'application' => [
                'id' => $app->id,
                'number' => $app->application_number ?? null,
            ],
            'student' => [
                'id' => $student->id,
                'name' => "{$student->first_name} {$student->last_name}",
            ],
            'university' => [
                'id' => $university->id,
                'name' => $university->name,
            ],
            'submitted_by' => [
                'id' => $agent->id,
                'name' => $agent->business_name ?? $agent->username ?? $agent->name,
            ],
            'link' => $link,
        ];
    }
}
