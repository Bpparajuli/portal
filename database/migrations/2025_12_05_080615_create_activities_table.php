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
        Schema::create('activities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->index(); // index added
            $table->string('type');
            $table->text('description');
            $table->unsignedBigInteger('notifiable_id')->nullable();
            $table->string('link')->nullable();
            $table->timestamps();

            // Add foreign key
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onUpdate('no action')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
