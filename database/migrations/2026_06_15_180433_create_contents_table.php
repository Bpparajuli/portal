<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type')->default('post')->comment('post, news, article, testimonial, faq');
            $table->longText('content')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('featured_image')->nullable();
            $table->json('gallery_images')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('category')->nullable();
            $table->json('tags')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->string('status')->default('draft')->comment('draft, published, archived');
            $table->unsignedBigInteger('author_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('author_id', 'fk_contents_author')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by', 'fk_contents_created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by', 'fk_contents_updated_by')->references('id')->on('users')->nullOnDelete();
            $table->index('type', 'idx_contents_type');
            $table->index('category', 'idx_contents_category');
            $table->index('is_published', 'idx_contents_is_published');
            $table->index('published_at', 'idx_contents_published_at');
            $table->index('status', 'idx_contents_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
