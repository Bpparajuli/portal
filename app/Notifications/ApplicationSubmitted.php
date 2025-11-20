<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\ActivityLogger;
use App\Helpers\HasActivityLink;

class ApplicationSubmitted extends Notification
{
    use Queueable, HasActivityLink;

    public $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

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
            ->action('View Application', $this->getActivityLink($notifiable, 'application_submitted', $app));
    }

    public function toArray($notifiable)
    {
        $app = $this->application;
        $student = $app->student;
        $agent = $app->agent;
        $university = $app->university;
        $course = $app->course;

        $link = $this->getActivityLink($notifiable, 'application_submitted', $app);

        ActivityLogger::log(
            'application_submitted',
            "📨 Application submitted for {$student->first_name} {$student->last_name} to {$university->short_name} on {$course->title}.",
            $app->id,
            $link,
            $agent->id
        );

        return [
            'type' => 'application_submitted',
            'message' => "New application submitted for {$student->first_name} {$student->last_name} to {$university->short_name} on {$course->title}.",
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
