<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('universities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('university_logo')->nullable();
            $table->string('featured_image')->nullable();
            $table->json('gallery')->nullable();
            $table->string('short_name', 50)->nullable();
            $table->string('country');
            $table->string('city')->nullable();
            $table->string('website')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('map_url')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->text('address')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['short_name', 'city'], 'unique_short_name_city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('universities');
    }
};
