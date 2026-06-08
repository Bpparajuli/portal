<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            if (!Schema::hasColumn('universities', 'address')) {
                $table->text('address')->nullable()->after('description');
            }
            if (!Schema::hasColumn('universities', 'phone')) {
                $table->string('phone', 50)->nullable()->after('contact_email');
            }
            if (!Schema::hasColumn('universities', 'map_url')) {
                $table->text('map_url')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('universities', 'featured_image')) {
                $table->string('featured_image')->nullable()->after('university_logo');
            }
            if (!Schema::hasColumn('universities', 'gallery')) {
                $table->json('gallery')->nullable()->after('featured_image');
            }
            if (!Schema::hasColumn('universities', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
            if (!Schema::hasColumn('universities', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('universities', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            $table->dropColumn([
                'address', 'phone', 'map_url', 'featured_image',
                'gallery', 'is_active', 'is_featured', 'deleted_at'
            ]);
        });
    }
};
