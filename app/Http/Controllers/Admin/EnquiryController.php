<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InternalEmail;
use App\Models\Enquiry;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class EnquiryController extends Controller
{
    public function index(Request $request)
    {
        $query = Enquiry::latest();
        if ($request->get('status')) $query->where('status', $request->get('status'));
        if ($request->get('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")->orWhere('email', 'like', "%{$request->search}%")->orWhere('subject', 'like', "%{$request->search}%");
            });
        }
        $enquiries = $query->paginate(20)->withQueryString();
        return view('admin.enquiries.index', compact('enquiries'));
    }

    public function show(Enquiry $enquiry)
    {
        $enquiry->markAsRead();
        return view('admin.enquiries.show', compact('enquiry'));
    }

    public function reply(Request $request, Enquiry $enquiry)
    {
        $validated = $request->validate([
            'reply_message' => 'required|string|max:5000',
        ]);

        $enquiry->markAsReplied(Auth::user(), $validated['reply_message']);

        // Also send email to the enquirer
        try {
            $fromEmail = Setting::getValue('mail_from_address', config('mail.from.address'));
            $fromName = Setting::getValue('mail_from_name', config('mail.from.name'));

            if ($fromEmail && Setting::getValue('mail_enabled', false)) {
                $host = Setting::getValue('mail_host', config('mail.mailers.smtp.host'));
                $port = Setting::getValue('mail_port', config('mail.mailers.smtp.port'));
                $username = Setting::getValue('mail_username', config('mail.mailers.smtp.username'));
                $password = Setting::getValue('mail_password', config('mail.mailers.smtp.password'));
                $encryption = Setting::getValue('mail_encryption', config('mail.mailers.smtp.encryption'));

                config([
                    'mail.mailers.smtp.host' => $host, 'mail.mailers.smtp.port' => $port,
                    'mail.mailers.smtp.username' => $username, 'mail.mailers.smtp.password' => $password,
                    'mail.mailers.smtp.encryption' => $encryption,
                    'mail.from.address' => $fromEmail, 'mail.from.name' => $fromName,
                ]);

                Mail::mailer('smtp')->to($enquiry->email)->send(new \App\Mail\EnquiryReply($enquiry));
            }
        } catch (\Exception $e) {
            logger()->error('Enquiry reply email failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.enquiries.index')->with('success', 'Reply sent successfully.');
    }

    public function destroy(Enquiry $enquiry)
    {
        $enquiry->delete();
        return redirect()->route('admin.enquiries.index')->with('success', 'Enquiry deleted successfully.');
    }
}
