<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_statuses', function (Blueprint $table) {

            $table->bigIncrements('id');

            // One record per user
            $table->unsignedBigInteger('user_id')->unique();

            // Online status
            $table->boolean('is_online')->default(false);

            // Last seen timestamp
            $table->timestamp('last_seen')->nullable();

            $table->timestamps();

            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();

            // Index for sorting/filtering
            $table->index('last_seen', 'idx_last_seen');

            // Foreign key
            $table->foreign('user_id', 'fk_user_status_user')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_statuses');
    }
};
