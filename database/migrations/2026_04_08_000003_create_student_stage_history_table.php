<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_stage_history', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('from_stage_id')->nullable();
            $table->unsignedBigInteger('to_stage_id');
            $table->unsignedBigInteger('changed_by');

            // Additional info
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable(); // For extra data like previous assignee, etc.

            // Duration tracking (in days/hours)
            $table->integer('days_in_previous_stage')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('student_id');
            $table->index(['student_id', 'created_at']);
            $table->index('from_stage_id');
            $table->index('to_stage_id');
            $table->index('changed_by');

            // Foreign keys
            $table->foreign('student_id')
                ->references('id')->on('students')
                ->onDelete('cascade');

            $table->foreign('from_stage_id')
                ->references('id')->on('student_stages')
                ->onDelete('set null');

            $table->foreign('to_stage_id')
                ->references('id')->on('student_stages')
                ->onDelete('cascade');

            $table->foreign('changed_by')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_stage_history');
    }
};
