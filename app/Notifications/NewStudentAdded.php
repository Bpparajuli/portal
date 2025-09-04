<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Helpers\ActivityLogger;

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
        ActivityLogger::log("Added new student: {$this->student->name}", $this->student->created_by);

        return [
            'message' => 'A new student (' . $this->student->name . ') has been created by agent ' . $this->student->agent->business_name,
            'student_id' => $this->student->id,
            'type' => 'Student_Added', // <- important!
            'link' => route('admin.students.index'), // <- link to the admin pending users page

        ];
    }
}
