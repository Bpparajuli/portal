<?php

namespace App\Notifications;

use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Helpers\ActivityLogger;

class DocumentUploaded extends Notification
{
    use Queueable;

    public $agent;
    public $student;
    public $document;

    /**
     * @param User $agent
     * @param Student $student
     * @param Document $document
     */
    public function __construct(User $agent, Student $student, Document $document)
    {
        $this->agent = $agent;
        $this->student = $student;
        $this->document = $document;
    }

    /**
     * Notification channels
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Email representation
     */
    public function toMail($notifiable)
    {
        $doc = $this->document;
        $student = $this->student;
        $uploadedBy = $this->agent->business_name ?? $this->agent->username ?? $this->agent->name;

        return (new MailMessage)
            ->subject('New Document Uploaded')
            ->greeting('Hello!')
            ->line("A new document ({$doc->document_type}: {$doc->file_name}) was uploaded for student {$student->first_name} {$student->last_name} by {$uploadedBy}.")
            ->action('View Documents', url("/admin/students/{$student->id}/documents"));
    }

    /**
     * Database representation
     */
    public function toArray($notifiable)
    {
        $doc = $this->document;
        $student = $this->student;
        $uploadedBy = $this->agent;

        // Log activity
        ActivityLogger::log(
            'document_uploaded',
            "Document uploaded: {$doc->document_type} ({$doc->file_name}) for student {$student->first_name} {$student->last_name}",
            $doc->id,
            route($notifiable->is_admin ? 'admin.students.show' : 'agent.documents.index', $student->id),
            $uploadedBy->id
        );

        return [
            'type'          => 'document_uploaded',
            'message'       => "{$doc->document_type} document uploaded for {$student->first_name} {$student->last_name} by " .
                ($uploadedBy->business_name ?? $uploadedBy->username ?? $uploadedBy->name),
            'document_id'   => $doc->id,
            'file_name'     => $doc->file_name,
            'document_type' => $doc->document_type,
            'student'       => [
                'id'   => $student->id,
                'name' => $student->first_name . ' ' . $student->last_name,
            ],
            'uploaded_by'   => [
                'id'   => $uploadedBy->id,
                'name' => $uploadedBy->business_name ?? $uploadedBy->username ?? $uploadedBy->name,
            ],
            'link' => route(
                $notifiable->is_admin ? 'admin.students.show' : 'agent.documents.index',
                $student->id
            ),
        ];
    }
}
