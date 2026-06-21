<?php

namespace App\Console\Commands;

use App\Services\EmailSyncService;
use Illuminate\Console\Command;

class SyncEmails extends Command
{
    protected $signature = 'emails:sync';
    protected $description = 'Fetch new unseen emails from the configured IMAP mailbox';

    public function handle(EmailSyncService $sync): int
    {
        if (!$sync->isEnabled()) {
            $this->warn('IMAP is not enabled. Skipping sync.');
            return Command::SUCCESS;
        }

        $this->info('Checking for new emails...');
        $count = $sync->syncNewEmails();
        $this->info("Imported {$count} new email(s).");

        return Command::SUCCESS;
    }
}
