<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('type')->default('general')->comment('general, course, university, admission');
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('university_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_replied')->default(false);
            $table->timestamp('replied_at')->nullable();
            $table->unsignedBigInteger('replied_by')->nullable();
            $table->text('reply_message')->nullable();
            $table->string('status')->default('pending')->comment('pending, replied, closed');
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('courses')->nullOnDelete();
            $table->foreign('university_id')->references('id')->on('universities')->nullOnDelete();
            $table->foreign('replied_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['is_read', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
