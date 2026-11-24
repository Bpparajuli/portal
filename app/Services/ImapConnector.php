<?php

namespace App\Services;

/**
 * Opens an IMAP connection and transparently retries without certificate
 * validation when the server presents a certificate that does not match the
 * hostname (common on shared hosting, e.g. ideacs.com.np served by a
 * certificate issued for the hosting server itself).
 *
 * A valid certificate is always preferred; the relaxed attempt is only used
 * when the initial secure connection is rejected specifically because of a
 * certificate/hostname problem.
 */
class ImapConnector
{
    /**
     * @return array{stream: resource|false, ref: string, spec: string, error: string|null}
     */
    public static function open(
        string $host,
        int $port,
        string $encryption,
        string $username,
        string $password,
        string $folder = 'INBOX',
        int $timeout = 5
    ): array {
        $base = '{' . $host . ':' . $port . '/imap/' . $encryption;

        if (function_exists('imap_timeout')) {
            @imap_timeout(IMAP_OPENTIMEOUT, $timeout);
            @imap_timeout(IMAP_READTIMEOUT, $timeout);
            @imap_timeout(IMAP_WRITETIMEOUT, $timeout);
        }

        $spec = $base . '}' . $folder;
        $stream = @imap_open($spec, $username, $password, 0, 1);

        if ($stream) {
            return ['stream' => $stream, 'ref' => $base, 'spec' => $spec, 'error' => null];
        }

        $error = imap_last_error() ?: 'Could not connect to IMAP server';
        imap_errors();
        imap_alerts();

        if (! self::isCertificateError($error)) {
            return ['stream' => false, 'ref' => $base, 'spec' => $spec, 'error' => $error];
        }

        // The certificate exists but does not cover this hostname. Retry
        // without validation so mailboxes on shared hosting still work.
        $base .= '/novalidate-cert';
        $spec = $base . '}' . $folder;
        $stream = @imap_open($spec, $username, $password, 0, 1);

        if ($stream) {
            return ['stream' => $stream, 'ref' => $base, 'spec' => $spec, 'error' => null];
        }

        $error = imap_last_error() ?: $error;
        imap_errors();
        imap_alerts();

        return ['stream' => false, 'ref' => $base, 'spec' => $spec, 'error' => $error];
    }

    public static function isCertificateError(string $error): bool
    {
        return (bool) preg_match(
            '/certificate|hostname mismatch|self.signed|unable to get local issuer|verify/i',
            $error
        );
    }
}
