<?php

namespace App\Notifications;

use App\Models\Student;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\ActivityLogger;
use App\Helpers\HasActivityLink;

class StudentDeleted extends Notification
{
    use Queueable, HasActivityLink;

    public $admin, $student;

    public function __construct(User $admin, Student $student)
    {
        $this->admin = $admin;
        $this->student = $student;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toMail($notifiable)
    {
        $student = $this->student;
        $deletedBy = $this->admin->business_name ?? $this->admin->username ?? $this->admin->name;

        $introLines = [
            "The student <strong>{$student->first_name} {$student->last_name}</strong> has been deleted.",
            "Deleted by: <strong>{$deletedBy}</strong>"
        ];

        return (new MailMessage)
            ->subject('Student Deleted')
            ->view('emails.layout', [
                'subject'    => 'Student Deleted',
                'greeting'   => "Hello {$notifiable->name},",
                'introLines' => $introLines,
                'actionText' => 'View Students',
                'actionUrl'  => $this->getActivityLink($notifiable, 'student_deleted', $student),
                'outroLines' => ['Please note that this student record has been removed from the system.']
            ]);
    }


    public function toArray($notifiable)
    {
        $link = $this->getActivityLink($notifiable, 'student_deleted', $this->student);

        ActivityLogger::log(
            'student_deleted',
            "🗑️ Student deleted: {$this->student->first_name} {$this->student->last_name}",
            $this->student->id,
            $link,
            $this->admin->id
        );

        return [
            'type' => 'student_deleted',
            'message' => "Student deleted: {$this->student->first_name} {$this->student->last_name} by " .
                ($this->admin->business_name ?? $this->admin->username ?? $this->admin->name),
            'student' => [
                'id' => $this->student->id,
                'name' => "{$this->student->first_name} {$this->student->last_name}",
            ],
            'deleted_by' => [
                'id' => $this->admin->id,
                'name' => $this->admin->business_name ?? $this->admin->username ?? $this->admin->name,
            ],
            'link' => $link,
        ];
    }
}
