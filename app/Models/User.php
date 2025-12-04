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
}
