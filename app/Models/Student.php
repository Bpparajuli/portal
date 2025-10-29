<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Student extends Model
{
    use HasFactory, Notifiable;

    const GENDERS = ['Male', 'Female', 'Other'];
    const STATUS = ['created', 'viewed', 'applied to university', 'accepted', 'rejected', 'applied to another university', 'forwarded to embassy'];

    protected $fillable = [
        'agent_id',
        'first_name',
        'last_name',
        'students_photo',
        'dob',
        'gender',
        'email',
        'phone_number',
        'permanent_address',
        'temporary_address',
        'nationality',
        'passport_number',
        'passport_expiry',
        'marital_status',
        'qualification',
        'passed_year',
        'gap',
        'last_grades',
        'education_board',
        'preferred_country',
        'preferred_course',
        'university_id',
        'course_id',
        'student_status',
        'notes',
        'follow_up_date',
    ];
    protected $casts = [
        'dob' => 'date',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
    public function university()
    {
        return $this->belongsTo(University::class);
    }
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
    public function latestApplication()
    {
        // If your Laravel supports latestOfMany:
        return $this->hasOne(Application::class)->latestOfMany();

        // Fallback alternative:
        // return $this->hasOne(Application::class)->orderBy('created_at', 'desc');
    }
}
