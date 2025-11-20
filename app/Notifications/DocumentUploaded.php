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
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $doc = $this->document;
        $uploadedBy = $this->agent->business_name ?? $this->agent->username ?? $this->agent->name;

        return (new MailMessage)
            ->subject('Document Uploaded')
            ->greeting('Hello!')
            ->line("A new document ({$doc->document_type}: {$doc->file_name}) was uploaded for student {$this->student->first_name} {$this->student->last_name} by {$uploadedBy}.")
            ->action('View Documents', $this->getActivityLink($notifiable, 'document_uploaded', $this->student));
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
