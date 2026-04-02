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

            $table->unsignedBigInteger('agent_id')->nullable()->index('fk_agent');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('students_photo')->nullable();
            $table->date('dob')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phone_number', 50)->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('temporary_address')->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('passport_number', 100)->nullable();
            $table->date('passport_expiry')->nullable();
            $table->enum('marital_status', ['Single', 'Married', 'Other'])->nullable();
            $table->string('qualification')->nullable();
            $table->year('passed_year')->nullable();
            $table->integer('gap')->nullable();
            $table->string('last_grades', 50)->nullable();
            $table->string('education_board', 100)->nullable();
            $table->string('preferred_country', 100)->nullable();
            $table->string('preferred_city', 100)->nullable();
            $table->string('preferred_course')->nullable();
            $table->string('preferred_university')->nullable();
            $table->enum('student_status', [
                'created',
                'counselling',
                'important_followup_counselling',
                'initial_document_review',
                'limbo',
                'agents',
                'application_offer_letters',
                'visa_document_preparation',
                'visa_lodgment_process',
                'visa_success',
                'visa_failure',
                'old_students_review',
                'hold_student',
                'lost_unwanted'

            ])->default('created');
            $table->text('notes')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->timestamps();

            // 🔗 Foreign keys
            $table->foreign('agent_id', 'fk_agent')
                ->references('id')->on('users')
                ->onUpdate('no action')
                ->onDelete('set null');
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
