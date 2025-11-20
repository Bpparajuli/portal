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
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $doc = $this->document;
        return (new MailMessage)
            ->subject('Document Deleted')
            ->greeting('Hello!')
            ->line("A document ({$doc->file_name}) was deleted for student {$this->student->first_name} {$this->student->last_name}.")
            ->action('View Documents', $this->getActivityLink($notifiable, 'document_deleted', $this->student));
    }

    public function toArray($notifiable)
    {
        $doc = $this->document;
        $link = $this->getActivityLink($notifiable, 'document_deleted', $this->student);

        ActivityLogger::log(
            'document_deleted',
            "🚮 {$doc->document_type} deleted for {$this->student->first_name} {$this->student->last_name}",
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
