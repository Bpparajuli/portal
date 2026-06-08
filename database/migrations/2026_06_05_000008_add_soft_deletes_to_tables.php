<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_messages', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('activities', function (Blueprint $table) {
            if (!Schema::hasColumn('activities', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('documents', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('activities', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
