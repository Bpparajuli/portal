<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;


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
        $type = $notification->data['type'] ?? 'unknown';
        $messageText = $notification->data['message'] ?? 'New Notification';

        switch ($type) {
            case 'student_added':
                $messageText = "👤 New student added: " . ($notification->data['student_name'] ?? 'Unknown');
                break;
            case 'student_deleted':
                $messageText = "❌ Student deleted: " . ($notification->data['student_name'] ?? 'Unknown');
                break;
            case 'student_status':
                $messageText = "📌 Student status updated: " . ($notification->data['student_name'] ?? 'Unknown');
                break;
            case 'application_submitted':
                $messageText = "📝 Application submitted: " . ($notification->data['student_name'] ?? 'Unknown');
                break;
            case 'application_status':
                $messageText = "📌 Application status updated: " . ($notification->data['application_number'] ?? 'N/A');
                break;
            case 'application_message':
                $messageText = "💬 New message received for Application #" . ($notification->data['application_number'] ?? 'N/A');
                break;
            case 'application_withdrawn':
                $messageText = "⚠️ Application withdrawn: " . ($notification->data['application_number'] ?? 'N/A');
                break;
            case 'document_uploaded':
                $messageText = "📁 Document uploaded for " . ($notification->data['student_name'] ?? 'Unknown');
                break;
            case 'document_deleted':
                $messageText = "🗑 Document deleted for " . ($notification->data['student_name'] ?? 'Unknown');
                break;
            case 'user_registered':
                $messageText = "🆕 New user registered: " . ($notification->data['user_name'] ?? 'Unknown');
                break;
            default:
                $messageText = $notification->data['message'] ?? 'New Notification';
        }
        return $messageText;
    }
}
