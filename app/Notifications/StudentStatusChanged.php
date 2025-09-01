<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StudentStatusChanged extends Notification
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
        return [
            'message' => 'The process for ' . $this->student->name . ' has been updated to ' . $this->student->status,
            'student_id' => $this->student->id,
            'type' => 'Student_status', // <- important!
            'link' => url("/agent/students/{$this->student->id}"), // <- link to the admin student show page

        ];
    }
}
