<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrmTasksTable extends Migration
{
    public function up()
    {
        Schema::create('crm_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('activity_type', ['call', 'email', 'meeting', 'whatsapp', 'todo', 'follow_up', 'counseling', 'document_review', 'note', 'stage_change']);
            $table->string('subject');
            $table->text('description')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->enum('priority_time_slot', ['morning', 'day', 'evening'])->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled', 'missed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->enum('call_direction', ['inbound', 'outbound'])->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('student_id');
            $table->index('assigned_to');
            $table->index('scheduled_at');
            $table->index('status');
            $table->index('priority_time_slot');
            $table->index('activity_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('crm_tasks');
    }
}
