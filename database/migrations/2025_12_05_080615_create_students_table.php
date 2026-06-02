<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('agent_id')->nullable()->index('fk_agent');
            $table->string('source')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('students_photo')->nullable();
            $table->date('dob')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone_number', 50)->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('temporary_address')->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('passport_number', 100)->nullable();
            $table->date('passport_expiry')->nullable();
            $table->enum('marital_status', ['Single', 'Married', 'Other'])->nullable();
            $table->string('applying_for')->nullable();
            $table->string('qualification')->nullable();
            $table->integer('passed_year')->nullable();
            $table->integer('gap')->nullable();
            $table->string('last_grades', 50)->nullable();
            $table->string('education_board', 100)->nullable();
            $table->string('preferred_country', 100)->nullable();
            $table->string('preferred_city', 100)->nullable();
            $table->string('preferred_course')->nullable();
            $table->string('preferred_university')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('current_stage_id')->default(1);
            $table->json('tags')->nullable();
            $table->boolean('pinned')->default(0);
            $table->integer('rating')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Optional: foreign key for current_stage_id
            $table->foreign('current_stage_id', 'fk_current_stage')
                ->references('id')->on('student_stages')
                ->onDelete('set null')
                ->onUpdate('cascade');

            // Optional: foreign key for agent_id

            $table->foreign('agent_id', 'fk_agent')
                ->references('id')->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
