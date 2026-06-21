<?php

namespace App\Http\Controllers\Admin;

use App\Mail\InternalEmail;
use App\Http\Controllers\Controller;
use App\Models\Emails;
use App\Models\Setting;
use App\Models\User;
use App\Services\EmailSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EmailController extends Controller
{
    private function getFolderCounts(): array
    {
        $myEmail = Auth::user()->email;
        return [
            'inboxUnread' => Emails::where(function ($q) use ($myEmail) {
                    $q->where('recipient_email', $myEmail)
                      ->orWhere('is_external', true);
                })
                ->where('folder', 'inbox')
                ->where(function ($q) { $q->whereNull('status')->orWhere('status', '!=', 'read'); })
                ->count(),
            'inboxTotal'  => Emails::where(function ($q) use ($myEmail) {
                    $q->where('recipient_email', $myEmail)
                      ->orWhere('is_external', true);
                })
                ->where('folder', 'inbox')
                ->count(),
            'sentCount'   => Emails::where('sender_id', Auth::id())->where('folder', 'sent')->count(),
            'draftsCount' => Emails::where('sender_id', Auth::id())->where('folder', 'drafts')->count(),
        ];
    }

    public function inbox(Request $request)
    {
        $email = Auth::user()->email;
        $emails = Emails::where(function ($q) use ($email) {
                $q->where('recipient_email', $email)
                  ->orWhere(function ($sub) use ($email) {
                      $sub->where('is_external', true);
                  });
            })
            ->where('folder', 'inbox')
            ->latest('sent_at')
            ->paginate(50);
        $selectedEmail = $request->has('view') ? Emails::find($request->view) : $emails->first();
        if ($selectedEmail && $selectedEmail->folder === 'inbox' && $selectedEmail->recipient_email === Auth::user()->email && $selectedEmail->status === 'delivered') {
            $selectedEmail->update(['status' => 'read', 'read_at' => now()]);
        }
        return view('admin.emails.index', compact('emails', 'selectedEmail') + ['folder' => 'inbox'] + $this->getFolderCounts());
    }

    public function syncNow(EmailSyncService $sync)
    {
        if (!$sync->isEnabled()) {
            return redirect()->back()->with('error', 'IMAP not configured. Go to Settings > Email to set up IMAP.');
        }

        $result = $sync->syncNewEmails();
        if ($result['error']) {
            return redirect()->back()->with('error', 'IMAP sync failed: ' . $result['error']);
        }
        return redirect()->back()->with('success', "Synced {$result['count']} new email(s).");
    }

    public function sent(Request $request)
    {
        $emails = Emails::where('sender_id', Auth::id())
            ->where('folder', 'sent')
            ->latest()
            ->paginate(50);
        $selectedEmail = $request->has('view') ? Emails::find($request->view) : null;
        return view('admin.emails.index', compact('emails', 'selectedEmail') + ['folder' => 'sent'] + $this->getFolderCounts());
    }

    public function drafts(Request $request)
    {
        $emails = Emails::where('sender_id', Auth::id())
            ->where('folder', 'drafts')
            ->latest()
            ->paginate(50);
        $selectedEmail = $request->has('view') ? Emails::find($request->view) : null;
        return view('admin.emails.index', compact('emails', 'selectedEmail') + ['folder' => 'drafts'] + $this->getFolderCounts());
    }

    public function create()
    {
        $users = User::where('active', true)->orderBy('name')->get();
        return view('admin.emails.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_email' => 'nullable|email',
            'manual_recipient' => 'nullable|email',
            'recipient_name' => 'nullable|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'cc' => 'nullable|string|max:500',
            'bcc' => 'nullable|string|max:500',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,zip|max:10240',
        ]);

        $recipientEmail = $validated['recipient_email'] ?: $validated['manual_recipient'];
        if (!$recipientEmail) {
            return back()->withErrors(['recipient_email' => 'Please select or enter a recipient email.'])->withInput();
        }

        $recipient = User::where('email', $recipientEmail)->first();

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('email_attachments/' . date('Y/m'), 'public');
                $attachments[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                ];
            }
        }

        $email = Emails::create([
            'sender_id' => Auth::id(),
            'sender_email' => Auth::user()->email,
            'sender_name' => Auth::user()->name,
            'recipient_email' => $recipientEmail,
            'recipient_name' => $validated['recipient_name'] ?? $recipient?->name,
            'recipient_id' => $recipient?->id,
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'body_html' => nl2br(e($validated['body'])),
            'cc' => $validated['cc'] ?? null,
            'bcc' => $validated['bcc'] ?? null,
            'attachments' => count($attachments) ? $attachments : null,
            'folder' => 'sent',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        if ($recipient) {
            Emails::create([
                'sender_id' => Auth::id(),
                'sender_email' => Auth::user()->email,
                'sender_name' => Auth::user()->name,
                'recipient_email' => $recipient->email,
                'recipient_name' => $recipient->name,
                'recipient_id' => $recipient->id,
                'subject' => $validated['subject'],
                'body' => $validated['body'],
                'body_html' => nl2br(e($validated['body'])),
                'attachments' => count($attachments) ? $attachments : null,
                'folder' => 'inbox',
                'status' => 'delivered',
                'parent_id' => $email->id,
            ]);
        }

        $smtpResult = $this->sendViaSmtp($email);

        $msg = 'Email sent successfully.';
        if ($smtpResult === 'disabled') {
            $msg .= ' <span class="text-warning">(SMTP not enabled — only saved to database. Configure in Settings > Email.)</span>';
        } elseif ($smtpResult === 'failed') {
            $msg .= ' <span class="text-danger">(SMTP send failed — check logs.)</span>';
        }
        return redirect()->route('admin.emails.sent')->with('success', $msg);
    }

    public function show(Emails $email)
    {
        if ($email->folder === 'drafts') {
            return redirect()->route('admin.emails.edit', $email);
        }
        $route = $email->folder === 'inbox' ? 'admin.emails.inbox' : 'admin.emails.' . $email->folder;
        return redirect()->to(route($route) . '?view=' . $email->id);
    }

    public function edit(Emails $email)
    {
        abort_if($email->folder !== 'drafts' || $email->sender_id !== Auth::id(), 404);
        $users = User::where('active', true)->orderBy('name')->get();
        return view('admin.emails.create', compact('email', 'users'));
    }

    public function update(Request $request, Emails $email)
    {
        abort_if($email->folder !== 'drafts' || $email->sender_id !== Auth::id(), 404);

        $validated = $request->validate([
            'recipient_email' => 'nullable|email',
            'manual_recipient' => 'nullable|email',
            'recipient_name' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,zip|max:10240',
        ]);

        $recipientEmail = $validated['recipient_email'] ?: ($validated['manual_recipient'] ?? $email->recipient_email);

        $attachments = $email->attachments ?? [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('email_attachments/' . date('Y/m'), 'public');
                $attachments[] = ['path' => $path, 'name' => $file->getClientOriginalName(), 'size' => $file->getSize()];
            }
        }

        $email->update([
            'recipient_email' => $recipientEmail,
            'recipient_name' => $validated['recipient_name'] ?? $email->recipient_name,
            'subject' => $validated['subject'] ?? $email->subject,
            'body' => $validated['body'] ?? $email->body,
            'body_html' => isset($validated['body']) ? nl2br(e($validated['body'])) : $email->body_html,
            'attachments' => count($attachments) ? $attachments : null,
        ]);

        return redirect()->route('admin.emails.drafts')->with('success', 'Draft updated.');
    }

    public function sendDraft(Request $request, Emails $email)
    {
        abort_if($email->folder !== 'drafts' || $email->sender_id !== Auth::id(), 404);

        if (!$email->recipient_email) {
            return back()->withErrors(['recipient_email' => 'Recipient email is required.']);
        }
        if (!$email->subject) {
            return back()->withErrors(['subject' => 'Subject is required.']);
        }
        if (!$email->body) {
            return back()->withErrors(['body' => 'Body is required.']);
        }

        $email->update([
            'folder' => 'sent',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $smtpResult = $this->sendViaSmtp($email);

        $msg = 'Draft sent successfully.';
        if ($smtpResult === 'disabled') {
            $msg .= ' <span class="text-warning">(SMTP not enabled — only saved to database. Configure in Settings > Email.)</span>';
        } elseif ($smtpResult === 'failed') {
            $msg .= ' <span class="text-danger">(SMTP send failed — check logs.)</span>';
        }
        return redirect()->route('admin.emails.sent')->with('success', $msg);
    }

    public function reply(Request $request, Emails $email)
    {
        $validated = $request->validate([
            'body' => 'required|string',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,zip|max:10240',
        ]);

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('email_attachments/' . date('Y/m'), 'public');
                $attachments[] = ['path' => $path, 'name' => $file->getClientOriginalName(), 'size' => $file->getSize()];
            }
        }

        $reply = Emails::create([
            'sender_id' => Auth::id(),
            'sender_email' => Auth::user()->email,
            'sender_name' => Auth::user()->name,
            'recipient_email' => $email->sender_email,
            'recipient_name' => $email->sender_name,
            'recipient_id' => $email->sender_id,
            'subject' => 'Re: ' . $email->subject,
            'body' => $validated['body'],
            'body_html' => nl2br(e($validated['body'])),
            'attachments' => count($attachments) ? $attachments : null,
            'folder' => 'sent',
            'status' => 'sent',
            'parent_id' => $email->id,
            'sent_at' => now(),
        ]);

        $inboxEmail = Emails::create([
            'sender_id' => Auth::id(),
            'sender_email' => Auth::user()->email,
            'sender_name' => Auth::user()->name,
            'recipient_email' => $email->sender_email,
            'recipient_name' => $email->sender_name,
            'recipient_id' => $email->sender_id,
            'subject' => 'Re: ' . $email->subject,
            'body' => $validated['body'],
            'body_html' => nl2br(e($validated['body'])),
            'attachments' => count($attachments) ? $attachments : null,
            'folder' => 'inbox',
            'status' => 'delivered',
            'parent_id' => $reply->id,
        ]);
        $smtpResult = $this->sendViaSmtp($inboxEmail, true);

        $msg = 'Reply sent successfully.';
        if ($smtpResult === 'disabled') {
            $msg .= ' <span class="text-warning">(SMTP not enabled — only saved to database. Configure in Settings > Email.)</span>';
        } elseif ($smtpResult === 'failed') {
            $msg .= ' <span class="text-danger">(SMTP send failed — check logs.)</span>';
        }
        return redirect()->route('admin.emails.sent')->with('success', $msg);
    }

    private function sendViaSmtp(Emails $email, bool $isReply = false): string
    {
        if (!Setting::getValue('mail_enabled', false)) {
            return 'disabled';
        }
        try {
            $host = Setting::getValue('mail_host', config('mail.mailers.smtp.host'));
            $port = Setting::getValue('mail_port', config('mail.mailers.smtp.port'));
            $username = Setting::getValue('mail_username', config('mail.mailers.smtp.username'));
            $password = Setting::getValue('mail_password', config('mail.mailers.smtp.password'));
            $encryption = Setting::getValue('mail_encryption', config('mail.mailers.smtp.encryption'));
            $fromAddress = Setting::getValue('mail_from_address', config('mail.from.address'));
            $fromName = Setting::getValue('mail_from_name', config('mail.from.name'));
            config([
                'mail.mailers.smtp.host' => $host, 'mail.mailers.smtp.port' => $port,
                'mail.mailers.smtp.username' => $username, 'mail.mailers.smtp.password' => $password,
                'mail.mailers.smtp.encryption' => $encryption,
                'mail.from.address' => $fromAddress, 'mail.from.name' => $fromName,
            ]);
            Mail::mailer('smtp')->to($email->recipient_email)->send(new InternalEmail($email, $isReply));
            $email->update(['status' => 'delivered']);
            return 'sent';
        } catch (\Exception $e) {
            logger()->error('SMTP send failed: ' . $e->getMessage(), ['email_id' => $email->id, 'recipient' => $email->recipient_email]);
            return 'failed';
        }
    }

    public function saveDraft(Request $request)
    {
        $validated = $request->validate([
            'recipient_email' => 'nullable|email',
            'manual_recipient' => 'nullable|email',
            'subject' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,zip|max:10240',
        ]);

        $recipientEmail = $validated['recipient_email'] ?: ($validated['manual_recipient'] ?? '');

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('email_attachments/' . date('Y/m'), 'public');
                $attachments[] = ['path' => $path, 'name' => $file->getClientOriginalName(), 'size' => $file->getSize()];
            }
        }

        Emails::create([
            'sender_id' => Auth::id(),
            'sender_email' => Auth::user()->email,
            'sender_name' => Auth::user()->name,
            'recipient_email' => $recipientEmail,
            'subject' => $validated['subject'] ?? '(No Subject)',
            'body' => $validated['body'] ?? '',
            'attachments' => count($attachments) ? $attachments : null,
            'folder' => 'drafts',
            'status' => 'draft',
        ]);

        return redirect()->route('admin.emails.drafts')->with('success', 'Draft saved.');
    }

    public function downloadAttachment($emailId, $index)
    {
        $email = Emails::findOrFail($emailId);
        $attachments = $email->attachments ?? [];
        if (!isset($attachments[$index])) abort(404);

        $file = $attachments[$index];
        $path = storage_path('app/public/' . $file['path']);
        if (!file_exists($path)) abort(404);

        return response()->download($path, $file['name']);
    }

    public function destroy(Emails $email)
    {
        if ($email->attachments) {
            $files = $email->attachments;
            foreach ($files as $f) {
                Storage::disk('public')->delete($f['path']);
            }
        }
        $email->delete();
        return redirect()->back()->with('success', 'Email deleted.');
    }

    public function toggleStar(Emails $email)
    {
        $email->update(['is_starred' => !$email->is_starred]);
        return redirect()->back();
    }
}
