<?php

namespace App\Notifications;

use App\Models\Student;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Helpers\ActivityLogger;

class StudentAdded extends Notification // implements ShouldQueue
{
    use Queueable;

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
        $addedBy = $this->agent->business_name ?? $this->agent->username;
        return (new MailMessage)
            ->subject('New Student Added')
            ->greeting('Hello!')
            ->line("A new student, {$this->student->first_name} {$this->student->last_name}, has been added by {$addedBy}.")
            ->action('View Student', url("/admin/students/{$this->student->id}"));
    }

    public function toArray($notifiable)
    {
        ActivityLogger::log(
            'student_added', // ✅ proper type
            "👤 Student added: {$this->student->first_name} {$this->student->last_name}",
            $this->student->id,
            route($notifiable->is_admin ? 'admin.students.show' : 'agent.students.show', $this->student->id),
            $this->agent->id
        );

        return [
            'type'      => 'student_added',
            'message'   => "New student added: {$this->student->first_name} {$this->student->last_name} by " .
                ($this->agent->business_name ?? $this->agent->username ?? $this->agent->name) . ".",
            'student'   => [
                'id' => $this->student->id,
                'name' => $this->student->first_name . ' ' . $this->student->last_name,
            ],
            'added_by'  => [
                'id' => $this->agent->id,
                'name' => $this->agent->business_name ?? $this->agent->username ?? $this->agent->name,
            ],
            'link' => route(
                $notifiable->is_admin ? 'admin.students.show' : 'agent.students.show',
                $this->student->id
            ),
        ];
    }
}
