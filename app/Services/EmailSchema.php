<?php

namespace App\Services;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ensures the email-related tables exist even when migrations have not been
 * run on a given database (e.g. production deploys with an out-of-sync
 * migrations table). Safe to call repeatedly.
 */
class EmailSchema
{
    /** Create any missing email tables. Returns the names created. */
    public static function ensureAll(): array
    {
        $created = [];

        try {
            if (self::ensureBulkEmails()) {
                $created[] = 'bulk_emails';
            }
        } catch (\Throwable $e) {
        }

        try {
            $queue = self::ensureQueue();
            if ($queue) {
                $created[] = 'jobs';
                $created[] = 'failed_jobs';
            }
        } catch (\Throwable $e) {
        }

        try {
            if (self::ensureMailboxes()) {
                $created[] = 'mailboxes';
            }
        } catch (\Throwable $e) {
        }

        return $created;
    }

    public static function ensureBulkEmails(): bool
    {
        $created = false;

        if (! Schema::hasTable('bulk_emails')) {
            Schema::create('bulk_emails', function (Blueprint $table) {
                $table->id();
                $table->string('subject');
                $table->text('body');
                $table->string('recipient_group')->default('students');
                $table->unsignedInteger('recipients_count')->default(0);
                $table->unsignedInteger('sent_count')->default(0);
                $table->unsignedInteger('failed_count')->default(0);
                $table->string('status')->default('queued');
                $table->json('attachments')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->index('status');
            });

            $created = true;
        }

        // Needed for the "Resend" button on existing installs.
        if (Schema::hasTable('bulk_emails') && ! Schema::hasColumn('bulk_emails', 'recipients')) {
            Schema::table('bulk_emails', function (Blueprint $table) {
                $table->json('recipients')->nullable();
            });
        }

        return $created;
    }

    public static function ensureQueue(): bool
    {
        $created = false;

        if (! Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
            $created = true;
        }

        if (! Schema::hasTable('failed_jobs')) {
            Schema::create('failed_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
            $created = true;
        }

        return $created;
    }

    public static function ensureMailboxes(): bool
    {
        if (Schema::hasTable('mailboxes')) {
            return false;
        }

        Schema::create('mailboxes', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('email');
            $table->string('category')->nullable();
            $table->string('imap_host')->nullable();
            $table->unsignedInteger('imap_port')->default(993);
            $table->string('imap_username')->nullable();
            $table->string('imap_password')->nullable();
            $table->string('imap_encryption')->default('ssl');
            $table->string('webmail_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('last_unread_count')->default(0);
            $table->unsignedInteger('last_total_count')->default(0);
            $table->timestamp('last_checked_at')->nullable();
            $table->string('last_error')->nullable();
            $table->timestamps();
            $table->index('category');
            $table->index('is_active');
        });

        return true;
    }
}
