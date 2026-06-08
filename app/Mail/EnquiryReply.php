<?php

namespace App\Mail;

use App\Models\Enquiry;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class EnquiryReply extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Enquiry $enquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                Setting::getValue('mail_from_address', config('mail.from.address')),
                Setting::getValue('mail_from_name', config('mail.from.name'))
            ),
            subject: 'Re: ' . ($this->enquiry->subject ?? 'Your Enquiry'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.enquiry-reply',
            with: ['enquiry' => $this->enquiry],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
