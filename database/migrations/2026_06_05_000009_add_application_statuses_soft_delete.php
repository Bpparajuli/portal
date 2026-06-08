<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('application_statuses')) {
            return;
        }

        Schema::table('application_statuses', function (Blueprint $table) {
            if (!Schema::hasColumn('application_statuses', 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn('application_statuses', 'icon')) {
                $table->string('icon')->nullable()->after('text_color');
            }
            if (!Schema::hasColumn('application_statuses', 'description')) {
                $table->text('description')->nullable()->after('icon');
            }
        });
    }

    public function down(): void
    {
        Schema::table('application_statuses', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['icon', 'description']);
        });
    }
};
