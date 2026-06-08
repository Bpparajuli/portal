<?php

namespace App\Http\Controllers\Admin;

use App\Mail\InternalEmail;
use App\Http\Controllers\Controller;
use App\Models\Emails;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EmailController extends Controller
{
    public function inbox()
    {
        $emails = Emails::where('recipient_email', Auth::user()->email)
            ->where('folder', 'inbox')
            ->latest()
            ->paginate(20);
        return view('admin.emails.index', compact('emails') + ['folder' => 'inbox']);
    }

    public function sent()
    {
        $emails = Emails::where('sender_id', Auth::id())
            ->where('folder', 'sent')
            ->latest()
            ->paginate(20);
        return view('admin.emails.index', compact('emails') + ['folder' => 'sent']);
    }

    public function drafts()
    {
        $emails = Emails::where('sender_id', Auth::id())
            ->where('folder', 'drafts')
            ->latest()
            ->paginate(20);
        return view('admin.emails.index', compact('emails') + ['folder' => 'drafts']);
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
            'attachments' => count($attachments) ? json_encode($attachments) : null,
            'folder' => 'sent',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        if ($recipient) {
            $inboxEmail = Emails::create([
                'sender_id' => Auth::id(),
                'sender_email' => Auth::user()->email,
                'sender_name' => Auth::user()->name,
                'recipient_email' => $recipient->email,
                'recipient_name' => $recipient->name,
                'recipient_id' => $recipient->id,
                'subject' => $validated['subject'],
                'body' => $validated['body'],
                'body_html' => nl2br(e($validated['body'])),
                'attachments' => count($attachments) ? json_encode($attachments) : null,
                'folder' => 'inbox',
                'status' => 'delivered',
                'parent_id' => $email->id,
            ]);
            $this->sendViaSmtp($inboxEmail);
        }

        return redirect()->route('admin.emails.sent')->with('success', 'Email sent successfully.');
    }

    public function show(Emails $email)
    {
        if ($email->folder === 'inbox' && $email->recipient_email === Auth::user()->email && $email->status === 'delivered') {
            $email->update(['status' => 'read', 'read_at' => now()]);
        }
        return view('admin.emails.show', compact('email'));
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
            'attachments' => count($attachments) ? json_encode($attachments) : null,
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
            'attachments' => count($attachments) ? json_encode($attachments) : null,
            'folder' => 'inbox',
            'status' => 'delivered',
            'parent_id' => $reply->id,
        ]);
        $this->sendViaSmtp($inboxEmail, true);

        return redirect()->route('admin.emails.sent')->with('success', 'Reply sent successfully.');
    }

    private function sendViaSmtp(Emails $email, bool $isReply = false): void
    {
        if (!Setting::getValue('mail_enabled', false)) return;
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
        } catch (\Exception $e) {
            logger()->error('SMTP send failed: ' . $e->getMessage(), ['email_id' => $email->id, 'recipient' => $email->recipient_email]);
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
            'attachments' => count($attachments) ? json_encode($attachments) : null,
            'folder' => 'drafts',
            'status' => 'draft',
        ]);

        return redirect()->route('admin.emails.drafts')->with('success', 'Draft saved.');
    }

    public function downloadAttachment($emailId, $index)
    {
        $email = Emails::findOrFail($emailId);
        $attachments = $email->attachments ? json_decode($email->attachments, true) : [];
        if (!isset($attachments[$index])) abort(404);

        $file = $attachments[$index];
        $path = storage_path('app/public/' . $file['path']);
        if (!file_exists($path)) abort(404);

        return response()->download($path, $file['name']);
    }

    public function destroy(Emails $email)
    {
        if ($email->attachments) {
            $files = json_decode($email->attachments, true);
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
