<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewStudentAdded extends Notification
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
            'message' => 'A new student (' . $this->student->name . ') has been created by agent ' . $this->student->agent->business_name,
            'student_id' => $this->student->id,
            'type' => 'Student_Added', // <- important!
            'link' => route('admin.students.list'), // <- link to the admin pending users page

        ];
    }
}
