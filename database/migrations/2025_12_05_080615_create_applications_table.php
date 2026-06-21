<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('university_id');
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('application_status_id')->nullable();
            $table->enum('application_status', [
                'Application started', 'Application viewed by Admin', 'Applied to University',
                'Need to give the test', 'Accepted by the University', 'Rejected by the University',
                'Applied to another university', 'Application forwarded to embassy',
                'Is on waiting list on Embassy', 'Visa Approved', 'Visa Rejected', 'Lost', 'Withdrawn'
            ])->default('Application started');
            $table->string('application_number')->nullable()->unique();
            $table->string('sop_file')->nullable();
            $table->timestamps();
            $table->timestamp('withdrawn_at')->nullable();
            $table->string('withdraw_reason')->nullable();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('university_id')->references('id')->on('universities')->cascadeOnDelete();
            $table->foreign('course_id')->references('id')->on('courses')->nullOnDelete();
            $table->foreign('agent_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('application_status_id', 'fk_application_status')->references('id')->on('application_statuses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
