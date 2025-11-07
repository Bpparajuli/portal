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

    /**
     * Create a new notification instance.
     */
    public function __construct(Student $student)
    {
        $this->student = $student;
    }

    /**
     * Define delivery channels.
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Store notification in the database.
     */
    public function toDatabase($notifiable)
    {
        $studentName = trim($this->student->first_name . ' ' . $this->student->last_name);
        $status = ucfirst($this->student->status);
        $link = route('agent.students.show', $this->student->id);

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
