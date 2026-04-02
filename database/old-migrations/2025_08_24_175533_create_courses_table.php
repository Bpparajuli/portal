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
        Schema::create('courses', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('university_id')->nullable()->index('university_id');
            $table->string('course_code', 255);
            $table->string('title');
            $table->enum('course_type', ['UG', 'PG', 'Diploma']);
            $table->text('course_link')->nullable();
            $table->text('description')->nullable();
            $table->text('academic_requirement')->nullable();
            $table->string('duration', 255)->nullable();
            $table->string('fee', 255)->nullable(); // keep as string if it includes text
            $table->string('intakes');
            $table->string('ielts_pte_other_languages')->nullable();
            $table->enum('moi_requirement', ['Yes', 'No']);
            $table->string('application_fee')->nullable();
            $table->string('scholarships')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();

            // Composite unique key: course_code + university_id
            $table->unique(['course_code', 'university_id'], 'course_uni_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
