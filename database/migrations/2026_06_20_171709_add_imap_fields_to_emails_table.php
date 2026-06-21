<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->string('message_id')->nullable()->after('attachments')->index();
            $table->text('references')->nullable()->after('message_id');
            $table->string('in_reply_to')->nullable()->after('references');
            $table->boolean('is_external')->default(false)->after('in_reply_to')->index();
            $table->string('external_folder')->nullable()->after('is_external');
        });
    }

    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->dropColumn(['message_id', 'references', 'in_reply_to', 'is_external', 'external_folder']);
        });
    }
};
