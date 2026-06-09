<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'student_id',
        'university_id',
        'course_id',
        'agent_id',
        'application_status_id',
        'application_number',
        'sop_file',
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
        return $this->status?->bg_color ?? 'bg-secondary';
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'student_id', 'student_id')
            ->where('document_type', '!=', 'SOP');
    }

    public function sopDocument()
    {
        return $this->hasOne(Document::class, 'student_id', 'student_id')
            ->where('document_type', 'SOP');
    }

    public function messages()
    {
        return $this->hasMany(ApplicationMessage::class, 'application_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(ApplicationStatusHistory::class);
    }

    public function scopeAccessible($query)
    {
        $user = auth()->user();
        if (!$user) return $query->whereRaw('1 = 0');

        if ($user->is_admin) return $query;

        if ($user->is_staff) {
            if ($user->is_admin_staff) return $query;
            if ($user->is_agent_staff) {
                return $query->whereIn('agent_id', [$user->id, $user->parent_id]);
            }
            return $query->where('agent_id', $user->id);
        }

        if ($user->is_agent) {
            $staffIds = User::where('parent_id', $user->id)->where('role', 'staff')->pluck('id')->toArray();
            return $query->whereIn('agent_id', array_merge([$user->id], $staffIds));
        }

        return $query->whereRaw('1 = 0');
    }
}
