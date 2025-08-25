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
            $table->bigIncrements('id');
            $table->unsignedBigInteger('agent_id')->index('agent_id');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->date('dob')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->default('male');
            $table->string('email');
            $table->string('phone_number', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('passport_number', 50)->nullable();
            $table->string('preferred_country', 100)->nullable();
            $table->string('nationality', 100)->nullable();
            $table->integer('university_id')->nullable()->index('university_id');
            $table->integer('course_id')->nullable()->index('course_id');
            $table->text('academic_background')->nullable();
            $table->string('english_proficiency', 100)->nullable();
            $table->string('financial_proof')->nullable();
            $table->enum('student_status', ['pending', 'in_progress', 'accepted', 'rejected'])->nullable()->default('pending');
            $table->string('agent_student_id', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
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
