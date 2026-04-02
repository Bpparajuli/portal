<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id')->nullable();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('student_photo')->nullable();
            $table->date('dob')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->string('email')->unique();
            $table->string('phone_number')->nullable();

            $table->string('permanent_address')->nullable();
            $table->string('temporary_address')->nullable();
            $table->string('nationality')->nullable();

            $table->string('passport_number')->nullable();
            $table->date('passport_expiry')->nullable();

            $table->enum('marital_status', ['Single', 'Married', 'Other'])->nullable();

            $table->string('qualification')->nullable();
            $table->year('passed_year')->nullable();
            $table->integer('gap')->nullable();
            $table->string('last_grades')->nullable();
            $table->string('education_board')->nullable();

            $table->string('preferred_country')->nullable();
            $table->string('preferred_course')->nullable();

            $table->unsignedBigInteger('university_id')->nullable();
            $table->unsignedBigInteger('course_id')->nullable();

            $table->enum('student_status', ['Pending', 'Active', 'Completed', 'Rejected'])->default('Pending');
            $table->text('notes')->nullable();

            $table->date('follow_up_date')->nullable();

            $table->timestamps();

            // Relationships
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('university_id')->references('id')->on('universities')->onDelete('set null');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
