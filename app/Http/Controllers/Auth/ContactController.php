<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function submit(Request $req)
    {
        $data = $req->validate([
            'name'    => 'required|string|max:100',
            'subject' => 'nullable|string|max:150',
            'email'   => 'required|email',
            'message' => 'required|string',
            'hp'      => 'nullable|size:0', // honeypot should be empty
        ]);

        if ($req->filled('hp')) {
            // Spam bot detected
            return back()->with('success', 'Thank you! We have received your message.');
        }

        // Send a simple email
        Mail::send([], [], function ($message) use ($data) {
            $message->to('bishesworparajuli@gmail.com')
                ->from($data['email'], $data['name'])
                ->subject($data['subject'] ?? 'New Contact Form Message')
                ->setBody(
                    "You received a new message from the contact form:\n\n" .
                        "Name: {$data['name']}\n" .
                        "Email: {$data['email']}\n" .
                        "Subject: " . ($data['subject'] ?? 'N/A') . "\n\n" .
                        "Message:\n{$data['message']}",
                    'text/plain'
                );
        });

        return back()->with('success', 'Thank you! We have received your message and will get back to you soon.');
    }
}
