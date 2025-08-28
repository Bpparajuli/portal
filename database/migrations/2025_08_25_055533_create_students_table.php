<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id(); // Correct way to define the primary key

            $table->unsignedBigInteger('agent_id');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->date('dob')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->default('male');
            $table->string('email')->unique(); // 'email' should usually be unique
            $table->string('phone_number', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('passport_number', 50)->nullable();
            $table->string('preferred_country', 100)->nullable();
            $table->string('nationality', 100)->nullable();
            $table->unsignedBigInteger('university_id')->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->text('academic_background')->nullable();
            $table->string('english_proficiency', 100)->nullable();
            $table->string('financial_proof')->nullable();
            $table->enum('student_status', ['pending', 'in_progress', 'accepted', 'rejected'])->default('pending');
            $table->string('agent_student_id', 50)->nullable();
            $table->text('notes')->nullable();

            // Using $table->timestamps() for correct created_at and updated_at columns
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('university_id')->references('id')->on('universities')->onDelete('set null');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
