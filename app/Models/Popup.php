<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Popup extends Model
{
    protected $fillable = [
        'title', 'description', 'image', 'button_text', 'button_link',
        'button_target', 'show_close', 'display_on', 'display_duration',
        'is_active', 'starts_at', 'ends_at', 'sort_order',
    ];

    protected $casts = [
        'display_on' => 'array',
        'show_close' => 'boolean',
        'is_active' => 'boolean',
        'display_duration' => 'integer',
        'sort_order' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderBy('sort_order');
    }
}
