<?php

namespace App\Console\Commands;

use App\Services\EmailSyncService;
use Illuminate\Console\Command;

class ImportAllEmails extends Command
{
    protected $signature = 'emails:import-all {--force : Skip confirmation}';
    protected $description = 'Import all historical emails from the configured IMAP mailbox';

    public function handle(EmailSyncService $sync): int
    {
        if (!$sync->isEnabled()) {
            $this->error('IMAP is not enabled. Configure imap_enabled, imap_host, imap_username, and imap_password in Settings > Email first.');
            $this->line('Run: php artisan emails:test-connection to verify your IMAP settings.');
            return Command::FAILURE;
        }

        if (!$this->option('force') && !$this->confirm('This will import ALL emails from the connected IMAP account. Existing emails with the same Message-ID will be skipped. Continue?')) {
            $this->info('Cancelled.');
            return Command::SUCCESS;
        }

        $this->info('Connecting to IMAP server and importing all emails...');
        $this->newLine();

        $bar = $this->output->createProgressBar(1);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

        $result = $sync->importAllEmails();

        $bar->finish();
        $this->newLine(2);

        $this->info("Import complete!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Imported', $result['imported']],
                ['Skipped (already exist)', $result['skipped']],
                ['Errors', $result['errors']],
            ]
        );

        return Command::SUCCESS;
    }
}
