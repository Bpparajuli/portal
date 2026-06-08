<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->index(['receiver_id', 'read_at'], 'idx_receiver_read');
            $table->index(['sender_id', 'receiver_id', 'created_at'], 'idx_sender_receiver_created');
            $table->index(['receiver_id', 'sender_id', 'created_at'], 'idx_receiver_sender_created');
            $table->index(['receiver_id', 'sender_id', 'read_at'], 'idx_receiver_sender_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex('idx_receiver_read');
            $table->dropIndex('idx_sender_receiver_created');
            $table->dropIndex('idx_receiver_sender_created');
            $table->dropIndex('idx_receiver_sender_read');
        });
    }
};
