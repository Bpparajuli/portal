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
            'name'    => 'required|string|max:200',
            'subject' => 'nullable|string|max:300',
            'email'   => 'required|email|max:255',
            'message' => 'required|string|max:2000',
            'hp'      => 'nullable|size:0', // honeypot
        ]);

        if ($request->filled('hp')) {
            return back()->with('success', 'Thank you! Your message has been received.');
        }

        // Sanitize strings
        $name = strip_tags($data['name']);
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $subject = strip_tags($data['subject'] ?? 'New Contact Form Message');
        $messageBody = strip_tags($data['message']);

        try {
            Mail::raw(
                "You received a new message from the contact form:\n\n" .
                    "Name: {$name}\n" .
                    "Email: {$email}\n" .
                    "Subject: {$subject}\n\n" .
                    "Message:\n{$messageBody}",
                function ($message) use ($name, $email, $subject) {
                    $message->to('info@ideacs.com.np')
                        ->from($email, $name)
                        ->subject($subject);
                }
            );

            return back()->with('success', '✅ Thank you! Your message has been sent successfully.');
        } catch (\Exception $e) {
            return back()->with('error', '⚠️ Something went wrong. Please try again later.');
        }
    }
}
