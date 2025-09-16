<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = ['student_id', 'university_id', 'course_id', 'agent_id', 'application_status', 'remarks', 'application_number'];

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

    public function sop()
    {
        return $this->hasOne(Document::class)->where('document_type', 'SOP');
    }
}
