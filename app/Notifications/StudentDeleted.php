<?php

namespace App\Notifications;

use App\Models\Student;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Helpers\ActivityLogger;

class StudentDeleted extends Notification
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
        return (new MailMessage)
            ->subject('Student Deleted')
            ->greeting('Hello!')
            ->line(
                "The student {$this->student->first_name} {$this->student->last_name} was deleted by " .
                    ($this->agent->business_name ?? $this->agent->username) . "."
            )
            ->action('View Students', url('/admin/students'));
    }

    public function toArray($notifiable)
    {
        // Log activity properly
        ActivityLogger::log(
            'student_deleted', // ✅ proper type
            "🗑️ Student deleted: {$this->student->first_name} {$this->student->last_name}",
            $this->student->id, // optional: link to the student
            route($notifiable->is_admin ? 'admin.users.show' : 'agent.students.show', $this->student->id),
            $this->agent->id // user who performed the action
        );

        return [
            'type'    => 'student_deleted',
            'message' => "Student deleted: {$this->student->first_name} {$this->student->last_name} by " .
                ($this->agent->business_name ?? $this->agent->username ?? $this->agent->name) . ".",
            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->first_name . ' ' . $this->student->last_name,
            ],
            'deleted_by' => [
                'id' => $this->agent->id,
                'name' => $this->agent->business_name ?? $this->agent->username ?? $this->agent->name,
            ],
            'link' => route($notifiable->is_admin ? 'admin.users.show' : 'agent.students.show', $this->student->id),
        ];
    }
}
