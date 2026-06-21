<?php

namespace App\Console\Commands;

use App\Services\EmailSyncService;
use Ddeboer\Imap\Server;
use Illuminate\Console\Command;

class TestImapConnection extends Command
{
    protected $signature = 'emails:test-connection';
    protected $description = 'Test the IMAP connection with configured settings';

    public function handle(EmailSyncService $sync): int
    {
        $config = $sync->getConfig();

        $this->table(
            ['Setting', 'Value'],
            [
                ['IMAP Enabled', $sync->isEnabled() ? 'Yes' : 'No'],
                ['Host', $config['host'] ?: '(not set)'],
                ['Port', $config['port']],
                ['Username', $config['username'] ?: '(not set)'],
                ['Encryption', $config['encryption']],
                ['Mailbox', $config['mailbox']],
                ['Password', $config['password'] ? '******' : '(not set)'],
            ]
        );

        if (!$config['host'] || !$config['username']) {
            $this->error('IMAP is not configured. Set imap_host, imap_username, and imap_password in Settings > Email.');
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('Connecting to IMAP server...');

        try {
            $server = new Server($config['host'], $config['port'], $config['encryption']);
            $connection = $server->authenticate($config['username'], $config['password']);

            $mailboxes = $connection->getMailboxes();
            $this->info('✓ Connection successful!');
            $this->newLine();
            $this->info('Available mailboxes:');

            $rows = [];
            foreach ($mailboxes as $mailbox) {
                $rows[] = [$mailbox->getName(), $mailbox->count() . ' messages'];
            }
            $this->table(['Mailbox', 'Messages'], $rows);

            $connection->close();
        } catch (\Exception $e) {
            $this->error('✗ Connection failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
