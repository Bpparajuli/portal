<?php

namespace App\Notifications;

use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\ActivityLogger;
use App\Helpers\HasActivityLink;

class DocumentDeleted extends Notification
{
    use Queueable, HasActivityLink;

    public $agent, $student, $document;

    public function __construct(User $agent, Student $student, Document $document)
    {
        $this->agent = $agent;
        $this->student = $student;
        $this->document = $document;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toMail($notifiable)
    {
        $doc = $this->document;
        $student = $this->student;
        $agentName = $this->agent->business_name ?? $this->agent->username ?? $this->agent->name;

        $introLines = [
            "A document (<strong>{$doc->file_name}</strong>) has been deleted for student <strong>{$student->first_name} {$student->last_name}</strong>.",
            "Deleted by: <strong>{$agentName}</strong>"
        ];

        return (new MailMessage)
            ->subject('Document Deleted')
            ->view('emails.layout', [
                'subject'    => 'Document Deleted',
                'greeting'   => "Hello {$notifiable->name},",
                'introLines' => $introLines,
                'actionText' => 'View Documents',
                'actionUrl'  => $this->getActivityLink($notifiable, 'document_deleted', $student),
                'outroLines' => ['Please review the student documents if necessary.']
            ]);
    }


    public function toArray($notifiable)
    {
        $doc = $this->document;
        $link = $this->getActivityLink($notifiable, 'document_deleted', $this->student);

        ActivityLogger::log(
            'document_deleted',
            "ðŸš® {$doc->document_type} deleted for {$this->student->first_name} {$this->student->last_name}",
            $this->student->id,
            $link,
            $this->agent->id
        );

        return [
            'type' => 'document_deleted',
            'message' => "{$doc->document_type} deleted for {$this->student->first_name} {$this->student->last_name} by " .
                ($this->agent->business_name ?? $this->agent->username ?? $this->agent->name),
            'document_id' => $doc->id,
            'student' => [
                'id' => $this->student->id,
                'name' => "{$this->student->first_name} {$this->student->last_name}",
            ],
            'deleted_by' => [
                'id' => $this->agent->id,
                'name' => $this->agent->business_name ?? $this->agent->username ?? $this->agent->name,
            ],
            'link' => $link,
        ];
    }
}
