<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'students',
        'applications',
        'documents',
        'courses',
        'universities',
        'activities',
        'application_statuses',
        'chat_messages',
        'crm_tasks',
        'student_notes',
        'student_stages',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'deleted_by')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('deleted_by')->nullable()->index();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'deleted_by')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('deleted_by');
                });
            }
        }
    }
};
