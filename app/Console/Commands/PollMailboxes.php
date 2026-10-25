<?php

namespace App\Console\Commands;

use App\Services\MailboxMonitor;
use Illuminate\Console\Command;

class PollMailboxes extends Command
{
    protected $signature = 'mailboxes:poll {--force : Poll even if the counts are still fresh}';

    protected $description = 'Poll all configured mailboxes over IMAP and update unread counts';

    public function handle(MailboxMonitor $monitor): int
    {
        if (! $this->option('force') && ! $monitor->isStale()) {
            $this->line('Mailbox counts are still fresh — skipping.');
            return self::SUCCESS;
        }

        $results = $monitor->pollAll();

        if (empty($results)) {
            $this->warn('No active mailboxes configured.');
            return self::SUCCESS;
        }

        foreach ($results as $id => $result) {
            if ($result['ok']) {
                $this->info("Mailbox #{$id}: {$result['unread']} unread / {$result['total']} total");
            } else {
                $this->error("Mailbox #{$id}: {$result['error']}");
            }
        }

        return self::SUCCESS;
    }
}
