<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'paid_crm')) {
                $table->boolean('paid_crm')->default(false)->after('crm_notification_preferences');
            }
            if (!Schema::hasColumn('users', 'subscription_plan')) {
                $table->string('subscription_plan')->nullable()->after('paid_crm');
            }
            if (!Schema::hasColumn('users', 'subscription_starts_at')) {
                $table->timestamp('subscription_starts_at')->nullable()->after('subscription_plan');
            }
            if (!Schema::hasColumn('users', 'subscription_ends_at')) {
                $table->timestamp('subscription_ends_at')->nullable()->after('subscription_starts_at');
            }
            if (!Schema::hasColumn('users', 'max_staff')) {
                $table->integer('max_staff')->default(0)->after('subscription_ends_at');
            }
            if (!Schema::hasColumn('users', 'max_students')) {
                $table->integer('max_students')->default(0)->after('max_staff');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 50)->nullable()->after('contact');
            }
            if (!Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone', 100)->nullable()->default('UTC')->after('address');
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('crm_notification_preferences');
            }
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'paid_crm', 'subscription_plan', 'subscription_starts_at',
                'subscription_ends_at', 'max_staff', 'max_students',
                'phone', 'timezone', 'last_login_at', 'last_login_ip'
            ]);
        });
    }
};
