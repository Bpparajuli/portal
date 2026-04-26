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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Business Info
            $table->string('business_name')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('business_logo')->nullable();
            $table->string('registration')->nullable();
            $table->string('pan')->nullable();

            // Basic Info
            $table->string('name')->unique();
            $table->string('email')->unique();
            $table->string('contact')->nullable();
            $table->string('address')->nullable();

            // Slug (for URLs)
            $table->string('slug', 191)->unique()->nullable();

            // Role system (MAIN CONTROL)
            $table->enum('role', [
                'superadmin',
                'admin',
                'agent',
                'staff',
                'university',
                'student'
            ])->default('agent');

            // Parent hierarchy (VERY IMPORTANT)
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Auth
            $table->string('password');

            // Agreement
            $table->string('agreement_file')->nullable();
            $table->enum('agreement_status', [
                'not_uploaded',
                'uploaded',
                'verified'
            ])->default('not_uploaded');
            $table->timestamp('agreement_uploaded_at')->nullable();

            // Status
            $table->boolean('active')->default(true);
            $table->boolean('is_admin')->default(false); //it was wowrking in old version but now it is not working because of role system so i am adding this field for backward compatibility
            $table->boolean('is_agent')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
