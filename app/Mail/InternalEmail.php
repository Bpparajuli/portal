<?php

namespace App\Mail;

use App\Models\Emails;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InternalEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Emails $email,
        public bool $isReply = false,
    ) {}

    public function envelope(): Envelope
    {
        $fromEmail = Setting::getValue('mail_from_address', config('mail.from.address'));
        $fromName = Setting::getValue('mail_from_name', config('mail.from.name'));

        return new Envelope(
            from: new Address($fromEmail, $fromName),
            subject: $this->email->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'admin.emails.notifications.internal',
            with: [
                'email' => $this->email,
                'isReply' => $this->isReply,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
