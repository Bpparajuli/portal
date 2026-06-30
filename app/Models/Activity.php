<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Activity extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['user_id', 'type', 'description', 'notifiable_id', 'link'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getLinkAttribute()
    {
        if ($this->attributes['link'] ?? false) {
            return $this->attributes['link'];
        }

        if (isset($this->data['link'])) {
            return $this->data['link'];
        }

        return null;
    }

    public function getViewUrlAttribute()
    {
        if ($this->link) {
            return $this->link;
        }

        $id = $this->notifiable_id;
        if (!$id) return null;

        return match ($this->type) {
            'student_added', 'student_updated', 'student_deleted', 'stage_changed' => $id ? route('crm.student.show', $id) : null,
            'task_completed', 'task_cancelled', 'task_created', 'crm_task_assigned' => $this->student ? route('crm.student.show', $this->student) : null,
            'document_uploaded', 'document_deleted' => $this->student ? route('crm.student.show', $this->student) : null,
            'application_submitted', 'application_status_updated', 'application_withdrawn', 'application_message_added' => $this->application ? route('admin.applications.show', $this->application) : null,
            'revenue_added', 'revenue_updated', 'revenue_deleted' => $this->student ? route('crm.student.show', $this->student) : null,
            'user_registered', 'user_approved', 'staff_created', 'profile_updated' => route('admin.users.show', $id),
            default => null,
        };
    }
}
