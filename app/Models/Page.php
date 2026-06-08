<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    protected $fillable = [
        'title', 'slug', 'content',
        'meta_title', 'meta_description',
        'template', 'featured_image',
        'is_published', 'is_menu_item', 'menu_order',
        'status', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_menu_item' => 'boolean',
        'menu_order' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->where('status', 'published');
    }

    public function scopeMenuItems($query)
    {
        return $query->where('is_menu_item', true)->orderBy('menu_order');
    }
}
