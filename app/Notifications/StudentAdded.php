<?php

namespace App\Notifications;

use App\Models\Student;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\ActivityLogger;
use App\Helpers\HasActivityLink;

class StudentAdded extends Notification
{
    use Queueable, HasActivityLink;

    public $agent, $student;

    public function __construct(User $agent, Student $student)
    {
        $this->agent = $agent;
        $this->student = $student;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $addedBy = $this->agent->business_name ?? $this->agent->username ?? $this->agent->name;

        return (new MailMessage)
            ->subject('New Student Added')
            ->greeting('Hello!')
            ->line("A new student, {$this->student->first_name} {$this->student->last_name}, has been added by {$addedBy}.")
            ->action('View Student', $this->getActivityLink($notifiable, 'student_added', $this->student));
    }

    public function toArray($notifiable)
    {
        $link = $this->getActivityLink($notifiable, 'student_added', $this->student);

        ActivityLogger::log(
            'student_added',
            "👤 Student added: {$this->student->first_name} {$this->student->last_name} by {$this->agent->business_name}",
            $this->student->id,
            $link,
            $this->agent->id
        );

        return [
            'type' => 'student_added',
            'message' => "New student added: {$this->student->first_name} {$this->student->last_name} by " .
                ($this->agent->business_name ?? $this->agent->username ?? $this->agent->name),
            'student' => [
                'id' => $this->student->id,
                'name' => "{$this->student->first_name} {$this->student->last_name}",
            ],
            'added_by' => [
                'id' => $this->agent->id,
                'name' => $this->agent->business_name ?? $this->agent->username ?? $this->agent->name,
            ],
            'link' => $link,
        ];
    }
}
