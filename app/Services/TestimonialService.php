<?php
namespace App\Services;

use App\Models\Testimonial;

class TestimonialService
{
    /**
     * Get all testimonials, ordered by sort_order.
     */
    public function getAll(): \Illuminate\Support\Collection
    {
        return Testimonial::orderBy('sort_order')->get();
    }

    /**
     * Get only active testimonials (for frontend display).
     */
    public function getActive(): \Illuminate\Support\Collection
    {
        return Testimonial::where('is_active', true)->orderBy('sort_order')->get();
    }
}
