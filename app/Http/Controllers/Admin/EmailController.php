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
        \App\Services\EmailSchema::ensureBulkEmails();

        $campaigns = BulkEmail::latest()->paginate(20);
        return view('admin.emails.bulk-index', compact('campaigns'));
    }

    public function bulkForm()
    {
        \App\Services\EmailSchema::ensureBulkEmails();

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
            'send_now' => 'nullable|boolean',
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

        // Self-heal: create the tables if migrations haven't run on this DB.
        \App\Services\EmailSchema::ensureBulkEmails();
        \App\Services\EmailSchema::ensureQueue();

        $bulk = BulkEmail::create([
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'recipient_group' => $validated['recipient_group'] ?? 'selected',
            'recipients' => $emails,
            'recipients_count' => count($emails),
            'attachments' => count($attachments) ? $attachments : null,
            'status' => 'queued',
            'created_by' => Auth::id(),
        ]);

        $sendNow = $request->boolean('send_now', true);

        try {
            if ($sendNow) {
                // Send inline — no queue worker required.
                SendBulkEmailJob::dispatchSync($bulk->id, $emails);
                $bulk->refresh();

                return redirect()->route('admin.emails.bulk.index')
                    ->with('success', "Bulk email processed: {$bulk->sent_count} sent, {$bulk->failed_count} failed.");
            }

            SendBulkEmailJob::dispatch($bulk->id, $emails);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Bulk email dispatch failed: ' . $e->getMessage());

            return redirect()->route('admin.emails.bulk.index')
                ->with('error', 'Bulk email failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.emails.bulk.index')
            ->with('success', 'Bulk email queued for ' . count($emails) . ' recipient(s).');
    }

    /** Process any queued bulk-email jobs now (works without a separate worker). */
    public function bulkProcessQueue()
    {
        try {
            // Remove stale poll jobs from an earlier deploy (class no longer exists).
            \Illuminate\Support\Facades\DB::table('jobs')
                ->where('payload', 'like', '%PollMailboxesJob%')->delete();
            \Illuminate\Support\Facades\DB::table('failed_jobs')
                ->where('payload', 'like', '%PollMailboxesJob%')->delete();
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            \Illuminate\Support\Facades\Artisan::call('queue:work', [
                '--stop-when-empty' => true,
                '--tries' => 1,
                '--timeout' => 300,
            ]);

            $output = trim(\Illuminate\Support\Facades\Artisan::output());

            return redirect()->route('admin.emails.bulk.index')
                ->with('success', 'Queue processed.' . ($output ? ' ' . \Illuminate\Support\Str::limit($output, 300) : ''));
        } catch (\Throwable $e) {
            return redirect()->route('admin.emails.bulk.index')
                ->with('error', 'Queue processing failed: ' . $e->getMessage());
        }
    }

    /** Re-send a campaign to its stored recipients. */
    public function bulkResend(BulkEmail $bulk)
    {
        \App\Services\EmailSchema::ensureBulkEmails();
        $bulk->refresh();

        $recipients = is_array($bulk->recipients) ? array_values(array_filter($bulk->recipients)) : [];

        if (empty($recipients)) {
            return redirect()->route('admin.emails.bulk.index')
                ->with('error', 'This campaign has no stored recipients (created before resend support). Create a new campaign instead.');
        }

        \App\Services\EmailSchema::ensureQueue();

        $new = BulkEmail::create([
            'subject' => $bulk->subject,
            'body' => $bulk->body,
            'recipient_group' => $bulk->recipient_group,
            'recipients' => $recipients,
            'recipients_count' => count($recipients),
            'attachments' => $bulk->attachments,
            'status' => 'queued',
            'created_by' => Auth::id(),
        ]);

        try {
            SendBulkEmailJob::dispatchSync($new->id, $recipients);
            $new->refresh();

            return redirect()->route('admin.emails.bulk.index')
                ->with('success', "Resent: {$new->sent_count} sent, {$new->failed_count} failed.");
        } catch (\Throwable $e) {
            return redirect()->route('admin.emails.bulk.index')
                ->with('error', 'Resend failed: ' . $e->getMessage());
        }
    }

    /** Remove a campaign (and any queued job for it). */
    public function bulkDestroy(BulkEmail $bulk)
    {
        try {
            \Illuminate\Support\Facades\DB::table('jobs')
                ->where('payload', 'like', '%SendBulkEmailJob%')
                ->where('payload', 'like', '%bulkEmailId%i:' . $bulk->id . ';%')
                ->delete();
            \Illuminate\Support\Facades\DB::table('failed_jobs')
                ->where('payload', 'like', '%SendBulkEmailJob%')
                ->where('payload', 'like', '%bulkEmailId%i:' . $bulk->id . ';%')
                ->delete();
        } catch (\Throwable $e) {
            // ignore
        }

        $bulk->delete();

        return redirect()->route('admin.emails.bulk.index')->with('success', 'Campaign removed.');
    }
}
