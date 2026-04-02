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
        Schema::create('universities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('university_logo')->nullable();
            $table->string('short_name', 50)->nullable();
            $table->string('country');
            $table->string('city')->nullable();
            $table->string('website')->nullable();
            $table->string('contact_email')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['short_name', 'city'], 'unique_short_name_city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('universities');
    }
};
