<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'image', 'location', 'content', 'rating', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rating' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getImageUrlAttribute(): string
    {
        if (!$this->image) return '';

        $path = $this->image;
        // Strip legacy 'public/' prefix if present
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }

        return asset('storage/' . $path);
    }
}
