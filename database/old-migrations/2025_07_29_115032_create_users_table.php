<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('business_name')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('business_logo')->nullable();
            $table->string('registration')->nullable();
            $table->string('pan')->nullable();
            $table->string('name')->unique();
            $table->string('contact')->nullable();
            $table->string('address')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('agreement_file')->nullable();
            $table->enum('agreement_status', ['not_uploaded', 'uploaded', 'verified'])->default('not_uploaded');
            $table->string('slug')->unique();
            $table->enum('role', ['superadmin', 'admin', 'agent', 'staff', 'university', 'student'])->default('agent');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
