<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // optional if you use queues
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Helpers\ActivityLogger;
use App\Models\Student;
use App\Models\User;

class DocumentUploaded extends Notification // implements ShouldQueue
{
    use Queueable;

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
        $student = $doc->student;

        return (new MailMessage)
            ->subject('New Document Uploaded')
            ->greeting('Hello!')
            ->line("A new document ({$doc->file_name}) was uploaded for student {$student->first_name} {$student->last_name}.")
            ->action('View Documents', url("/admin/students/{$this->student->id}/documents"));
    }

    public function toArray($notifiable)
    {
        $doc = $this->document;
        $student = $doc->student;
        $uploader = $doc->uploader;
        ActivityLogger::log("Uploaded document: {$this->document->title}", $this->document->uploaded_by);

        return [
            'type'        => 'document_uploaded',
            'document_id' => $doc->id,
            'file_name'   => $doc->file_name,
            'document_type' => $doc->document_type,
            'student'     => [
                'id' => $student->id,
                'name' => $student->first_name . ' ' . $student->last_name,
            ],
            'uploaded_by' => [
                'id' => $uploader->id,
                'name' => $uploader->business_name ?? $uploader->username ?? $uploader->name,
            ],
            'link' => route(
                $notifiable->is_admin ? 'admin.documents.index' : 'agent.documents.index',
                $student->id
            ),
        ];
    }
}
