<?php

namespace App\Notifications;

use App\Models\Student;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\ActivityLogger;

class StudentDeleted extends Notification
{
    use Queueable;

    public $admin, $student;

    public function __construct(User $admin, Student $student)
    {
        $this->admin = $admin;
        $this->student = $student;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $deletedBy = $this->admin->business_name ?? $this->admin->username ?? $this->admin->name;
        return (new MailMessage)
            ->subject('Student Deleted')
            ->greeting('Hello!')
            ->line("The student {$this->student->first_name} {$this->student->last_name} was deleted by {$deletedBy}.")
            ->action('View Students', url('/agent/students'));
    }

    public function toArray($notifiable)
    {
        ActivityLogger::log(
            'student_deleted',
            "🗑️ Student deleted: {$this->student->first_name} {$this->student->last_name}",
            $this->student->id,
            route('agent.students.show', $this->student->id),
            $this->admin->id
        );

        return [
            'type' => 'student_deleted',
            'message' => "Student deleted: {$this->student->first_name} {$this->student->last_name} by " .
                ($this->admin->business_name ?? $this->admin->username ?? $this->admin->name) . ".",
            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->first_name . ' ' . $this->student->last_name,
            ],
            'deleted_by' => [
                'id' => $this->admin->id,
                'name' => $this->admin->business_name ?? $this->admin->username ?? $this->admin->name,
            ],
            'link' => route('agent.students.show', $this->student->id),
        ];
    }
}
