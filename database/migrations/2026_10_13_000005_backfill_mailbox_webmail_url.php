<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mailboxes')) {
            return;
        }

        // Backfill an empty webmail URL for existing mailboxes.
        DB::table('mailboxes')
            ->where(function ($q) {
                $q->whereNull('webmail_url')->orWhere('webmail_url', '');
            })
            ->update(['webmail_url' => 'https://mail.ideacs.com.np']);
    }

    public function down(): void
    {
        // Nothing to revert.
    }
};
