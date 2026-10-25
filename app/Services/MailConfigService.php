<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

/**
 * Central SMTP configuration sourced from the database `settings` table.
 *
 * Historically the app ran with MAIL_MAILER=log and only the webmail
 * (EmailController) reconfigured SMTP on the fly. That meant every
 * Laravel notification (UserRegistered, AgreementSubmitted, ...) was
 * silently written to the log instead of being delivered.
 *
 * Calling configure() during boot makes ALL outgoing mail use the
 * DB-configured SMTP server (with a log fallback so a broken SMTP
 * config can never 500 a request).
 */
class MailConfigService
{
    protected static ?array $settings = null;

    /** Load (once per request) the mail settings from the DB. */
    public static function settings(): array
    {
        if (static::$settings !== null) {
            return static::$settings;
        }

        try {
            if (! Schema::hasTable('settings')) {
                return static::$settings = [];
            }

            $rows = Setting::whereIn('key', [
                'mail_enabled',
                'mail_host',
                'mail_port',
                'mail_username',
                'mail_password',
                'mail_encryption',
                'mail_from_address',
                'mail_from_name',
                'mail_reply_to_address',
                'mail_reply_to_name',
            ])->get();

            $settings = [];
            foreach ($rows as $row) {
                $settings[$row->key] = ($row->is_encrypted && $row->value)
                    ? decrypt($row->value)
                    : $row->value;
            }

            static::$settings = $settings;
        } catch (\Throwable $e) {
            static::$settings = [];
        }

        return static::$settings;
    }

    /** Reset the per-request cache (used after settings change). */
    public static function flush(): void
    {
        static::$settings = null;
    }

    /**
     * Mail is considered enabled when explicitly switched on, or when a
     * host + credentials have been configured.
     */
    public static function isEnabled(): bool
    {
        $s = static::settings();

        if (array_key_exists('mail_enabled', $s) && $s['mail_enabled'] !== null && $s['mail_enabled'] !== '') {
            if (! filter_var($s['mail_enabled'], FILTER_VALIDATE_BOOLEAN)) {
                return false;
            }
        }

        return ! empty($s['mail_host'])
            && ! empty($s['mail_username'])
            && ! empty($s['mail_password']);
    }

    /** Apply the DB mail settings to the runtime config. */
    public static function configure(): bool
    {
        if (! static::isEnabled()) {
            return false;
        }

        $s = static::settings();

        $port = (int) ($s['mail_port'] ?? 587);
        $encryption = strtolower((string) ($s['mail_encryption'] ?? ''));
        $scheme = match ($encryption) {
            'ssl' => 'smtps',
            'tls' => 'smtp',
            default => $port === 465 ? 'smtps' : 'smtp',
        };

        config([
            // Default goes through failover so a bad SMTP connection logs
            // instead of throwing and breaking the request.
            'mail.default' => 'failover',
            'mail.mailers.failover.mailers' => ['smtp', 'log'],

            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.scheme' => $scheme,
            'mail.mailers.smtp.host' => $s['mail_host'],
            'mail.mailers.smtp.port' => $port,
            'mail.mailers.smtp.username' => $s['mail_username'] ?? null,
            'mail.mailers.smtp.password' => $s['mail_password'] ?? null,

            'mail.from.address' => $s['mail_from_address'] ?? config('mail.from.address'),
            'mail.from.name' => $s['mail_from_name'] ?? config('mail.from.name'),
        ]);

        if (! empty($s['mail_reply_to_address'])) {
            config([
                'mail.reply_to.address' => $s['mail_reply_to_address'],
                'mail.reply_to.name' => $s['mail_reply_to_name'] ?? null,
            ]);
        }

        return true;
    }
}
