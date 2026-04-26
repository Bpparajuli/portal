<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'student_notes';

    protected $fillable = [
        'student_id',
        'created_by',
        'content',
        'type',
        'is_pinned',
        'remind_at',
        'reminder_time_slot'
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'remind_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeInternal($query)
    {
        return $query->where('type', 'internal');
    }

    public function scopeCustomerVisible($query)
    {
        return $query->where('type', 'customer_visible');
    }

    public function scopeReminders($query)
    {
        return $query->where('type', 'reminder')->whereNotNull('remind_at');
    }

    public function scopeUpcomingReminders($query)
    {
        return $query->where('type', 'reminder')
            ->whereNotNull('remind_at')
            ->where('remind_at', '>=', now())
            ->orderBy('remind_at', 'asc');
    }

    public function scopeDueReminders($query)
    {
        return $query->where('type', 'reminder')
            ->whereNotNull('remind_at')
            ->where('remind_at', '<=', now())
            ->whereNull('deleted_at');
    }

    /**
     * Accessors
     */
    public function getTypeBadgeAttribute()
    {
        $badges = [
            'internal' => 'badge-secondary',
            'customer_visible' => 'badge-info',
            'reminder' => 'badge-warning',
        ];
        return $badges[$this->type] ?? 'badge-secondary';
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            'internal' => 'Internal Note',
            'customer_visible' => 'Customer Visible',
            'reminder' => 'Reminder',
        ];
        return $labels[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Methods
     */
    public function togglePin()
    {
        $this->is_pinned = !$this->is_pinned;
        $this->save();

        return $this;
    }
}
