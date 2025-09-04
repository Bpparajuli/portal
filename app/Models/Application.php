<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'student_id',
        'university_id',
        'course_id',
        'agent_id',
        'application_status',
        'remarks'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
    protected static function booted()
    {
        static::created(function ($application) {
            $application_number = $application->agent_id . '-'
                . $application->student_id . '-'
                . $application->university_id . '-'
                . ($application->course_id ?? 0) . '-'
                . $application->id;

            $application->application_number = $application_number;
            $application->save();
        });
    }
}
