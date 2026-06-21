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
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');
            $table->text('message');
            $table->string('file')->nullable();
            $table->string('file_type')->nullable();
            $table->enum('status', ['sent', 'delivered', 'read'])->default('sent');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['sender_id', 'receiver_id'], 'idx_sender_receiver');
            $table->index(['receiver_id', 'read_at'], 'idx_receiver_read');
            $table->index(['sender_id', 'receiver_id', 'created_at'], 'idx_sender_receiver_created');
            $table->index(['receiver_id', 'sender_id', 'created_at'], 'idx_receiver_sender_created');
            $table->index(['receiver_id', 'sender_id', 'read_at'], 'idx_receiver_sender_read');
            $table->foreign('sender_id', 'fk_sender')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('receiver_id', 'fk_receiver')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
