<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->string('sender_email');
            $table->string('sender_name')->nullable();

            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->unsignedBigInteger('recipient_id')->nullable();

            $table->string('cc')->nullable();
            $table->string('bcc')->nullable();

            $table->string('subject');
            $table->longText('body');
            $table->longText('body_html')->nullable();

            $table->string('folder')->default('sent')->comment('inbox, sent, drafts, trash');
            $table->enum('status', ['draft', 'sent', 'delivered', 'read', 'failed'])->default('sent');

            $table->unsignedBigInteger('parent_id')->nullable()->comment('for reply/forward chains');
            $table->string('reference_type')->nullable()->comment('application, student, enquiry');
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->json('attachments')->nullable();
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_important')->default(false);

            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('recipient_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('parent_id')->references('id')->on('emails')->nullOnDelete();

            $table->index(['sender_id', 'folder']);
            $table->index(['recipient_email', 'folder']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
