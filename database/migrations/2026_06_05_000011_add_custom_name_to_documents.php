<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('documents', 'custom_name')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->string('custom_name')->nullable()->after('document_type');
                $table->string('description')->nullable()->after('custom_name');
                $table->timestamp('reviewed_at')->nullable()->after('status');
                $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['custom_name', 'description', 'reviewed_at', 'reviewed_by']);
        });
    }
};
