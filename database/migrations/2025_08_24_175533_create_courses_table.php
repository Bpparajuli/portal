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
            $table->integer('id', true);
            $table->integer('university_id')->nullable()->index('university_id');
            $table->string('course_code', 50)->unique('course_code');
            $table->string('title');
            $table->enum('course_type', ['UG', 'PG', 'Diploma']);
            $table->text('description')->nullable();
            $table->string('duration', 100)->nullable();
            $table->decimal('fee', 10)->nullable();
            $table->string('intakes');
            $table->text('ielts_pte_other_langugaes')->nullable();
            $table->enum('moi_requirement', ['Yes', 'No']);
            $table->integer('application_fee');
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
