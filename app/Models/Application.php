<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = ['student_id', 'university_id', 'course_id', 'status'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function universities()
    {
        return $this->belongsTo(University::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
