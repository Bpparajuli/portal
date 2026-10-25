<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Generic portal notification mailable used to route activity emails
 * to the portal/admin/department inboxes.
 */
class PortalNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $mailSubject,
        public array $introLines = [],
        public ?string $greeting = null,
        public ?string $actionText = null,
        public ?string $actionUrl = null,
        public array $outroLines = [],
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.layout',
            with: [
                'subject' => $this->mailSubject,
                'greeting' => $this->greeting,
                'introLines' => $this->introLines,
                'actionText' => $this->actionText,
                'actionUrl' => $this->actionUrl,
                'outroLines' => $this->outroLines,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
