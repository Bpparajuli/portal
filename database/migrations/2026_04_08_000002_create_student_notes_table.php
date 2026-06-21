<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('content');
            $table->string('title')->nullable();
            $table->enum('type', ['internal', 'log'])->default('internal');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_log')->default(false);
            $table->timestamp('remind_at')->nullable();
            $table->enum('reminder_time_slot', ['morning', 'day', 'evening'])->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('student_id');
            $table->index('created_by');
            $table->index('type');
            $table->index('is_pinned');
            $table->index('remind_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_notes');
    }
};
