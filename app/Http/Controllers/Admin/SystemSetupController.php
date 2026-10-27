<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PortalNotificationMail;
use App\Services\EmailSchema;
use App\Services\MailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SystemSetupController extends Controller
{
    /** One-click setup for email tables. Visit /admin/system/email-setup once. */
    public function emailSetup()
    {
        abort_unless(auth()->user()?->is_admin, 403);

        $created = EmailSchema::ensureAll();

        $message = empty($created)
            ? 'All email tables already exist.'
            : 'Created: ' . implode(', ', $created);

        return response()->json([
            'success' => true,
            'message' => $message,
            'created' => $created,
        ]);
    }

    /** Email system diagnostics. */
    public function diagnostics()
    {
        abort_unless(auth()->user()?->is_admin, 403);

        EmailSchema::ensureAll();

        $mail = MailConfigService::describe();

        $db = MailConfigService::settings();
        if (! empty($db['mail_password'])) {
            $db['mail_password'] = '******(set)';
        }

        $queued = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        $recentFailed = DB::table('failed_jobs')->latest('id')->take(3)->get(['id', 'failed_at', 'exception']);
        $campaigns = \App\Models\BulkEmail::latest()->take(10)->get();
        $mailboxes = \App\Models\Mailbox::orderBy('label')->get();

        return view('admin.system.email-diagnostics', compact(
            'mail', 'db', 'queued', 'failedJobs', 'recentFailed', 'campaigns', 'mailboxes'
        ));
    }

    /** Send a test email using the resolved configuration. */
    public function sendTest(Request $request)
    {
        abort_unless(auth()->user()?->is_admin, 403);

        $to = $request->validate(['to' => 'required|email'])['to'];

        try {
            MailConfigService::configure();

            $mailerName = MailConfigService::mailerName();
            $pending = $mailerName ? Mail::mailer($mailerName) : Mail::mailer();

            $pending->to($to)->send(new PortalNotificationMail(
                mailSubject: 'Email system test',
                introLines: [
                    'This is a test email from the portal.',
                    'If you received it, outbound email is working.',
                ],
            ));

            return back()->with('success', "Test email sent to {$to}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Test failed: ' . $e->getMessage());
        }
    }
}
