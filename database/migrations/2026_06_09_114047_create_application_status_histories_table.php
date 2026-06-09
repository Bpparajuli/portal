<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_status_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('from_status_id')->nullable();
            $table->unsignedBigInteger('to_status_id');
            $table->unsignedBigInteger('changed_by');
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index('application_id');
            $table->index(['application_id', 'created_at']);
            $table->index('from_status_id');
            $table->index('to_status_id');
            $table->index('changed_by');

            $table->foreign('application_id')
                ->references('id')->on('applications')
                ->onDelete('cascade');

            $table->foreign('from_status_id')
                ->references('id')->on('application_statuses')
                ->onDelete('set null');

            $table->foreign('to_status_id')
                ->references('id')->on('application_statuses')
                ->onDelete('cascade');

            $table->foreign('changed_by')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_status_histories');
    }
};
