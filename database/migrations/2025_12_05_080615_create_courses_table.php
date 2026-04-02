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
            $table->bigIncrements('id');
            $table->integer('university_id')->nullable()->index('university_id');
            $table->string('course_code');
            $table->string('title');
            $table->text('course_link')->nullable();
            $table->enum('course_type', ['UG', 'PG', 'DIPLOMA']);
            $table->text('academic_requirement')->nullable();
            $table->text('description')->nullable();
            $table->string('duration')->nullable();
            $table->string('fee')->nullable();
            $table->string('intakes');
            $table->text('ielts_pte_other_languages')->nullable();
            $table->enum('moi_requirement', ['Yes', 'No']);
            $table->string('application_fee')->nullable();
            $table->text('scholarships')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
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
