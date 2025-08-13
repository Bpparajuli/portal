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
        'description',
        'duration',
        'fee',
        'intakes',
        'MOI Requirement',
    ];

    /**
     * Relationship: A course belongs to a university.
     */
    public function university()
    {
        return $this->belongsTo(University::class);
    }
}
