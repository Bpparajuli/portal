<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'agent_id',
        'first_name',
        'last_name',
        'dob',
        'gender',
        'email',
        'phone_number',
        'address',
        'passport_number',
        'preferred_country',
        'nationality',
        'university_id',
        'course_id',
        'academic_background',
        'english_proficiency',
        'financial_proof',
        'student_status',
        'agent_student_id',
        'notes'
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
    public function university()
    {
        return $this->belongsTo(University::class, 'university_id');
    }
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
    public function applications()
    {
        return $this->hasMany(StudentApplication::class);
    }
}
