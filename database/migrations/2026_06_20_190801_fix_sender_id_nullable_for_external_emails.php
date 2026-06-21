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
        Schema::table('emails', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->unsignedBigInteger('sender_id')->nullable()->change();
            $table->foreign('sender_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->unsignedBigInteger('sender_id')->nullable(false)->change();
            $table->foreign('sender_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
