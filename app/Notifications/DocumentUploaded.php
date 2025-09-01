<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DocumentUploaded extends Notification
{
    use Queueable;

    protected $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $student = $this->document->student;
        return (new MailMessage)
            ->subject('New Document uploaded')
            ->line("A new document was uploaded for student: {$student->first_name} {$student->last_name}.")
            ->action('View Student', url("/admin/students/{$student->id}"))
            ->line('Thanks.');
    }

    public function toDatabase($notifiable)
    {
        $student = $this->document->student;
        return [
            'document_id' => $this->document->id,
            'student_id' => $student->id,
            'message' => "Document '{$this->document->file_name}' uploaded for {$student->first_name} {$student->last_name} by. $this->document->student->agent->business_name",
            'type' => 'Document_Uploaded', // <- important!
            'link' => url("/admin/students/{$student->id}"), // <- link to the admin student show page
        ];
    }
}
