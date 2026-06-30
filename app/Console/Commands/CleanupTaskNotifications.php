<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupTaskNotifications extends Command
{
    protected $signature = 'tasks:cleanup-notifications
                            {--stale : Remove due_today/overdue notifications for past-due pending tasks too}';

    protected $description = 'Remove notifications for completed, cancelled, or deleted tasks';

    public function handle(): int
    {
        $removed = 0;

        // 1. Completed / cancelled / missed tasks
        $ids = DB::table('crm_tasks')
            ->whereIn('status', ['completed', 'cancelled', 'missed'])
            ->pluck('id');

        foreach ($ids->chunk(500) as $chunk) {
            $del = DB::table('notifications')
                ->where('type', 'App\Notifications\CrmTaskNotification')
                ->whereRaw('JSON_EXTRACT(`data`, \'$.task_id\') IN (' . implode(',', $chunk->toArray()) . ')')
                ->delete();
            $removed += $del;
        }

        $this->info("Removed {$removed} notifications for completed/cancelled tasks.");

        // 2. Soft-deleted tasks
        $deletedIds = DB::table('crm_tasks')->whereNotNull('deleted_at')->pluck('id');

        foreach ($deletedIds->chunk(500) as $chunk) {
            $del = DB::table('notifications')
                ->where('type', 'App\Notifications\CrmTaskNotification')
                ->whereRaw('JSON_EXTRACT(`data`, \'$.task_id\') IN (' . implode(',', $chunk->toArray()) . ')')
                ->delete();
            $removed += $del;
        }

        $this->info("Removed {$removed} total notifications.");

        // 3. Stale due_today/overdue for past-due pending tasks
        if ($this->option('stale')) {
            $pastDueIds = DB::table('crm_tasks')
                ->where('status', 'pending')
                ->whereNotNull('scheduled_for')
                ->where('scheduled_for', '<', now())
                ->pluck('id');

            $staleRemoved = 0;
            foreach ($pastDueIds->chunk(500) as $chunk) {
                $del = DB::table('notifications')
                    ->where('type', 'App\Notifications\CrmTaskNotification')
                    ->where(function($q) {
                        $q->whereRaw('JSON_EXTRACT(`data`, \'$.subtype\') = \'"due_today"\'')
                          ->orWhereRaw('JSON_EXTRACT(`data`, \'$.subtype\') = \'"overdue"\'');
                    })
                    ->whereRaw('JSON_EXTRACT(`data`, \'$.task_id\') IN (' . implode(',', $chunk->toArray()) . ')')
                    ->delete();
                $staleRemoved += $del;
            }
            $this->info("Removed {$staleRemoved} stale due_today/overdue notifications.");
            $removed += $staleRemoved;
        }

        $this->info("Done. Notifications table now has " . DB::table('notifications')->count() . " rows.");

        return Command::SUCCESS;
    }
}
