<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CrmTasks extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_tasks';

    protected $fillable = [
        'student_id',
        'created_by',
        'assigned_to',
        'activity_type',
        'subject',
        'description',
        'scheduled_at',
        'priority_time_slot',
        'status',
        'completed_at',
        'call_direction',
        'duration_minutes',
        'meta_data',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'meta_data'    => 'array',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_at', today());
    }

    public function scopeTomorrow($query)
    {
        return $query->whereDate('scheduled_at', Carbon::tomorrow());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('scheduled_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function scopeByTimeSlot($query, $slot)
    {
        return $query->where('priority_time_slot', $slot);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<', now());
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope activities visible to the currently authenticated user.
     *
     * - Admin  → all activities
     * - Agent  → activities on students they own, or students owned by their staff
     * - Staff  → only activities they created or are assigned to them,
     *            restricted to their own students
     */
    public function scopeAccessible($query)
    {
        $user = Auth::user();

        if ($user->is_admin) {
            return $query;
        }

        if ($user->is_agent) {
            return $query->whereHas('student', function ($q) use ($user) {
                $q->where('agent_id', $user->id)
                    ->orWhereHas('agent', function ($q) use ($user) {
                        $q->where('parent_id', $user->id);
                    });
            });
        }

        if ($user->is_staff) {
            return $query->whereHas('student', function ($q) use ($user) {
                $q->where('agent_id', $user->id);
            });
        }

        return $query->whereRaw('1 = 0');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getTimeSlotLabelAttribute(): string
    {
        $labels = [
            'morning' => '🌅 Morning (9AM - 12PM)',
            'day'     => '☀️ Day (12PM - 4PM)',
            'evening' => '🌙 Evening (4PM - 8PM)',
        ];
        return $labels[$this->priority_time_slot] ?? 'Not scheduled';
    }

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'pending'   => 'badge-warning',
            'completed' => 'badge-success',
            'cancelled' => 'badge-danger',
            'missed'    => 'badge-dark',
        ];
        return $badges[$this->status] ?? 'badge-secondary';
    }

    public function getActivityIconAttribute(): string
    {
        $icons = [
            'call'            => '📞',
            'email'           => '✉️',
            'meeting'         => '👥',
            'whatsapp'        => '💬',
            'todo'            => '✅',
            'follow_up'       => '⏰',
            'counseling'      => '🎓',
            'document_review' => '📄',
            'note'            => '📝',
            'stage_change'    => '🔄',
        ];
        return $icons[$this->activity_type] ?? '📌';
    }

    public function getActivityTypeLabelAttribute(): string
    {
        $labels = [
            'call'            => 'Phone Call',
            'email'           => 'Email',
            'meeting'         => 'Meeting',
            'whatsapp'        => 'WhatsApp',
            'todo'            => 'To Do',
            'follow_up'       => 'Follow Up',
            'counseling'      => 'Counseling',
            'document_review' => 'Document Review',
            'note'            => 'Note',
            'stage_change'    => 'Stage Change',
        ];
        return $labels[$this->activity_type] ?? ucfirst(str_replace('_', ' ', $this->activity_type));
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending'
            && $this->scheduled_at !== null
            && $this->scheduled_at->lt(now());
    }

    // -------------------------------------------------------------------------
    // Methods
    // -------------------------------------------------------------------------

    public function markAsComplete(): static
    {
        $this->status       = 'completed';
        $this->completed_at = now();
        $this->save();

        return $this;
    }

    public function markAsCancelled(): static
    {
        $this->status = 'cancelled';
        $this->save();

        return $this;
    }

    public function markAsMissed(): static
    {
        $this->status = 'missed';
        $this->save();

        return $this;
    }

    public function reassignTo(int $userId): static
    {
        $this->assigned_to = $userId;
        $this->save();

        return $this;
    }
}
