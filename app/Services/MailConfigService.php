<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

/**
 * Resolves the outbound mail configuration.
 *
 * Priority:
 *   1. If the deployment already has a real mailer in .env (smtp/ses/...),
 *      TRUST IT — this is what was working before. We only sync the From
 *      address from the DB.
 *   2. Otherwise (MAIL_MAILER=log/array/unset) use the DB SMTP settings,
 *      routed through `failover` so bad credentials fall back to the log
 *      instead of throwing.
 */
class MailConfigService
{
    protected static ?array $settings = null;
    protected static ?string $originalDefault = null;
    protected static ?array $envSmtp = null;

    private static function captureEnv(): void
    {
        if (static::$originalDefault === null) {
            static::$originalDefault = (string) config('mail.default');
            static::$envSmtp = [
                'host' => config('mail.mailers.smtp.host'),
                'username' => config('mail.mailers.smtp.username'),
            ];
        }
    }

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

    /** The mailer configured in .env at boot (before we touch config). */
    public static function envDefault(): string
    {
        static::captureEnv();

        return static::$originalDefault;
    }

    /** True when .env defines a real, usable mailer. */
    public static function envMailerIsReal(): bool
    {
        static::captureEnv();

        $default = static::$originalDefault;

        if (in_array($default, ['log', 'array', ''], true)) {
            return false;
        }

        // For SMTP, require an actual host + username; otherwise treat as unconfigured.
        if ($default === 'smtp') {
            return ! empty(static::$envSmtp['host']) && ! empty(static::$envSmtp['username']);
        }

        return true;
    }

    /**
     * Mail is considered enabled when explicitly switched on, or when a
     * host + credentials have been configured in the DB.
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

    /** Can we send at all (env mailer or DB config)? */
    public static function isSendable(): bool
    {
        return static::envMailerIsReal() || static::isEnabled();
    }

    /**
     * Which mailer name should be used explicitly for portal mail?
     * Returns null to mean "use the default mailer" (env-configured).
     */
    public static function mailerName(): ?string
    {
        return static::envMailerIsReal() ? null : 'smtp';
    }

    /** Apply the resolved mail configuration to runtime config. */
    public static function configure(): bool
    {
        $s = static::settings();

        $fromAddress = $s['mail_from_address'] ?? null;
        $fromName = $s['mail_from_name'] ?? null;

        // Trust a real .env mailer (this is what worked before our changes).
        if (static::envMailerIsReal()) {
            if (! empty($fromAddress)) {
                config([
                    'mail.from.address' => $fromAddress,
                    'mail.from.name' => $fromName ?: config('mail.from.name'),
                ]);
            }
            return true;
        }

        // Otherwise use the DB-configured SMTP via failover.
        if (! static::isEnabled()) {
            return false;
        }

        $port = (int) ($s['mail_port'] ?? 587);
        $encryption = strtolower((string) ($s['mail_encryption'] ?? ''));
        $scheme = match ($encryption) {
            'ssl' => 'smtps',
            'tls' => 'smtp',
            default => $port === 465 ? 'smtps' : 'smtp',
        };

        config([
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

    /** Effective config summary for the diagnostics page (password masked). */
    public static function describe(): array
    {
        return [
            'env_default' => static::envDefault(),
            'env_mailer_is_real' => static::envMailerIsReal(),
            'db_enabled' => static::isEnabled(),
            'effective_default' => config('mail.default'),
            'smtp_host' => config('mail.mailers.smtp.host'),
            'smtp_port' => config('mail.mailers.smtp.port'),
            'smtp_scheme' => config('mail.mailers.smtp.scheme'),
            'smtp_username' => config('mail.mailers.smtp.username'),
            'smtp_password' => config('mail.mailers.smtp.password') ? '******(set)' : '(empty)',
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ];
    }
}
