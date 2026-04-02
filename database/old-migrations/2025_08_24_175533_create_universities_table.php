<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('universities', function (Blueprint $table) {
            $table->id(); // Corrected to match students table
            $table->string('name')->unique();
            $table->string('university_logo')->nullable();
            $table->string('short_name', 50)->nullable()->unique();
            $table->string('country');
            $table->string('city')->nullable();
            $table->string('website')->nullable();
            $table->string('contact_email')->nullable();
            $table->text('description')->nullable();

            $table->timestamps(); // Corrected timestamps
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('universities');
    }
};
