<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('university_id')->nullable()->index('university_id');
            $table->string('course_code');
            $table->string('title');
            $table->text('course_link')->nullable();
            $table->string('course_type', 50)->nullable();
            $table->text('academic_requirement')->nullable();
            $table->text('description')->nullable();
            $table->string('duration')->nullable();
            $table->string('fee')->nullable();
            $table->string('intakes');
            $table->text('ielts_pte_other_languages')->nullable();
            $table->text('moi_acceptance')->nullable();
            $table->string('application_fee')->nullable();
            $table->text('scholarships')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->useCurrent();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
