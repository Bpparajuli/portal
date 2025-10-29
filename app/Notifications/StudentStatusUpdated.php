<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Helpers\ActivityLogger;

class StudentStatusUpdated extends Notification
{
    use Queueable;
    protected $student;

    public function __construct(Student $student)
    {
        $this->student = $student;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        ActivityLogger::log(
            'student_status_changed', // type
            "Changed status to {$this->student->status} for student {$this->student->first_name} {$this->student->last_name}", // description
            $this->student->id, // notifiable_id
            route('agent.students.show', $this->student->id), // optional link to view
            $notifiable->id // optional user_id (agent who performed action)
        );
        return [
            'message' => 'The process for ' . $this->student->name . ' has been updated to ' . $this->student->status,
            'student_id' => $this->student->id,
            'type' => 'student_status', // <- important!
            'link' => url("/agent/students/{$this->student->id}"), // <- link to the admin student show page

        ];
    }
}
