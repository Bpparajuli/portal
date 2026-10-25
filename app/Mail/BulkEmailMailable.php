<?php

namespace App\Mail;

use App\Models\BulkEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class BulkEmailMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public BulkEmail $bulk)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->bulk->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.layout',
            with: [
                'subject' => $this->bulk->subject,
                'customContent' => nl2br(e($this->bulk->body)),
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        foreach (($this->bulk->attachments ?? []) as $file) {
            if (! empty($file['path']) && Storage::disk('public')->exists($file['path'])) {
                $attachments[] = Attachment::fromStorageDisk('public', $file['path'])
                    ->as($file['name'] ?? basename($file['path']));
            }
        }

        return $attachments;
    }
}
