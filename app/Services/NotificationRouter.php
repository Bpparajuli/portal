<?php

namespace App\Services;

use App\Mail\PortalNotificationMail;
use App\Models\Application;
use App\Models\Enquiry;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Notifications\PortalActivityNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Single notification pipeline.
 *
 * In-app  → PortalActivityNotification (database) → one unified bell with tabs.
 * Email   → only the important "act next" events → responsible person(s).
 *
 * Recipients:
 *   - Admins: always included (safety net).
 *   - Staff: added per category once the admin assigns them (settings: notification_routing).
 *   - Agents/users: added explicitly for their own events.
 *
 * Email is only sent for the 6 agreed events; everything else is in-app only.
 */
class NotificationRouter
{
    public const USERS        = 'Users';
    public const APPLICATIONS = 'Applications';
    public const STUDENTS     = 'Students';
    public const DOCUMENTS    = 'Documents';
    public const ENQUIRIES    = 'Enquiries';
    public const TASKS        = 'Tasks';
    public const REVENUE      = 'Revenue';

    public static function admins(): Collection
    {
        try {
            return User::whereIn('role', ['superadmin', 'admin'])->where('active', 1)->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    /** Admin-configured routing map: [category => ['users' => [ids], 'email' => bool]]. */
    public static function routing(): array
    {
        try {
            $value = Setting::getValue('notification_routing', []);
            return is_array($value) ? $value : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** Staff routing rows for a category: [['id'=>..., 'inapp'=>bool, 'email'=>bool], ...]. */
    public static function routingStaff(string $category): array
    {
        $map = static::routing();
        $rows = $map[$category]['staff'] ?? [];

        return is_array($rows) ? $rows : [];
    }

    /** Staff assigned to a category for a specific channel (inapp|email). */
    public static function staffFor(string $category, string $channel = 'inapp'): Collection
    {
        $ids = [];
        foreach (static::routingStaff($category) as $row) {
            if (! empty($row['id']) && ! empty($row[$channel])) {
                $ids[] = (int) $row['id'];
            }
        }

        if (empty($ids)) {
            return collect();
        }

        try {
            return User::whereIn('id', $ids)->where('active', 1)->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    /** Admins + assigned staff for a category and channel (inapp|email). */
    public static function recipients(string $category, string $channel = 'inapp'): Collection
    {
        return static::admins()->merge(static::staffFor($category, $channel))->unique('id')->values();
    }

    /** Create on-page notifications (deduped per user/event/message/day). */
    public static function inApp(Collection $users, string $event, string $category, string $title, string $message, ?string $link = null): void
    {
        $event = str_replace('.', '_', $event);

        foreach ($users as $user) {
            try {
                $exists = $user->notifications()
                    ->where('created_at', '>=', now()->subDay())
                    ->where('data->event', $event)
                    ->where('data->message', $message)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $user->notify(new PortalActivityNotification($event, $category, $title, $message, $link));
            } catch (\Throwable $e) {
                Log::warning('NotificationRouter in-app failed: ' . $e->getMessage());
            }
        }
    }

    /** Send one email to the given users (+ extra addresses), BCC'ing the rest. */
    public static function email(Collection $users, string $subject, array $lines, ?string $actionText = null, ?string $actionUrl = null, array $extraEmails = []): void
    {
        $emails = $users->pluck('email')->filter()->merge($extraEmails)->unique()->values()->all();

        if (empty($emails) || ! MailConfigService::isSendable()) {
            return;
        }

        try {
            MailConfigService::configure();

            $primary = array_shift($emails);
            $mailerName = MailConfigService::mailerName();
            $pending = $mailerName ? Mail::mailer($mailerName) : Mail::mailer();
            $pending = $pending->to($primary);
            if (! empty($emails)) {
                $pending->bcc($emails);
            }

            $pending->send(new PortalNotificationMail($subject, $lines, null, $actionText, $actionUrl));

            Log::info('NotificationRouter email sent', [
                'subject' => $subject,
                'to' => array_merge([$primary], $emails),
            ]);
        } catch (\Throwable $e) {
            Log::error('NotificationRouter email failed: ' . $e->getMessage(), ['subject' => $subject]);
        }
    }

    private static function line(string $label, $value): string
    {
        return '<strong>' . e($label) . ':</strong> ' . e((string) $value);
    }

    // =====================================================================
    // EMAIL EVENTS (the 6)
    // =====================================================================

    public static function userRegistered(User $user): void
    {
        $cat = self::USERS;
        $link = url('/admin/users');

        self::inApp(self::recipients($cat, 'inapp'), 'user.registered', $cat, 'New user registered', "{$user->name} ({$user->email}) registered.", $link);

        self::email(self::recipients($cat, 'email'), 'New user registered', [
            self::line('Name', $user->name),
            self::line('Email', $user->email),
            self::line('Role', ucfirst($user->role)),
            self::line('Registered at', now()->format('d M Y, g:i A')),
        ], 'Open Users', $link);
    }

    public static function agreementUploaded(User $user): void
    {
        $cat = self::USERS;
        $link = url('/admin/users/waiting');

        self::inApp(self::recipients($cat, 'inapp'), 'agreement.uploaded', $cat, 'Agreement uploaded', "{$user->name} uploaded their agreement.", $link);

        self::email(self::recipients($cat, 'email'), 'Agreement uploaded', [
            self::line('Name', $user->name),
            self::line('Email', $user->email),
            self::line('Status', ucfirst(str_replace('_', ' ', (string) $user->agreement_status))),
            self::line('Uploaded at', now()->format('d M Y, g:i A')),
        ], 'Review Agreements', $link);
    }

    public static function userApproved(User $user): void
    {
        $cat = self::USERS;
        $users = collect([$user]);
        $link = url('/login');

        self::inApp($users, 'user.approved', $cat, 'Account approved', 'Your account has been approved. You can now log in.', $link);

        self::email($users, 'Your account has been approved', [
            'Hello ' . e($user->name) . ',',
            'Your account has been approved. You can now log in and continue.',
        ], 'Log in', $link);
    }

    public static function applicationAdded(Application $application): void
    {
        $cat = self::APPLICATIONS;
        $student = $application->student;
        $link = $student ? url('/crm/students/' . $student->id) : url('/crm');

        self::inApp(self::recipients($cat, 'inapp'), 'application.added', $cat, 'New application', 'Application ' . $application->application_number . ' submitted for ' . ($student?->full_name ?? 'a student') . '.', $link);

        self::email(self::recipients($cat, 'email'), 'New application added', [
            self::line('Application', $application->application_number),
            self::line('Student', $student?->full_name ?? '—'),
            self::line('University', $application->university?->name ?? '—'),
            self::line('Course', $application->course?->title ?? '—'),
        ], 'View Application', $link);
    }

    public static function applicationStatusUpdated(Application $application, User $actor): void
    {
        $cat = self::APPLICATIONS;
        $student = $application->student;
        $agent = $student?->agent;
        $status = $application->status?->name ?? 'Updated';
        $link = $student ? url('/crm/students/' . $student->id) : url('/crm');

        $recipients = self::recipients($cat, 'inapp');
        if ($agent) {
            $recipients = $recipients->push($agent);
        }

        self::inApp($recipients->unique('id'), 'application.status', $cat, 'Application status updated', "Status changed to {$status} for " . ($student?->full_name ?? 'a student') . '.', $link);

        // Email the owning agent (responsible person).
        if ($agent) {
            self::email(collect([$agent]), 'Application status updated', [
                self::line('Student', $student?->full_name ?? '—'),
                self::line('Application', $application->application_number),
                self::line('New Status', $status),
                self::line('Updated by', $actor->business_name ?: $actor->name),
            ], 'View Application', $link);
        }
    }

    public static function enquiryReceived(Enquiry $enquiry): void
    {
        $cat = self::ENQUIRIES;
        $link = url('/admin/enquiries');
        $subject = $enquiry->subject ?: 'New Enquiry';

        self::inApp(self::recipients($cat, 'inapp'), 'enquiry.received', $cat, 'New enquiry', $subject . ' — ' . $enquiry->name, $link);

        self::email(self::recipients($cat, 'email'), 'New enquiry received: ' . $subject, [
            self::line('Name', $enquiry->name),
            self::line('Email', $enquiry->email),
            self::line('Phone', $enquiry->phone ?: '—'),
            self::line('Message', $enquiry->message),
            self::line('Received at', now()->format('d M Y, g:i A')),
        ], 'Open Enquiries', $link);
    }

    // =====================================================================
    // IN-APP ONLY EVENTS
    // =====================================================================

    public static function studentEvent(string $event, Student $student, string $label): void
    {
        $cat = self::STUDENTS;
        $rec = self::recipients($cat, 'inapp');
        $link = url('/crm/students/' . $student->id);

        self::inApp($rec, $event, $cat, $label, "{$student->full_name} — {$label}", $link);
    }

    public static function documentEvent(string $event, Student $student, string $label, string $detail): void
    {
        $cat = self::DOCUMENTS;
        $rec = self::recipients($cat, 'inapp');
        $link = url('/crm/students/' . $student->id);

        self::inApp($rec, $event, $cat, $label, "{$detail} for {$student->full_name}", $link);
    }
}
