<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusStudentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Application $application, public ?string $reason = null)
    {
    }

    public function envelope(): Envelope
    {
        $number = $this->application->application_number ?: 'Your Application';

        return new Envelope(
            subject: 'Application Status Update — ' . $number,
        );
    }

    public function content(): Content
    {
        $app = $this->application;
        $student = $app->student;
        $status = $app->status?->name ?? 'Updated';
        $siteName = \App\Models\Setting::getValue('site.name') ?: config('app.name');

        $introLines = [
            'Dear ' . e($student?->full_name ?? 'Student') . ',',
            'The status of your application has been updated.',
            '<strong>University:</strong> ' . e($app->university?->name ?? '—'),
            '<strong>Course:</strong> ' . e($app->course?->title ?? '—'),
            '<strong>New Status:</strong> ' . e($status),
        ];

        if (! empty($this->reason)) {
            $introLines[] = '<strong>Note:</strong> ' . e($this->reason);
        }

        return new Content(
            view: 'emails.layout',
            with: [
                'subject' => 'Application Status Update',
                'greeting' => null,
                'introLines' => $introLines,
                'actionText' => null,
                'actionUrl' => null,
                'outroLines' => ['Thank you, ' . e($siteName)],
            ],
        );
    }
}
