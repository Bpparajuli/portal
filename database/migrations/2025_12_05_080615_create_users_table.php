<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('business_name')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('name')->unique();
            $table->string('business_logo')->nullable();
            $table->string('registration')->nullable();
            $table->string('pan')->nullable();
            $table->string('contact')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('address')->nullable();
            $table->string('timezone', 100)->nullable()->default('UTC');
            $table->string('email')->unique();
            $table->enum('role', ['superadmin', 'admin', 'agent', 'staff', 'university', 'student'])->default('agent');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('password');
            $table->string('agreement_file')->nullable();
            $table->enum('agreement_status', ['not_uploaded', 'uploaded', 'verified'])->default('not_uploaded');
            $table->timestamp('agreement_uploaded_at')->nullable();
            $table->string('slug', 191)->nullable()->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->json('crm_notification_preferences')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->boolean('paid_crm')->default(false);
            $table->string('subscription_plan')->nullable();
            $table->timestamp('subscription_starts_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->integer('max_staff')->default(0);
            $table->integer('max_students')->default(0);

            $table->foreign('parent_id', 'fk_users_parent')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
