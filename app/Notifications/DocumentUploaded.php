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

class DocumentUploaded extends Notification
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
        $uploadedBy = $this->agent->business_name ?? $this->agent->username ?? $this->agent->name;

        $introLines = [
            "A new document (<strong>{$doc->document_type}: {$doc->file_name}</strong>) was uploaded for student <strong>{$student->first_name} {$student->last_name}</strong>.",
            "Uploaded by: <strong>{$uploadedBy}</strong>"
        ];

        return (new MailMessage)
            ->subject('Document Uploaded')
            ->view('emails.layout', [
                'subject'    => 'Document Uploaded',
                'greeting'   => "Hello {$notifiable->name},",
                'introLines' => $introLines,
                'actionText' => 'View Documents',
                'actionUrl'  => $this->getActivityLink($notifiable, 'document_uploaded', $student),
                'outroLines' => ['Please review the uploaded document for your records.']
            ]);
    }


    public function toArray($notifiable)
    {
        $doc = $this->document;
        $link = $this->getActivityLink($notifiable, 'document_uploaded', $this->student);

        ActivityLogger::log(
            'document_uploaded',
            "📤 {$doc->document_type} uploaded for {$this->student->first_name} {$this->student->last_name}",
            $this->student->id,
            $link,
            $this->agent->id
        );

        return [
            'type' => 'document_uploaded',
            'message' => "{$doc->document_type} uploaded for {$this->student->first_name} {$this->student->last_name} by " .
                ($this->agent->business_name ?? $this->agent->username ?? $this->agent->name),
            'document_id' => $doc->id,
            'student' => [
                'id' => $this->student->id,
                'name' => "{$this->student->first_name} {$this->student->last_name}",
            ],
            'uploaded_by' => [
                'id' => $this->agent->id,
                'name' => $this->agent->business_name ?? $this->agent->username ?? $this->agent->name,
            ],
            'link' => $link,
        ];
    }
}
