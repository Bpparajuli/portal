<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Application extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'student_id',
        'university_id',
        'course_id',
        'agent_id',
        'application_status_id', // ✅ UPDATED
        'application_number',
        'sop_file',
    ];

    /**
     * Relationships
     */

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

    /**
     * Status (NEW SYSTEM)
     */
    public function status()
    {
        return $this->belongsTo(ApplicationStatus::class, 'application_status_id');
    }

    public function getStatusNameAttribute()
    {
        return $this->status?->name ?? 'N/A';
    }
    public function getStatusColorAttribute()
    {
        return $this->status?->color ?? 'bg-secondary';
    }
    /**
     * All documents except SOP
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'student_id', 'student_id')
            ->where('document_type', '!=', 'SOP');
    }

    /**
     * SOP document
     */
    public function sopDocument()
    {
        return $this->hasOne(Document::class, 'student_id', 'student_id')
            ->where('document_type', 'SOP');
    }

    /**
     * Messages
     */
    public function messages()
    {
        return $this->hasMany(ApplicationMessage::class, 'application_id');
    }
}
