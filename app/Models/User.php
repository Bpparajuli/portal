<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Notification;

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
        'business_logo',
        'is_admin',
        'is_agent',
        'active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        // ✅ Cast to boolean
        'is_admin' => 'boolean',
        'is_agent' => 'boolean',
        'active'   => 'boolean',
    ];
    public function students()
    {
        return $this->hasMany(Student::class, 'agent_id');
    }

    public function applications()
    {
        return $this->hasManyThrough(
            Application::class,   // Target model
            Student::class,       // Intermediate model
            'agent_id',           // Foreign key on students table
            'student_id',         // Foreign key on applications table
            'id',                 // Local key on users table
            'id'                  // Local key on students table
        );
    }
    public function formatNotification($notification)
    {
        // Ensure $data is always an associative array
        $data = json_decode(json_encode($notification->data), true);
        $type = $data['type'] ?? 'unknown';

        switch ($type) {
            // 🧍 Student added
            case 'student_added':
                $messageText = "👤 New student added: "
                    . ($data['student']['name'] ?? 'Unknown Student')
                    . " by "
                    . ($data['added_by']['name'] ?? 'Unknown Agent');
                break;

            // ❌ Student deleted
            case 'student_deleted':
                $messageText = "❌ Student deleted: "
                    . ($data['student_name'] ?? 'Unknown Student')
                    . " by "
                    . ($data['deleted_by']['name'] ?? 'Unknown User');
                break;

            // 📌 Student status updated
            case 'student_status':
                $messageText = "📌 Status of student "
                    . ($data['student']['name'] ?? 'Unknown Student')
                    . " updated to "
                    . ($data['student']['status'] ?? 'Unknown Status')
                    . " by "
                    . ($data['updated_by']['name'] ?? 'Unknown User');
                break;

            // 📝 Application submitted
            case 'application_submitted':
                $messageText = "📝 Application submitted for "
                    . ($data['student']['name'] ?? 'Unknown Student')
                    . " to "
                    . ($data['university']['name'] ?? 'Unknown University')
                    . " by "
                    . ($data['submitted_by']['name'] ?? 'Unknown Agent');
                break;

            // 📌 Application status updated
            case 'application_status_updated':
                $messageText = "📌 Application status updated for "
                    . ($data['student']['name'] ?? 'Unknown Student')
                    . " to "
                    . ($data['application']['status'] ?? 'Unknown Status')
                    . " by "
                    . ($data['updated_by']['name'] ?? 'Unknown User');
                break;

            // 💬 New message on application
            case 'application_message_added':
                $messageText = "💬 New message for "
                    . ($data['student']['name'] ?? 'Unknown Student')
                    . " by "
                    . ($data['added_by']['name'] ?? 'Unknown User');
                break;

            // ⚠️ Application withdrawn
            case 'application_withdrawn':
                $messageText = "⚠️ Application withdrawn for "
                    . ($data['student']['name'] ?? 'Unknown Student')
                    . " (" . ($data['application']['number'] ?? 'N/A') . ")";
                break;

            // 📤 Document uploaded
            case 'document_uploaded':
                $messageText = "📤 "
                    . ucfirst($data['document_type'] ?? 'Document')
                    . " uploaded for "
                    . ($data['student']['name'] ?? 'Unknown Student')
                    . " by "
                    . ($data['uploaded_by']['name'] ?? 'Unknown User');
                break;

            // 🗑️ Document deleted
            case 'document_deleted':
                $messageText = "🗑️ "
                    . ucfirst($data['document_type'] ?? 'Document')
                    . " deleted for "
                    . ($data['student']['name'] ?? 'Unknown Student')
                    . " by "
                    . ($data['deleted_by']['name'] ?? 'Unknown User');
                break;

            // 🆕 New user registered
            case 'user_registered':
                $messageText = "🆕 New user registered: "
                    . ($data['user_name'] ?? 'Unknown User');
                break;

            default:
                $messageText = $data['message'] ?? '🔔 New Notification';
        }

        return $messageText;
    }
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
