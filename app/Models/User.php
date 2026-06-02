<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use App\Models\Student;
use App\Models\Document;
use App\Models\Application;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'business_name',
        'owner_name',
        'name',
        'contact',
        'address',
        'email',
        'password',
        'agreement_file',
        'agreement_status',
        'agreement_uploaded_at',
        'business_logo',
        'registration',
        'pan',
        'role',
        'slug', // store slug permanently
        'is_admin',
        'is_agent',
        'active',
        'crm_notification_preferences'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_admin' => 'boolean',
        'is_agent' => 'boolean',
        'active'   => 'boolean',
        'agreement_uploaded_at' => 'datetime',
        'crm_notification_preferences' => 'array',

    ];

    /**
     * Use slug for route model binding
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Generate slug automatically
     */
    protected static function booted()
    {
        static::creating(function ($user) {
            if (!$user->slug) {
                $user->slug = self::makeSlug($user->business_name);
            }
        });

        static::updating(function ($user) {
            if (!$user->slug) {
                $user->slug = self::makeSlug($user->business_name);
            }
        });
    }

    /**
     * Create a slug from business name
     */
    public static function makeSlug($businessName)
    {
        $slug = Str::slug($businessName, '-');

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

    /**
     * Old IsAdmin,IsAgent Middleware helpers
     */
    public function getIsAdminAttribute($value)
    {
        return in_array($this->role, ['superadmin', 'admin']);
    }

    public function getIsAgentAttribute($value)
    {
        return $this->role === 'agent';
    }

    public function getIsStaffAttribute()
    {
        return $this->role === 'staff';
    }
    public function getIsStudentAttribute()
    {
        return $this->role === 'student';
    }

    public function getIsUniversityAttribute()
    {
        return $this->role === 'university';
    }

    // =========================================================================
    // Staff type helpers
    // =========================================================================

    /**
     * TRUE when this staff member belongs to an admin (or has no parent = also admin-level).
     * Admin-staff see ALL students.
     */
    public function getIsAdminStaffAttribute(): bool
    {
        if (! $this->is_staff) return false;
        if (! $this->parent_id) return true; // no parent => treat as admin-level

        $parent = $this->parent()->first();
        return $parent && $parent->is_admin;
    }

    /**
     * TRUE when this staff member belongs to an agent.
     * Agent-staff see own students + parent agent's students.
     */
    public function getIsAgentStaffAttribute(): bool
    {
        if (! $this->is_staff) return false;
        if (! $this->parent_id) return false;

        $parent = $this->parent()->first();
        return $parent && $parent->is_agent;
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    public function students()
    {
        return $this->hasMany(Student::class, 'agent_id');
    }

    public function documents()
    {
        return $this->hasManyThrough(
            Document::class,
            Student::class,
            'agent_id',
            'student_id',
            'id',
            'id'
        );
    }

    public function applications()
    {
        return $this->hasManyThrough(
            Application::class,
            Student::class,
            'agent_id',
            'student_id',
            'id',
            'id'
        );
    }
    /**
     * Parent-child relationships for staff and agents
     **/
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

    public function parentAgent()
    {
        return $this->belongsTo(User::class, 'parent_id')->where('role', 'agent');
    }


    /**
     * Format notifications
     */
    public function formatNotification($notification)
    {
        $data = json_decode(json_encode($notification->data), true);
        $type = $data['type'] ?? 'unknown';
        $messageText = $data['message'] ?? '🔔 New Notification';

        switch ($type) {
            case 'student_added':
                $messageText = "👤 New student added: "
                    . ($data['student']['name'] ?? 'Unknown Student')
                    . " by " . ($data['added_by']['name'] ?? 'Unknown Agent');
                break;
            case 'student_deleted':
                $messageText = "❌ Student deleted: "
                    . ($data['student_name'] ?? 'Unknown Student')
                    . " by " . ($data['deleted_by']['name'] ?? 'Unknown User');
                break;
            case 'student_status':
                $messageText = "📌 Status of student "
                    . ($data['student']['name'] ?? 'Unknown Student')
                    . " updated to "
                    . ($data['student']['status'] ?? 'Unknown Status')
                    . " by " . ($data['updated_by']['name'] ?? 'Unknown User');
                break;
            case 'application_submitted':
                $messageText = "📝 Application submitted for "
                    . ($data['student']['name'] ?? 'Unknown Student')
                    . " to " . ($data['university']['name'] ?? 'Unknown University')
                    . " by " . ($data['submitted_by']['name'] ?? 'Unknown Agent');
                break;
            case 'application_status_updated':
                $messageText = "📌 Application status updated for "
                    . ($data['student']['name'] ?? 'Unknown Student')
                    . " to " . ($data['application']['status'] ?? 'Unknown Status')
                    . " by " . ($data['updated_by']['name'] ?? 'Unknown User');
                break;
            case 'application_message_added':
                $messageText = "💬 New message for "
                    . ($data['student']['name'] ?? 'Unknown Student')
                    . " by " . ($data['added_by']['name'] ?? 'Unknown User');
                break;
            case 'application_withdrawn':
                $messageText = "⚠️ Application withdrawn for "
                    . ($data['student']['name'] ?? 'Unknown Student')
                    . " (" . ($data['application']['number'] ?? 'N/A') . ")";
                break;
            case 'document_uploaded':
                $messageText = "📤 " . ucfirst($data['document_type'] ?? 'Document')
                    . " uploaded for " . ($data['student']['name'] ?? 'Unknown Student')
                    . " by " . ($data['uploaded_by']['name'] ?? 'Unknown User');
                break;
            case 'document_deleted':
                $messageText = "🗑️ " . ucfirst($data['document_type'] ?? 'Document')
                    . " deleted for " . ($data['student']['name'] ?? 'Unknown Student')
                    . " by " . ($data['deleted_by']['name'] ?? 'Unknown User');
                break;
            case 'user_registered':
                $messageText = "🆕 New user registered: "
                    . ($data['user']['name'] ?? 'Unknown User');
                break;
        }

        return $messageText;
    }

    // =========================================================================
    // Format and All CRM NOtifications 
    // =========================================================================


    public function formatCrmNotification($notification)
    {
        $data = $notification->data;
        $subtype = $data['subtype'] ?? 'unknown';

        $icons = [
            'assigned' => '📋',
            'due_today' => '⚠️',
            'upcoming' => '🔔',
            'overdue' => '❌',
        ];

        $icon = $icons[$subtype] ?? '📌';
        $message = $data['message'] ?? 'Task notification';

        // Add student name if available
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

    /**
     * Get default notification preferences
     */
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

    /**
     * Check if user wants to receive a specific notification type
     */
    public function wantsNotification($type)
    {
        $preferences = $this->notification_preferences;
        return $preferences[$type] ?? true;
    }

    /**
     * Helper for query and listings 
     */

    public function scopeAdmins($query)
    {
        return $query->whereIn('role', ['superadmin', 'admin']);
    }

    public function scopeAgents($query)
    {
        return $query->where('role', 'agent');
    }

    public function scopeStaff($query)
    {
        return $query->where('role', 'staff');
    }

    public function scopeUniversities($query)
    {
        return $query->where('role', 'university');
    }




    /**
     * Notifications helpers
     */
    public static function notifyAdmins($notification)
    {
        $admins = self::where('is_admin', 1)->get();
        Notification::send($admins, $notification);
    }

    public static function notifyAgent($agentId, $notification)
    {
        $agent = self::where('id', $agentId)
            ->where('is_agent', 1)
            ->where('active', 1)
            ->first();

        if ($agent) {
            $agent->notify($notification);
        }
    }
    // ==============================
    // MESSAGE AND CHAT RELATIONS
    // ==============================

    // All messages involving this user (sent OR received)
    public function chatMessages()
    {
        return ChatMessage::where('sender_id', $this->id)
            ->orWhere('receiver_id', $this->id);
    }

    // Only unread messages for this user (messages sent to me, not read yet)
    public function unreadMessages()
    {
        return ChatMessage::where('receiver_id', $this->id)
            ->where('status', '!=', 'read');
    }

    // Fetch full conversation with a specific user (optional)
    public function conversationWith($otherUserId)
    {
        return ChatMessage::where(function ($q) use ($otherUserId) {
            $q->where('sender_id', $this->id)
                ->where('receiver_id', $otherUserId);
        })
            ->orWhere(function ($q) use ($otherUserId) {
                $q->where('sender_id', $otherUserId)
                    ->where('receiver_id', $this->id);
            })
            ->orderBy('created_at', 'asc');
    }

    // Fetch unread messages from a specific sender (optional)
    public function unreadFrom($senderId)
    {
        return ChatMessage::where('receiver_id', $this->id)
            ->where('sender_id', $senderId)
            ->where('status', '!=', 'read');
    }

    // ==============================
    // ONLINE STATUS RELATIONS
    // ==============================

    public function status()
    {
        return $this->hasOne(UserStatus::class, 'user_id');
    }


    /**
     * Enhanced online status with formatted last seen and last login
     */
    public function getOnlineStatusAttribute()
    {
        if (!$this->status) {
            return [
                'is_online' => false,
                'last_seen' => 'Never',
                'last_seen_full' => null,
                'last_seen_human' => 'Never',
                'last_seen_with_day' => 'Never',
                'last_login' => null,
                'last_login_ip' => null,
            ];
        }

        $isOnline = (bool) $this->status->is_online;
        $lastSeen = $this->status->last_seen;
        $lastLogin = $this->status->last_login_at;
        $lastLoginIp = $this->status->last_login_ip;

        if (!$lastSeen) {
            return [
                'is_online' => false,
                'last_seen' => 'Never',
                'last_seen_full' => null,
                'last_seen_human' => 'Never',
                'last_seen_with_day' => 'Never',
                'last_login' => $this->formatLastLogin($lastLogin),
                'last_login_ip' => $lastLoginIp,
            ];
        }

        // Convert to Nepal timezone
        $carbonLastSeen = Carbon::parse($lastSeen)->timezone(config('app.timezone'));
        $now = now();

        // ✅ Online check (within last 2 minutes)
        if ($isOnline && $carbonLastSeen->diffInMinutes($now) <= 2) {
            return [
                'is_online' => true,
                'last_seen' => 'Online',
                'last_seen_full' => $carbonLastSeen->format('l, F j, Y g:i A'),
                'last_seen_human' => 'Online now',
                'last_seen_with_day' => 'Online',
                'last_login' => $this->formatLastLogin($lastLogin),
                'last_login_ip' => $lastLoginIp,
            ];
        }

        // Time differences
        $diffInDays = $carbonLastSeen->diffInDays($now);

        // ✅ Smart formatting
        if ($diffInDays < 1) {
            // Recent → use human readable
            $lastSeenText = $carbonLastSeen->diffForHumans([
                'parts' => 1, // e.g. "2 hours 5 minutes ago"
            ]);

            $lastSeenWithDay = $carbonLastSeen->diffForHumans([
                'parts' => 1, // e.g. "2 hours ago"
            ]);
        } elseif ($diffInDays == 1) {
            $lastSeenText = 'Yesterday at ' . $carbonLastSeen->format('g:i A');
            $lastSeenWithDay = 'Yesterday';
        } elseif ($diffInDays < 7) {
            $lastSeenText = $carbonLastSeen->format('l') . ' at ' . $carbonLastSeen->format('g:i A');
            $lastSeenWithDay = $carbonLastSeen->format('l');
        } else {
            $lastSeenText = $carbonLastSeen->format('M j, Y') . ' at ' . $carbonLastSeen->format('g:i A');
            $lastSeenWithDay = $carbonLastSeen->format('M j, Y');
        }

        return [
            'is_online' => false,
            'last_seen' => $lastSeenText,
            'last_seen_full' => $carbonLastSeen->format('l, F j, Y g:i A'),
            'last_seen_human' => $lastSeenText,
            'last_seen_with_day' => $lastSeenWithDay,
            'last_login' => $this->formatLastLogin($lastLogin),
            'last_login_ip' => $lastLoginIp,
        ];
    }
    /**
     * Format last login timestamp
     */
    private function formatLastLogin($lastLogin)
    {
        if (!$lastLogin) {
            return null;
        }

        $carbonLastLogin = Carbon::parse($lastLogin);
        $now = now();
        $diffInDays = $carbonLastLogin->diffInDays($now);

        if ($diffInDays == 0) {
            return 'Today at ' . $carbonLastLogin->format('g:i A');
        } elseif ($diffInDays == 1) {
            return 'Yesterday at ' . $carbonLastLogin->format('g:i A');
        } elseif ($diffInDays < 7) {
            return $carbonLastLogin->format('l') . ' at ' . $carbonLastLogin->format('g:i A');
        } else {
            return $carbonLastLogin->format('M j, Y') . ' at ' . $carbonLastLogin->format('g:i A');
        }
    }

    /**
     * Get just the formatted last seen with day
     */
    public function getFormattedLastSeenAttribute()
    {
        $status = $this->online_status;

        if ($status['is_online']) {
            return '<span class="text-success">● Online</span>';
        }

        if (!$status['last_seen_full']) {
            return '<span class="text-muted">Never</span>';
        }

        return '<span class="text-muted" title="' . $status['last_seen_full'] . '">Last seen: ' . $status['last_seen_with_day'] . '</span>';
    }

    /**
     * Get formatted last login
     */
    public function getFormattedLastLoginAttribute()
    {
        $status = $this->online_status;

        if (!$status['last_login']) {
            return '<span class="text-muted">Never logged in</span>';
        }

        return '<span class="text-muted">Last login: ' . $status['last_login'] . '</span>';
    }

    /**
     * Update user's online status
     */
    public function updateOnlineStatus()
    {
        $status = $this->status()->firstOrCreate([], [
            'is_online' => true,
            'last_seen' => now()
        ]);

        $status->is_online = true;
        $status->last_seen = now();
        $status->save();

        return $status;
    }

    /**
     * Set user as offline
     */
    public function setOffline()
    {
        if ($this->status) {
            $this->status->update([
                'is_online' => false,
                'last_seen' => now()
            ]);
        }
    }

    /**
     * Update login info (call this after user logs in)
     */
    public function updateLoginInfo($request)
    {
        $status = $this->status()->firstOrCreate([], [
            'is_online' => true,
            'last_seen' => now(),
            'last_login_at' => now(),
            'last_login_ip' => $request->ip()
        ]);

        $status->update([
            'is_online' => true,
            'last_seen' => now(),
            'last_login_at' => now(),
            'last_login_ip' => $request->ip()
        ]);

        return $status;
    }
    // In app/Models/User.php - Add these methods

    // ========================================================================
    // CRM Relationships (Add these)
    // ========================================================================
    // =========================================================================
    // CRM Relationships
    // =========================================================================

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
        return $this->hasMany(CrmTasks::class, 'assigned_to')
            ->where('status', 'pending');
    }

    public function todayActivities()
    {
        return $this->hasMany(CrmTasks::class, 'assigned_to')
            ->whereDate('scheduled_at', today())
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

      // =========================================================================
    // Accessible students — used outside Eloquent scopes
    // =========================================================================

    /**
     * Returns a Collection of students this user is allowed to see.
     *
     * Rules:
     *  Admin            → all students
     *  Admin's staff    → all students
     *  Agent            → own students + all staff-under-agent students
     *  Agent's staff    → own students (agent_id = self) + parent agent's students (agent_id = parent_id)
     */
    public function getAccessibleStudents()
    {
        // Admin
        if ($this->is_admin) {
            return Student::all();
        }

        // Admin's staff → all students
        if ($this->is_staff && $this->is_admin_staff) {
            return Student::all();
        }

        // Agent → own + all staff-under-them
        if ($this->is_agent) {
            $staffIds        = User::where('parent_id', $this->id)->where('role', 'staff')->pluck('id')->toArray();
            $allowedAgentIds = array_merge([$this->id], $staffIds);
            return Student::whereIn('agent_id', $allowedAgentIds)->get();
        }

        // Agent's staff → own students + parent agent's students
        if ($this->is_staff && $this->is_agent_staff) {
            return Student::whereIn('agent_id', [$this->id, $this->parent_id])->get();
        }

        return collect();
    }
}
