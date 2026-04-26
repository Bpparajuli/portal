<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_stages', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->string('name');
            $table->string('slug')->unique();

            $table->string('color')->nullable();

            $table->integer('stage_order')->default(0);

            $table->boolean('is_active')->default(1);
            $table->boolean('is_won_stage')->default(0);
            $table->boolean('is_lost_stage')->default(0);

            $table->text('description')->nullable();
            $table->json('meta_data')->nullable();
            $table->json('allowed_next_stages')->nullable();
            $table->integer('max_days_in_stage')->nullable();

            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('stage_order');
            $table->index('is_active');
            $table->index(['is_won_stage', 'is_lost_stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_stages');
    }
};
