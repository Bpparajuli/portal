<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('activity_type', [
                'call', 'email', 'meeting', 'whatsapp', 'todo',
                'follow_up', 'counseling', 'document_review', 'note', 'stage_change'
            ]);
            $table->string('subject');
            $table->text('description')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            $table->enum('priority_time_slot', ['morning', 'afternoon', 'evening'])->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled', 'missed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('completion_note')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancellation_note')->nullable();
            $table->enum('call_direction', ['inbound', 'outbound'])->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['student_id', 'subject', 'scheduled_for', 'activity_type'], 'unique_task_per_student');
            $table->index('student_id');
            $table->index('assigned_to');
            $table->index('scheduled_for');
            $table->index('status');
            $table->index('activity_type');
            $table->index('priority_time_slot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_tasks');
    }
};
