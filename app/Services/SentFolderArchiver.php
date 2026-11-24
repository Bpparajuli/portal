<?php

namespace App\Services;

use App\Models\Mailbox;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

/**
 * Saves a copy of every outgoing email into the Sent folder of the central
 * mailbox (portal@) over IMAP, so it shows up in the normal mail client's
 * Sent folder.
 *
 * Reuses a single IMAP connection per request/process (bulk email is fast).
 */
class SentFolderArchiver
{
    private static $stream = null;
    private static ?string $ref = null;
    private static ?string $folder = null;

    public static function append(string $rawMessage): void
    {
        if (trim($rawMessage) === '') {
            return;
        }

        if (! filter_var(Setting::getValue('save_sent_copy', true), FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        try {
            if (! self::ensureConnection()) {
                return;
            }

            if (self::$folder) {
                $ok = @imap_append(self::$stream, self::$folder, $rawMessage, '\\Seen');
                if (! $ok) {
                    // Connection may be stale (e.g. long-running queue worker) — reset.
                    self::reset();
                }
            }
        } catch (\Throwable $e) {
            Log::warning('SentFolderArchiver append failed: ' . $e->getMessage());
        }
    }

    private static function reset(): void
    {
        if (self::$stream) {
            @imap_close(self::$stream);
        }
        self::$stream = null;
        self::$ref = null;
        self::$folder = null;
    }

    private static function ensureConnection(): bool
    {
        if (self::$stream) {
            return true;
        }

        $conn = self::connection();
        if (! $conn) {
            return false;
        }

        [$host, $port, $user, $pass, $enc] = $conn;

        $connection = ImapConnector::open($host, $port, $enc, $user, $pass, 'INBOX', 8);

        if (! $connection['stream']) {
            Log::warning('SentFolderArchiver: could not connect to IMAP.', ['errors' => $connection['error']]);
            return false;
        }

        self::$stream = $connection['stream'];
        self::$ref = $connection['ref'];
        self::$folder = self::resolveSentFolder(self::$stream, self::$ref);

        return self::$folder !== null;
    }

    private static function connection(): ?array
    {
        // Prefer the central portal mailbox.
        try {
            $mailbox = Mailbox::where('category', 'portal')->where('is_active', 1)->first()
                ?? Mailbox::where('is_active', 1)->whereNotNull('imap_host')->first();

            if ($mailbox && $mailbox->imap_host && $mailbox->imap_username && $mailbox->imap_password) {
                return [
                    $mailbox->imap_host,
                    (int) ($mailbox->imap_port ?: 993),
                    $mailbox->imap_username,
                    $mailbox->imap_password,
                    $mailbox->imap_encryption ?: 'ssl',
                ];
            }
        } catch (\Throwable $e) {
            // fall through to settings
        }

        // Fallback to the global mail settings.
        $host = Setting::getValue('mail_host');
        $user = Setting::getValue('mail_username');
        $pass = Setting::getValue('mail_password');

        if ($host && $user && $pass) {
            $enc = strtolower((string) Setting::getValue('mail_encryption', 'ssl')) ?: 'ssl';
            $port = (int) Setting::getValue('mail_port', $enc === 'ssl' ? 993 : 143);

            return [$host, $port ?: 993, $user, $pass, $enc];
        }

        return null;
    }

    private static function resolveSentFolder($stream, string $ref): ?string
    {
        $configured = trim((string) Setting::getValue('mail_sent_folder', ''));
        if ($configured !== '') {
            return str_starts_with($configured, '{') ? $configured : $ref . $configured;
        }

        $list = @imap_list($stream, $ref, '*');

        if (is_array($list) && ! empty($list)) {
            $candidates = [];
            foreach ($list as $mbox) {
                $short = str_replace($ref, '', $mbox);
                if (preg_match('/sent/i', $short)) {
                    $candidates[] = $mbox;
                }
            }

            foreach ($candidates as $c) {
                $short = str_replace($ref, '', $c);
                if (preg_match('/^(INBOX\.)?Sent$/i', $short)) {
                    return $c;
                }
            }

            if (! empty($candidates)) {
                return $candidates[0];
            }
        }

        return $ref . 'INBOX.Sent';
    }
}
