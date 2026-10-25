<?php

namespace App\Jobs;

use App\Mail\BulkEmailMailable;
use App\Models\BulkEmail;
use App\Models\Student;
use App\Models\User;
use App\Services\MailConfigService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBulkEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries = 1;

    public function __construct(public int $bulkEmailId, public array $recipients = [])
    {
    }

    public function handle(): void
    {
        $bulk = BulkEmail::find($this->bulkEmailId);
        if (! $bulk) {
            return;
        }

        $bulk->update(['status' => 'sending']);

        $emails = ! empty($this->recipients)
            ? collect($this->recipients)
            : $this->resolveRecipients($bulk->recipient_group);

        $emails = $emails->filter()->unique()->values();
        $bulk->update(['recipients_count' => $emails->count()]);

        if (! MailConfigService::isEnabled()) {
            $bulk->update(['status' => 'failed']);
            Log::warning('Bulk email aborted: mail is disabled or not configured.', ['bulk_email_id' => $bulk->id]);
            return;
        }

        MailConfigService::configure();

        $sent = 0;
        $failed = 0;

        foreach ($emails as $email) {
            try {
                Mail::mailer('smtp')->to($email)->send(new BulkEmailMailable($bulk));
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                Log::warning('Bulk email send failed: ' . $e->getMessage(), [
                    'recipient' => $email,
                    'bulk_email_id' => $bulk->id,
                ]);
            }

            $bulk->update(['sent_count' => $sent, 'failed_count' => $failed]);
        }

        $bulk->update(['status' => 'completed']);
    }

    private function resolveRecipients(string $group): Collection
    {
        $students = fn () => Student::whereNotNull('email')
            ->where('email', '!=', '')
            ->pluck('email');

        $users = fn () => User::where('active', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->pluck('email');

        return match ($group) {
            'agents' => User::agents()->where('active', true)
                ->whereNotNull('email')->where('email', '!=', '')
                ->pluck('email')->unique()->values(),
            'all' => $users()->merge($students())->unique()->values(),
            default => $students()->unique()->values(),
        };
    }
}
