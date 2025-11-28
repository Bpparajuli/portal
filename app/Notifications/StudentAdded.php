<?php

namespace App\Notifications;

use App\Models\Student;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\ActivityLogger;
use App\Helpers\HasActivityLink;

class StudentAdded extends Notification
{
    use Queueable, HasActivityLink;

    public $agent, $student;

    public function __construct(User $agent, Student $student)
    {
        $this->agent = $agent;
        $this->student = $student;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toMail($notifiable)
    {
        $student = $this->student;
        $addedBy = $this->agent->business_name ?? $this->agent->username ?? $this->agent->name;

        $introLines = [
            "A new student, <strong>{$student->first_name} {$student->last_name}</strong>, has been added.",
            "Added by: <strong>{$addedBy}</strong>"
        ];

        return (new MailMessage)
            ->subject('New Student Added')
            ->view('emails.layout', [
                'subject'    => 'New Student Added',
                'greeting'   => "Hello {$notifiable->name},",
                'introLines' => $introLines,
                'actionText' => 'View Student',
                'actionUrl'  => $this->getActivityLink($notifiable, 'student_added', $student),
                'outroLines' => ['Please review the student details if necessary.']
            ]);
    }


    public function toArray($notifiable)
    {
        $link = $this->getActivityLink($notifiable, 'student_added', $this->student);

        ActivityLogger::log(
            'student_added',
            "ðŸ‘¤ Student added: {$this->student->first_name} {$this->student->last_name} by {$this->agent->business_name}",
            $this->student->id,
            $link,
            $this->agent->id
        );

        return [
            'type' => 'student_added',
            'message' => "New student added: {$this->student->first_name} {$this->student->last_name} by " .
                ($this->agent->business_name ?? $this->agent->username ?? $this->agent->name),
            'student' => [
                'id' => $this->student->id,
                'name' => "{$this->student->first_name} {$this->student->last_name}",
            ],
            'added_by' => [
                'id' => $this->agent->id,
                'name' => $this->agent->business_name ?? $this->agent->username ?? $this->agent->name,
            ],
            'link' => $link,
        ];
    }
}
