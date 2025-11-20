<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Helpers\ActivityLogger;
use App\Helpers\HasActivityLink;

class StudentStatusUpdated extends Notification
{
    use Queueable, HasActivityLink;

    protected $student;

    public function __construct(Student $student)
    {
        $this->student = $student;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $studentName = trim($this->student->first_name . ' ' . $this->student->last_name);
        $status = ucfirst($this->student->status);

        // Get dynamic link based on notifiable
        $link = $this->getActivityLink($notifiable, 'student_status_updated', $this->student);

        // Log the activity
        ActivityLogger::log(
            'student_status_updated',
            "📘 Updated status to {$status} for student {$studentName}",
            $this->student->id,
            $link,
            $notifiable->id
        );

        return [
            'type' => 'student_status_updated',
            'student_id' => $this->student->id,
            'student_name' => $studentName,
            'new_status' => $status,
            'message' => "Student **{$studentName}** status has been updated to **{$status}**.",
            'link' => $link,
        ];
    }
}
