<?php
namespace App\Services;

use App\Models\Page;

class PageService
{
    /**
     * Get all dynamic pages (not system pages).
     */
    public function getAllDynamic(): \Illuminate\Support\Collection
    {
        return Page::where('is_system', false)->orderBy('title')->get();
    }

    /**
     * Get a page by its slug (for frontend display).
     */
    public function getBySlug(string $slug): ?Page
    {
        return Page::where('slug', $slug)->where('is_active', true)->first();
    }
}
