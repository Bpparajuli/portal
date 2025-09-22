<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('university_id');
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('agent_id');
            $table->enum('application_status', [
                'Application created',
                'Application viewed by Admin',
                'Applied to University',
                'Need to give the test',
                'Accepted by the University',
                'Rejected by the University',
                'Applied to another university',
                'Application forwarded to embassy',
                'Is on waiting list on Embassy',
                'Visa Approved',
                'Visa Rejected',
                'Lost',
            ])->default('Application created');
            $table->string('application_number')->nullable()->unique();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->timestamp('withdrawn_at')->nullable();
            $table->string('withdraw_reason')->nullable();
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('university_id')->references('id')->on('universities')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('set null');
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
