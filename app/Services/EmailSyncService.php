<?php
namespace App\Services;

use App\Models\Emails;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Ddeboer\Imap\Message\AttachmentInterface;
use Ddeboer\Imap\Server;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmailSyncService
{
    protected array $config = [];
    protected bool $enabled = false;

    public function __construct()
    {
        $this->config = [
            'host'       => Setting::getValue('imap_host', ''),
            'port'       => (int) Setting::getValue('imap_port', 993),
            'username'   => Setting::getValue('imap_username', ''),
            'password'   => Setting::getValue('imap_password', ''),
            'encryption' => Setting::getValue('imap_encryption', 'ssl'),
            'mailbox'    => Setting::getValue('imap_mailbox', 'INBOX'),
        ];
        $this->enabled = (bool) Setting::getValue('imap_enabled', false)
            && !empty($this->config['host'])
            && !empty($this->config['username']);
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Sync unseen emails from the IMAP inbox.
     * Returns count of new emails imported.
     */
    public function syncNewEmails(): array
    {
        if (!$this->enabled) {
            return ['count' => 0, 'error' => 'IMAP not enabled — configure in Settings > Email.'];
        }

        $count = 0;
        $connection = null;

        try {
            $server = new Server(
                $this->config['host'],
                $this->config['port'],
                $this->config['encryption']
            );

            $connection = $server->authenticate($this->config['username'], $this->config['password']);
            $mailbox = $connection->getMailbox($this->config['mailbox']);

            $messages = $mailbox->getMessages();

            foreach ($messages as $message) {
                $messageId = $message->getHeaders()->get('message_id') ?? $message->getNumber();
                $references = $message->getHeaders()->get('references') ?? '';
                $inReplyTo = $message->getHeaders()->get('in_reply_to') ?? '';

                if (Emails::where('message_id', $messageId)->exists()) {
                    continue;
                }

                $from = $message->getFrom();
                $fromEmail = $from ? $from->getAddress() : '';
                $fromName = $from ? ($from->getName() ?? $fromEmail) : $fromEmail;

                $subject = $message->getSubject() ?? '(No Subject)';
                $bodyText = $message->getBodyText() ?? '';
                $bodyHtml = $message->getBodyHtml() ?? '';

                $attachments = [];
                foreach ($message->getAttachments() as $attachment) {
                    $path = $this->storeAttachment($attachment);
                    if ($path) {
                        $attachments[] = $path;
                    }
                }

                $date = $message->getDate();
                $sentAt = $date ? Carbon::instance($date) : now();

                $senderUser = User::where('email', $fromEmail)->first();

                Emails::create([
                    'sender_id'       => $senderUser?->id,
                    'sender_email'    => $fromEmail,
                    'sender_name'     => $fromName,
                    'recipient_email' => $this->config['username'],
                    'recipient_name'  => null,
                    'subject'         => $subject,
                    'body'            => $bodyText ?: strip_tags($bodyHtml),
                    'body_html'       => $bodyHtml ?: null,
                    'folder'          => 'inbox',
                    'status'          => 'delivered',
                    'attachments'     => count($attachments) ? $attachments : null,
                    'message_id'      => $messageId,
                    'references'      => $references,
                    'in_reply_to'     => $inReplyTo,
                    'is_external'     => true,
                    'external_folder' => $this->config['mailbox'],
                    'sent_at'         => $sentAt,
                ]);

                $count++;
            }

            $connection->expunge();
        } catch (\Exception $e) {
            Log::error('EmailSync: IMAP sync failed: ' . $e->getMessage(), [
                'host' => $this->config['host'],
                'username' => $this->config['username'],
            ]);
            return ['count' => 0, 'error' => 'IMAP connection failed: ' . $e->getMessage()];
        } finally {
            if ($connection) {
                $connection->close();
            }
        }

        return ['count' => $count, 'error' => null];
    }

    /**
     * Import ALL emails from the configured IMAP mailbox.
     * This is a one-time operation for importing historical emails.
     */
    public function importAllEmails(): array
    {
        $result = ['imported' => 0, 'skipped' => 0, 'errors' => 0];

        if (!$this->enabled) return $result;

        try {
            $server = new Server(
                $this->config['host'],
                $this->config['port'],
                $this->config['encryption']
            );

            $connection = $server->authenticate($this->config['username'], $this->config['password']);

            $mailboxes = $connection->getMailboxes();

            foreach ($mailboxes as $mailbox) {
                $mailboxName = $mailbox->getName();

                if (str_starts_with($mailboxName, '[Gmail]') && PHP_OS_FAMILY === 'Windows') {
                    continue;
                }

                $folder = match (true) {
                    str_contains($mailboxName, 'INBOX') || str_contains($mailboxName, 'Inbox') => 'inbox',
                    str_contains($mailboxName, 'Sent') || str_contains($mailboxName, 'SENT') => 'sent',
                    str_contains($mailboxName, 'Draft') || str_contains($mailboxName, 'DRAFT') => 'drafts',
                    default => 'inbox',
                };

                $messages = $mailbox->getMessages();

                foreach ($messages as $message) {
                    try {
                        $messageId = $message->getHeaders()->get('message_id') ?? $message->getNumber();

                        if (Emails::where('message_id', $messageId)->exists()) {
                            $result['skipped']++;
                            continue;
                        }

                        $from = $message->getFrom();
                        $fromEmail = $from ? $from->getAddress() : '';
                        $fromName = $from ? ($from->getName() ?? $fromEmail) : $fromEmail;

                        $to = $message->getTo();
                        $toAddresses = [];
                        foreach ($to as $recipient) {
                            $toAddresses[] = $recipient->getAddress();
                        }
                        $recipientEmail = $toAddresses[0] ?? $this->config['username'];

                        $subject = $message->getSubject() ?? '(No Subject)';
                        $bodyText = $message->getBodyText() ?? '';
                        $bodyHtml = $message->getBodyHtml() ?? '';

                        $attachments = [];
                        foreach ($message->getAttachments() as $attachment) {
                            $path = $this->storeAttachment($attachment);
                            if ($path) {
                                $attachments[] = $path;
                            }
                        }

                        $date = $message->getDate();
                        $sentAt = $date ? Carbon::instance($date) : now();

                        $references = $message->getHeaders()->get('references') ?? '';
                        $inReplyTo = $message->getHeaders()->get('in_reply_to') ?? '';

                        Emails::create([
                            'sender_id'       => null,
                            'sender_email'    => $fromEmail,
                            'sender_name'     => $fromName,
                            'recipient_email' => $recipientEmail,
                            'recipient_name'  => null,
                            'subject'         => $subject,
                            'body'            => $bodyText ?: strip_tags($bodyHtml),
                            'body_html'       => $bodyHtml ?: null,
                            'folder'          => $folder,
                            'status'          => $folder === 'drafts' ? 'draft' : 'delivered',
                            'attachments'     => count($attachments) ? $attachments : null,
                            'message_id'      => $messageId,
                            'references'      => $references,
                            'in_reply_to'     => $inReplyTo,
                            'is_external'     => true,
                            'external_folder' => $mailboxName,
                            'sent_at'         => $sentAt,
                        ]);

                        $result['imported']++;
                    } catch (\Exception $e) {
                        $result['errors']++;
                        Log::error('EmailSync: Failed to import message: ' . $e->getMessage());
                    }
                }

                if (!$mailbox->isWritable()) {
                    continue;
                }
            }

            $connection->expunge();
        } catch (\Exception $e) {
            Log::error('EmailSync: Import all failed: ' . $e->getMessage());
        } finally {
            if (isset($connection)) {
                $connection->close();
            }
        }

        return $result;
    }

    protected function storeAttachment(AttachmentInterface $attachment): ?array
    {
        try {
            if (!$attachment->getFilename()) return null;

            $dir = 'email_attachments/imap/' . date('Y/m');
            $filename = uniqid() . '_' . $attachment->getFilename();
            $path = $dir . '/' . $filename;

            Storage::disk('public')->put($path, $attachment->getDecodedContent());

            return [
                'path' => $path,
                'name' => $attachment->getFilename(),
                'size' => strlen($attachment->getDecodedContent()),
            ];
        } catch (\Exception $e) {
            Log::error('EmailSync: Failed to store attachment: ' . $e->getMessage());
            return null;
        }
    }
}
