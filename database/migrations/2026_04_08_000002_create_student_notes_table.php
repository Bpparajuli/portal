<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentNotesTable extends Migration
{
    public function up()
    {
        Schema::create('student_notes', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Note Content
            $table->text('content');

            // Note Type
            $table->enum('type', [
                'internal',
                'customer_visible',
                'reminder'
            ])->default('internal');

            // Pin Important Notes
            $table->boolean('is_pinned')->default(false);

            // Reminder Fields
            $table->timestamp('remind_at')->nullable();

            $table->enum('reminder_time_slot', [
                'morning',
                'day',
                'evening'
            ])->nullable();

            // Laravel Default Timestamps
            $table->timestamps();

            // Soft Delete
            $table->softDeletes();

            // Indexes (matches your actual table)
            $table->index('student_id');
            $table->index('created_by');
            $table->index('type');
            $table->index('is_pinned');
            $table->index('remind_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_notes');
    }
}
