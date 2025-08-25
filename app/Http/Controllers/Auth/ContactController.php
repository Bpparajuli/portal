<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Show contact form
     */
    public function showForm()
    {
        return view('auth.contact');
    }

    /**
     * Handle contact form submission
     */
    public function submit(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'subject' => 'nullable|string|max:150',
            'email'   => 'required|email',
            'message' => 'required|string|max:2000',
            'hp'      => 'nullable|size:0', // honeypot
        ]);

        if ($request->filled('hp')) {
            return back()->with('success', 'Thank you! Your message has been received.');
        }

        try {
            Mail::raw(
                "You received a new message from the contact form:\n\n" .
                    "Name: {$data['name']}\n" .
                    "Email: {$data['email']}\n" .
                    "Subject: " . ($data['subject'] ?? 'N/A') . "\n\n" .
                    "Message:\n{$data['message']}",
                function ($message) use ($data) {
                    $message->to('bishesworparajuli@gmail.com')
                        ->from($data['email'], $data['name'])
                        ->subject($data['subject'] ?? 'New Contact Form Message');
                }
            );

            return back()->with('success', '✅ Thank you! Your message has been sent successfully.');
        } catch (\Exception $e) {
            return back()->with('error', '⚠️ Something went wrong. Please try again later.');
        }
    }
}
