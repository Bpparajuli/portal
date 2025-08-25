<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $table = 'students';
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
    /* A student can have many applications to different universities and courses.
     */
    public function university()
    {
        return $this->belongsTo(University::class);
    }

    /**
     * A student can have many documents.
     */
    public function documents()
    {
        return $this->hasMany(StudentDocument::class);
    }
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
    public function applications()
    {
        return $this->hasMany(Application::class);
    }
    public function chats()
    {
        return $this->hasMany(Chat::class);
    }
}
