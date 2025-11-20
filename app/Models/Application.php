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
        'application_status',
        'application_number',
        'sop_file', // SOP file path
    ];

    // All possible application statuses
    public const STATUSES = [
        'Application started',
        'Application viewed by Admin',
        'Applied to University',
        'Need to give the test',
        'Accepted by the University',
        'Rejected by the University',
        'Applied to another university',
        'Application forwarded to embassy',
        'Is on waiting list on Embassy',
        'Visa Approved',
        'Visa Rejected',
        'Lost',
        'Withdrawn', // optional
    ];

    // Status colors (for badges or display)
    public const STATUS_COLORS = [
        'Application started'              => 'bg-info text-dark',
        'Application viewed by Admin'      => 'bg-primary text-white',
        'Applied to University'            => 'bg-warning text-dark',
        'Need to give the test'            => 'bg-secondary text-white',
        'Accepted by the University'       => 'bg-success text-white',
        'Rejected by the University'       => 'bg-danger text-white',
        'Applied to another university'    => 'bg-warning text-dark',
        'Application forwarded to embassy' => 'bg-primary text-white',
        'Is on waiting list on Embassy'    => 'bg-info text-dark',
        'Visa Approved'                     => 'bg-success text-white',
        'Visa Rejected'                     => 'bg-danger text-white',
        'Lost'                              => 'bg-dark text-white',
        'Withdrawn'                         => 'bg-light text-muted',
        'No Application'                    => 'bg-light text-muted',
    ];

    /** Relationships */

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
     * All documents except SOP
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'student_id', 'student_id')
            ->where('document_type', '!=', 'SOP');
    }

    /**
     * SOP document if stored in documents table
     */
    public function sopDocument()
    {
        return $this->hasOne(Document::class, 'student_id', 'student_id')
            ->where('document_type', 'SOP');
    }

    /**
     * Application messages
     */
    public function messages()
    {
        return $this->hasMany(ApplicationMessage::class, 'application_id');
    }

    /**
     * Get CSS class for status (for badges)
     */
    public function getStatusClassAttribute(): string
    {
        return self::STATUS_COLORS[$this->application_status] ?? 'bg-light text-dark';
    }
}
