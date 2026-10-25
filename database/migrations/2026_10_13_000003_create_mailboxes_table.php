<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mailboxes')) {
            return;
        }

        Schema::create('mailboxes', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('email');
            // portal | admin | document | student | application | enquiry
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
    }

    public function down(): void
    {
        Schema::dropIfExists('mailboxes');
    }
};
