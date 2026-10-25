<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendBulkEmailJob;
use App\Models\BulkEmail;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Bulk email campaigns (the in-portal webmail has been retired — mail
 * lives in the external mail client; the portal tracks unread counts via
 * MailboxController).
 */
class EmailController extends Controller
{
    public function bulkIndex()
    {
        $campaigns = BulkEmail::latest()->paginate(20);
        return view('admin.emails.bulk-index', compact('campaigns'));
    }

    public function bulkForm()
    {
        $students = Student::whereNotNull('email')->where('email', '!=', '')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        $agents = User::agents()->where('active', true)
            ->whereNotNull('email')->where('email', '!=', '')
            ->orderBy('name')
            ->get(['id', 'name', 'business_name', 'email']);

        return view('admin.emails.bulk', compact('students', 'agents'));
    }

    public function bulkSend(Request $request)
    {
        $validated = $request->validate([
            'emails' => 'required|array|min:1|max:5000',
            'emails.*' => 'email',
            'recipient_group' => 'nullable|string|max:50',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,zip|max:10240',
        ]);

        $emails = array_values(array_unique(array_filter(array_map('trim', $validated['emails']))));
        if (empty($emails)) {
            return back()->withErrors(['emails' => 'Please select at least one recipient.'])->withInput();
        }

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('bulk_email_attachments/' . date('Y/m'), 'public');
                $attachments[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                ];
            }
        }

        $bulk = BulkEmail::create([
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'recipient_group' => $validated['recipient_group'] ?? 'selected',
            'recipients_count' => count($emails),
            'attachments' => count($attachments) ? $attachments : null,
            'status' => 'queued',
            'created_by' => Auth::id(),
        ]);

        SendBulkEmailJob::dispatch($bulk->id, $emails);

        return redirect()->route('admin.emails.bulk.index')
            ->with('success', 'Bulk email queued for ' . count($emails) . ' recipient(s).');
    }
}
