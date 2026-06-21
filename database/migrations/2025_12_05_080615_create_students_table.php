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
            $table->string('source', 50)->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('students_photo')->nullable();
            $table->date('dob')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phone_number', 50)->nullable();
            $table->string('phone_last_10', 10)->nullable()->index('idx_phone_last_10');
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
            $table->unsignedBigInteger('current_stage_id')->nullable();
            $table->integer('rating')->nullable();
            $table->json('tags')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('pinned')->default(false);
            $table->decimal('expected_revenue', 10, 2)->nullable();
            $table->decimal('received_revenue', 12, 2)->default(0);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->softDeletes();

            $table->foreign('agent_id', 'fk_agent')->references('id')->on('users')->nullOnDelete();
            $table->foreign('current_stage_id', 'fk_current_stage')->references('id')->on('student_stages')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
