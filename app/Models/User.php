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
        'business_logo',
        'registration',
        'pan',
        'role',
        'slug', // store slug permanently
        'is_admin',
        'is_agent',
        'active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_admin' => 'boolean',
        'is_agent' => 'boolean',
        'active'   => 'boolean',
    ];

    /**
     * Use slug for route model binding
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Relationships
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'agent_id');
    }

    public function documents()
    {
        return $this->hasManyThrough(
            Document::class,
            Student::class,
            'agent_id',    // Foreign key on students table
            'student_id',  // Foreign key on documents table
            'id',          // Local key on users table
            'id'           // Local key on students table
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
     * Enhanced online status with formatted last seen
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
            ];
        }

        $isOnline = (bool) $this->status->is_online;
        $lastSeen = $this->status->last_seen;

        if (!$lastSeen) {
            return [
                'is_online' => $isOnline,
                'last_seen' => 'Never',
                'last_seen_full' => null,
                'last_seen_human' => 'Never',
                'last_seen_with_day' => 'Never',
            ];
        }

        $carbonLastSeen = Carbon::parse($lastSeen);
        $now = Carbon::now();

        // Check if online (within last 2 minutes)
        if ($isOnline && $carbonLastSeen->diffInMinutes($now) <= 2) {
            return [
                'is_online' => true,
                'last_seen' => 'Online',
                'last_seen_full' => $carbonLastSeen->format('l, F j, Y g:i A'),
                'last_seen_human' => 'Online now',
                'last_seen_with_day' => 'Online',
            ];
        }

        // Format for different time periods
        $diffInMinutes = $carbonLastSeen->diffInMinutes($now);
        $diffInHours = $carbonLastSeen->diffInHours($now);
        $diffInDays = $carbonLastSeen->diffInDays($now);

        if ($diffInMinutes < 60) {
            $lastSeenText = $diffInMinutes . ' minute' . ($diffInMinutes != 1 ? 's' : '') . ' ago';
            $lastSeenWithDay = $lastSeenText;
        } elseif ($diffInHours < 24) {
            $lastSeenText = $diffInHours . ' hour' . ($diffInHours != 1 ? 's' : '') . ' ago';
            $lastSeenWithDay = $lastSeenText;
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
        ];
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
     * Update user's online status
     */
    public function updateOnlineStatus()
    {
        $status = $this->status()->firstOrCreate([], [
            'is_online' => true,
            'last_seen' => Carbon::now()
        ]);

        $status->is_online = true;
        $status->last_seen = Carbon::now();
        $status->save();

        return $status;
    }

    /**
     * Set user as offline
     */
    public function setOffline()
    {
        if ($this->status) {
            $this->status->is_online = false;
            $this->status->save();
        }
    }
}
