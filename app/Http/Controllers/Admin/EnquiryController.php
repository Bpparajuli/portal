<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\EnquiryReply;
use App\Models\Enquiry;
use App\Services\MailConfigService;
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
        return view('admin.enquiries', compact('enquiries'));
    }

    public function show(Enquiry $enquiry)
    {
        return redirect()->route('admin.enquiries.index');
    }

    public function reply(Request $request, Enquiry $enquiry)
    {
        $validated = $request->validate([
            'reply_message' => 'required|string|max:5000',
        ]);

        $enquiry->markAsReplied(Auth::user(), $validated['reply_message']);

        // Send the reply email to the enquirer.
        try {
            if (MailConfigService::isEnabled()) {
                MailConfigService::configure();
                Mail::mailer('smtp')->to($enquiry->email)->send(new EnquiryReply($enquiry));
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
