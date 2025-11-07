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

class DocumentDeleted extends Notification // implements ShouldQueue
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
            ->subject('New Document Deleted')
            ->greeting('Hello!')
            ->line("A new document ({$doc->file_name}) was deleted for student {$student->first_name} {$student->last_name}.")
            ->action('View Documents', url("/admin/students/{$this->student->id}/documents"));
    }

    public function toArray($notifiable)
    {
        $doc = $this->document;
        $student = $doc->student;
        $agent = $this->agent;

        ActivityLogger::log(
            'document_deleted',
            "Document deleted: {$doc->document_type} ({$doc->file_name}) for {$student->first_name} {$student->last_name} by {$agent->business_name}",
            $doc->id,
            route('agent.documents.index', $student->id),
            $agent->id
        );

        return [
            'type' => 'document_deleted',
            'message' => "{$doc->document_type} document deleted for {$student->first_name} {$student->last_name} by {$agent->owner_name}",
            'document_id' => $doc->id,
            'file_name' => $doc->file_name,
            'document_type' => $doc->document_type,
            'student' => [
                'id' => $student->id,
                'name' => $student->first_name . ' ' . $student->last_name,
            ],
            'deleted_by' => [
                'id' => $agent->id,
                'name' => $agent->owner_name,
            ],
            'link' => route(
                $notifiable->is_admin ? 'admin.students.show' : 'agent.documents.index',
                $student->id
            ),
        ];
    }
}
