<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnquiryRequest;
use App\Models\Enquiry;
use App\Models\Setting;
use App\Services\MailConfigService;
use App\Services\PortalNotifier;

class EnquiryController extends Controller
{
    public function create()
    {
        return view('guest.enquiry');
    }

    public function store(StoreEnquiryRequest $request)
    {
        $enquiry = Enquiry::create($request->validated());

        $subject = $enquiry->subject ?: 'New Enquiry';
        $body = $enquiry->message;

        // Route to portal@ + enquiry admin
        PortalNotifier::send(
            PortalNotifier::ENQUIRY,
            '📩 New Enquiry: ' . $subject,
            [
                PortalNotifier::line('Name', $enquiry->name),
                PortalNotifier::line('Email', $enquiry->email),
                PortalNotifier::line('Phone', $enquiry->phone ?: '—'),
                PortalNotifier::line('Type', ucfirst($enquiry->type ?? 'general')),
                PortalNotifier::line('Message', $body),
                PortalNotifier::line('Received at', now()->format('d M Y, g:i A')),
            ],
            'Open Enquiries',
            url('/admin/enquiries')
        );

        // Send an acknowledgement to the enquirer
        if (MailConfigService::isEnabled() && ! empty($enquiry->email)) {
            try {
                MailConfigService::configure();
                \Illuminate\Support\Facades\Mail::mailer('smtp')
                    ->to($enquiry->email)
                    ->send(new \App\Mail\PortalNotificationMail(
                        mailSubject: 'We received your enquiry — ' . (Setting::getValue('site.name') ?: 'IDEA Consultancy'),
                        introLines: [
                            'Dear ' . e($enquiry->name) . ',',
                            'Thank you for contacting us. We have received your enquiry and our team will get back to you shortly.',
                            '<strong>Reference:</strong> ENQ-' . str_pad((string) $enquiry->id, 6, '0', STR_PAD_LEFT),
                        ],
                        outroLines: ['This is an automated acknowledgement. Please do not reply directly to this message.'],
                    ));
            } catch (\Throwable $e) {
                logger()->warning('Enquiry acknowledgement failed: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Your enquiry has been submitted successfully. We will get back to you shortly.');
    }
}
