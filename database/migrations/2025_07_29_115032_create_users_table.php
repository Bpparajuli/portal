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
            $table->string('username')->unique();
            $table->string('contact')->nullable();
            $table->string('address')->nullable();
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_agent')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
