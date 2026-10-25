<?php

namespace App\Services;

use App\Models\Mailbox;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Polls each real mailbox (student@, document@, ...) over IMAP and stores
 * the unread / total counts. The portal never imports message bodies —
 * it only tracks counts so the navbar bell and login popup can notify.
 */
class MailboxMonitor
{
    public const CACHE_LAST_POLL = 'mailboxes.last_poll';
    public const CACHE_REFRESH_LOCK = 'mailboxes.refresh_lock';

    /** How often (seconds) the mailboxes should be re-polled. */
    public static function refreshSeconds(): int
    {
        $seconds = (int) Setting::getValue('mail_refresh_seconds', 60);

        return max(30, $seconds ?: 60);
    }

    /** True when the stored counts are older than the refresh interval. */
    public static function isStale(): bool
    {
        $last = (int) Cache::get(self::CACHE_LAST_POLL, 0);

        return $last === 0 || (time() - $last) >= self::refreshSeconds();
    }

    /** Poll every active mailbox. Returns the per-mailbox results. */
    public function pollAll(?int $maxSeconds = null): array
    {
        $results = [];
        $started = microtime(true);

        foreach (Mailbox::active()->get() as $mailbox) {
            $results[$mailbox->id] = $this->poll($mailbox);

            if ($maxSeconds !== null && (microtime(true) - $started) >= $maxSeconds) {
                break;
            }
        }

        Cache::put(self::CACHE_LAST_POLL, time(), 3600);

        return $results;
    }

    /** Poll a single mailbox and persist the counts. */
    public function poll(Mailbox $mailbox): array
    {
        if (! $mailbox->is_active) {
            return ['ok' => false, 'error' => 'Mailbox is inactive'];
        }

        if (! $mailbox->imap_host || ! $mailbox->imap_username || ! $mailbox->imap_password) {
            $mailbox->update([
                'last_checked_at' => now(),
                'last_error' => 'IMAP credentials incomplete',
            ]);
            return ['ok' => false, 'error' => 'IMAP credentials incomplete'];
        }

        $port = (int) ($mailbox->imap_port ?: 993);
        $encryption = $mailbox->imap_encryption ?: 'ssl';
        $spec = '{' . $mailbox->imap_host . ':' . $port . '/imap/' . $encryption . '}INBOX';
        $previousUnread = (int) $mailbox->last_unread_count;

        try {
            if (function_exists('imap_timeout')) {
                @imap_timeout(IMAP_OPENTIMEOUT, 10);
                @imap_timeout(IMAP_READTIMEOUT, 10);
                @imap_timeout(IMAP_WRITETIMEOUT, 10);
            }

            $stream = @imap_open($spec, $mailbox->imap_username, $mailbox->imap_password, 0, 1);

            if (! $stream) {
                $error = imap_last_error() ?: 'Could not connect to IMAP server';
                imap_errors();
                imap_alerts();
                throw new \Exception($error);
            }

            $status = @imap_status($stream, $spec, SA_UNSEEN | SA_MESSAGES);
            $unread = $status ? (int) $status->unseen : 0;
            $total = $status ? (int) $status->messages : 0;
            imap_close($stream);

            $mailbox->update([
                'last_unread_count' => $unread,
                'last_total_count' => $total,
                'last_checked_at' => now(),
                'last_error' => null,
            ]);

            return [
                'ok' => true,
                'unread' => $unread,
                'total' => $total,
                'previous_unread' => $previousUnread,
                'is_new' => $unread > $previousUnread,
            ];
        } catch (\Throwable $e) {
            $mailbox->update([
                'last_checked_at' => now(),
                'last_error' => mb_substr($e->getMessage(), 0, 250),
            ]);

            Log::warning('Mailbox poll failed: ' . $e->getMessage(), ['mailbox' => $mailbox->email]);

            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /** Total unread across the mailboxes the current user can see. */
    public static function totalUnread(): int
    {
        return (int) Mailbox::active()
            ->accessibleTo(\Illuminate\Support\Facades\Auth::user())
            ->sum('last_unread_count');
    }

    /** Per-mailbox unread breakdown for the mailboxes the current user can see. */
    public static function breakdown()
    {
        return Mailbox::active()
            ->accessibleTo(\Illuminate\Support\Facades\Auth::user())
            ->orderBy('label')
            ->get([
                'id', 'label', 'email', 'category',
                'last_unread_count', 'last_total_count',
                'last_checked_at', 'last_error', 'webmail_url',
            ]);
    }
}
