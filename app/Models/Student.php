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
        'notes',
    ];

    public const STATUSES = ['pending', 'in_progress', 'accepted', 'rejected'];
    public const GENDERS  = ['male', 'female', 'other'];

    // Relationships
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
        return $this->hasMany(StudentDocument::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    // Helper
    public static function getStatusLabel(string $status): string
    {
        return ucwords(str_replace('_', ' ', $status));
    }

    public static function getGenderLabel(string $gender): string
    {
        return ucwords($gender);
    }
}
