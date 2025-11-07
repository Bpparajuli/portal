<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'university_id',
        'course_code',
        'title',
        'course_type',
        'description',
        'duration',
        'fee',
        'intakes',
        'ielts_pte_other_languages',
        'moi_requirement',
        'application_fee',
        'scholarships',
    ];

    public function university()
    {
        return $this->belongsTo(University::class);
    }
}
