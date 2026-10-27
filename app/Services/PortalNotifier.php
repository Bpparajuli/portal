<?php

namespace App\Services;

use App\Mail\PortalNotificationMail;
use App\Models\Mailbox;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Routes portal activity notifications to email inboxes.
 *
 * Matrix (confirmed):
 *   portal@            → receives a copy of EVERY category
 *   admin@             → main events (registration, agreement, messages to admin)
 *   student@           → student added / removed / updated
 *   document@          → document added
 *   application@       → application added
 *   enquiry@           → new enquiries
 *
 * Recipients are read from the `mailboxes` table (category column), so the
 * destination addresses are configurable without code changes.
 */
class PortalNotifier
{
    public const PORTAL      = 'portal';
    public const ADMIN       = 'admin';
    public const DOCUMENT    = 'document';
    public const STUDENT     = 'student';
    public const APPLICATION = 'application';
    public const ENQUIRY     = 'enquiry';

    /** Resolve the recipient list for a given category. */
    public static function recipients(string $category): array
    {
        try {
            $mailboxes = Mailbox::active()->get();
        } catch (\Throwable $e) {
            // Mailboxes table may not be migrated yet — fall back to defaults.
            $mailboxes = collect();
        }

        $byCategory = fn (string $cat) => $mailboxes
            ->where('category', $cat)
            ->pluck('email')
            ->filter()
            ->values()
            ->all();

        $portal = $byCategory(self::PORTAL) ?: ['portal@ideacs.com.np'];

        $admin = $byCategory(self::ADMIN) ?: ['admin@ideacs.com.np'];

        if ($category === self::PORTAL) {
            return array_values(array_unique(array_filter($portal)));
        }

        if ($category === self::ADMIN) {
            return array_values(array_unique(array_filter(array_merge($portal, $admin))));
        }

        $own = $byCategory($category);
        if (empty($own)) {
            $own = $admin; // graceful fallback
        }

        return array_values(array_unique(array_filter(array_merge($portal, $own))));
    }

    /**
     * Send a routed activity notification.
     *
     * @param  string       $category    One of the category constants.
     * @param  string       $subject     Email subject.
     * @param  array        $introLines  Body lines (simple HTML allowed).
     * @param  string|null  $actionText  Optional button label.
     * @param  string|null  $actionUrl   Optional button URL.
     * @param  array        $outroLines  Optional footer lines.
     * @param  array        $extra       Extra recipients.
     */
    public static function send(
        string $category,
        string $subject,
        array $introLines = [],
        ?string $actionText = null,
        ?string $actionUrl = null,
        array $outroLines = [],
        array $extra = []
    ): void {
        if (! MailConfigService::isSendable()) {
            return;
        }

        $recipients = array_values(array_unique(array_filter(
            array_merge(self::recipients($category), $extra)
        )));

        if (empty($recipients)) {
            return;
        }

        // Primary recipient is "To", everyone else is BCC'd so addresses stay private.
        $primary = array_shift($recipients);

        try {
            $mail = Mail::to($primary);
            if (! empty($recipients)) {
                $mail->bcc($recipients);
            }

            $mail->send(new PortalNotificationMail(
                mailSubject: $subject,
                introLines: $introLines,
                greeting: null,
                actionText: $actionText,
                actionUrl: $actionUrl,
                outroLines: $outroLines,
            ));
        } catch (\Throwable $e) {
            Log::error('PortalNotifier send failed: ' . $e->getMessage(), [
                'category' => $category,
                'subject'  => $subject,
            ]);
        }
    }

    /** Convenience formatter for a "Label: value" line. */
    public static function line(string $label, $value): string
    {
        return '<strong>' . e($label) . ':</strong> ' . e((string) $value);
    }
}
