<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use App\Models\Traits\HasRoles;
use App\Models\Traits\HasOnlineStatus;
use App\Models\Traits\HasChatMessages;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasRoles, HasOnlineStatus, HasChatMessages;

    protected $fillable = [
        'business_name',
        'owner_name',
        'name',
        'contact',
        'phone',
        'address',
        'timezone',
        'email',
        'password',
        'agreement_file',
        'agreement_status',
        'agreement_uploaded_at',
        'business_logo',
        'registration',
        'pan',
        'role',
        'slug',

        'active',
        'crm_notification_preferences',
        'paid_crm',
        'subscription_plan',
        'subscription_starts_at',
        'subscription_ends_at',
        'max_staff',
        'max_students',
        'parent_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'active'   => 'boolean',
        'paid_crm' => 'boolean',
        'agreement_uploaded_at' => 'datetime',
        'crm_notification_preferences' => 'array',
        'subscription_starts_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'max_staff' => 'integer',
        'max_students' => 'integer',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            if (!$user->slug) {
                $user->slug = self::makeSlug(
                    $user->role === 'staff' ? $user->name : ($user->business_name ?: $user->name)
                );
            }
        });

        static::updating(function ($user) {
            if (!$user->slug) {
                $user->slug = self::makeSlug(
                    $user->role === 'staff' ? $user->name : ($user->business_name ?: $user->name)
                );
            }
        });
    }

    public static function makeSlug($name)
    {
        $slug = Str::slug($name, '-');

        if (!$slug) {
            $slug = 'user-' . uniqid();
        }

        $original = $slug;
        $count = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'agent_id');
    }

    public function documents()
    {
        return $this->hasManyThrough(Document::class, Student::class, 'agent_id', 'student_id', 'id', 'id');
    }

    public function applications()
    {
        return $this->hasManyThrough(Application::class, Student::class, 'agent_id', 'student_id', 'id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function staffMembers()
    {
        return $this->hasMany(User::class, 'parent_id')->where('role', 'staff');
    }

    public function assignedActivities()
    {
        return $this->hasMany(CrmTasks::class, 'assigned_to');
    }

    public function createdActivities()
    {
        return $this->hasMany(CrmTasks::class, 'created_by');
    }

    public function pendingActivities()
    {
        return $this->hasMany(CrmTasks::class, 'assigned_to')->where('status', 'pending');
    }

    public function todayActivities()
    {
        return $this->hasMany(CrmTasks::class, 'assigned_to')
            ->whereDate('scheduled_for', today())
            ->orderBy('priority_time_slot');
    }

    public function createdNotes()
    {
        return $this->hasMany(StudentNote::class, 'created_by');
    }

    public function stageChanges()
    {
        return $this->hasMany(StudentStageHistory::class, 'changed_by');
    }

    public function getNotificationPreferencesAttribute()
    {
        return $this->crm_notification_preferences ?? [
            'task_assigned' => true,
            'task_due_today' => true,
            'task_upcoming' => true,
            'task_overdue' => true,
            'email_notifications' => false,
        ];
    }

    public function wantsNotification($type)
    {
        $preferences = $this->notification_preferences;
        return $preferences[$type] ?? true;
    }

    public static function notifyAdmins($notification)
    {
        $admins = self::whereIn('role', ['superadmin', 'admin'])->get();
        Notification::send($admins, $notification);
    }

    public static function notifyAgent($agentId, $notification)
    {
        $agent = self::where('id', $agentId)
            ->where('role', 'agent')
            ->where('active', 1)
            ->first();

        if ($agent) {
            $agent->notify($notification);
        }
    }

    public function formatNotification($notification)
    {
        $data = json_decode(json_encode($notification->data), true);
        $type = $data['type'] ?? 'unknown';
        $messageText = $data['message'] ?? 'New Notification';

        switch ($type) {
            case 'student_added':
                $messageText = "New student added: "
                    . ($data['student']['name'] ?? 'Unknown Student')
                    . " by " . ($data['added_by']['name'] ?? 'Unknown Agent');
                break;
            case 'student_deleted':
                $messageText = "Student deleted: "
                    . ($data['student_name'] ?? 'Unknown Student')
                    . " by " . ($data['deleted_by']['name'] ?? 'Unknown User');
                break;
            case 'student_status':
                $messageText = "Status of student "
                    . ($data['student']['name'] ?? 'Unknown Student')
                    . " updated to "
                    . ($data['student']['status'] ?? 'Unknown Status')
                    . " by " . ($data['updated_by']['name'] ?? 'Unknown User');
                break;
            case 'application_submitted':
                $messageText = "Application submitted for "
                    . ($data['student']['name'] ?? 'Unknown Student')
                    . " to " . ($data['university']['name'] ?? 'Unknown University')
                    . " by " . ($data['submitted_by']['name'] ?? 'Unknown Agent');
                break;
            case 'application_status_updated':
                $messageText = "Application status updated for "
                    . ($data['student']['name'] ?? 'Unknown Student')
                    . " to " . ($data['application']['status'] ?? 'Unknown Status')
                    . " by " . ($data['updated_by']['name'] ?? 'Unknown User');
                break;
            case 'application_message_added':
                $messageText = "New message for "
                    . ($data['student']['name'] ?? 'Unknown Student')
                    . " by " . ($data['added_by']['name'] ?? 'Unknown User');
                break;
            case 'application_withdrawn':
                $messageText = "Application withdrawn for "
                    . ($data['student']['name'] ?? 'Unknown Student')
                    . " (" . ($data['application']['number'] ?? 'N/A') . ")";
                break;
            case 'document_uploaded':
                $messageText = ucfirst($data['document_type'] ?? 'Document')
                    . " uploaded for " . ($data['student']['name'] ?? 'Unknown Student')
                    . " by " . ($data['uploaded_by']['name'] ?? 'Unknown User');
                break;
            case 'document_deleted':
                $messageText = ucfirst($data['document_type'] ?? 'Document')
                    . " deleted for " . ($data['student']['name'] ?? 'Unknown Student')
                    . " by " . ($data['deleted_by']['name'] ?? 'Unknown User');
                break;
            case 'user_registered':
                $messageText = "New user registered: "
                    . ($data['user']['name'] ?? 'Unknown User');
                break;
        }

        return $messageText;
    }

    public function formatCrmNotification($notification)
    {
        $data = $notification->data;
        $subtype = $data['subtype'] ?? 'unknown';

        $icons = [
            'assigned' => 'Assigned',
            'due_today' => 'Due Today',
            'upcoming' => 'Upcoming',
            'overdue' => 'Overdue',
        ];

        $icon = $icons[$subtype] ?? 'Notification';
        $message = $data['message'] ?? 'Task notification';

        if (!empty($data['student_name'])) {
            $message .= " for student: {$data['student_name']}";
        }

        return [
            'icon' => $icon,
            'message' => $message,
            'link' => $data['link'] ?? '#',
            'task_title' => $data['task_title'] ?? '',
            'student_name' => $data['student_name'] ?? '',
        ];
    }

    public function scopeUniversities($query)
    {
        return $query->where('role', 'university');
    }

    public function isPaidCrm(): bool
    {
        return $this->paid_crm && (!$this->subscription_ends_at || $this->subscription_ends_at->isFuture());
    }

    public function hasReachedStudentLimit(): bool
    {
        if ($this->max_students <= 0) {
            return false;
        }
        return $this->students()->count() >= $this->max_students;
    }

    public function hasReachedStaffLimit(): bool
    {
        if ($this->max_staff <= 0) {
            return false;
        }
        return $this->staffMembers()->count() >= $this->max_staff;
    }
}
