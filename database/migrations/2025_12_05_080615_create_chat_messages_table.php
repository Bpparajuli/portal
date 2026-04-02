<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {

            $table->bigIncrements('id');

            // Users
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');

            // Message content
            $table->text('message');

            // File support
            $table->string('file')->nullable();
            $table->string('file_type')->nullable();

            // WhatsApp-style status
            $table->enum('status', ['sent', 'delivered', 'read'])
                ->default('sent');

            // Tracking timestamps
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['sender_id', 'receiver_id'], 'idx_sender_receiver');

            // Foreign keys (also create indexes automatically)
            $table->foreign('sender_id', 'fk_sender')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('receiver_id', 'fk_receiver')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
