<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Content extends Model
{
    use SoftDeletes;

    protected $table = 'contents';

    protected $fillable = [
        'title',
        'slug',
        'type',
        'content',
        'excerpt',
        'featured_image',
        'gallery_images',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'category',
        'tags',
        'sort_order',
        'is_published',
        'published_at',
        'status',
        'author_id',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'tags' => 'array',
        'gallery_images' => 'array',
        'published_at' => 'datetime',
        'sort_order' => 'integer'
    ];

    protected static function booted()
    {
        static::creating(function ($content) {
            if (empty($content->slug)) {
                $content->slug = Str::slug($content->title);
            }
            if (empty($content->author_id)) {
                $content->author_id = Auth::user()?->id;
            }
        });
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Relationships (using your existing User model)
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Accessors
    public function getFormattedDateAttribute()
    {
        return $this->published_at ? $this->published_at->format('M d, Y') : null;
    }

    public function getReadingTimeAttribute()
    {
        $words = str_word_count(strip_tags($this->content ?? ''), 0);
        $minutes = ceil($words / 200);
        return $minutes . ' min read';
    }

    public function getExcerptAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }

        // Auto-generate excerpt from content
        $content = strip_tags($this->content ?? '');
        return Str::limit($content, 150);
    }
}
