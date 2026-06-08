<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('applications')) {
            return;
        }

        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'application_status_id')) {
                $table->foreignId('application_status_id')->nullable()->constrained('application_statuses')->nullOnDelete()->after('course_id');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('applications') && Schema::hasColumn('applications', 'application_status_id')) {
            Schema::table('applications', function (Blueprint $table) {
                $table->dropForeign(['application_status_id']);
                $table->dropColumn('application_status_id');
            });
        }
    }
};
