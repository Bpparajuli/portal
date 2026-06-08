<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'university_id',
        'course_code',
        'title',
        'course_link',
        'course_type',
        'academic_requirement',
        'description',
        'duration',
        'fee',
        'intakes',
        'ielts_pte_other_languages',
        'moi_acceptance',
        'moi_requirement',
        'application_fee',
        'scholarships',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function getMoiRequirementAttribute()
    {
        return $this->moi_acceptance;
    }

    public function setMoiRequirementAttribute($value)
    {
        $this->moi_acceptance = $value;
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'course_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('course_type', $type);
    }
}
