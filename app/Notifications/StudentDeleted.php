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
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $deletedBy = $this->admin->business_name ?? $this->admin->username ?? $this->admin->name;

        return (new MailMessage)
            ->subject('Student Deleted')
            ->greeting('Hello!')
            ->line("The student {$this->student->first_name} {$this->student->last_name} was deleted by {$deletedBy}.")
            ->action('View Students', $this->getActivityLink($notifiable, 'student_deleted', $this->student));
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
