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
        'completed_by',
        'call_direction',
        'duration_minutes',
        'meta_data',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'meta_data'    => 'array',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

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

    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    // =========================================================================
    // Scopes
    // =========================================================================

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
        return $query->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()]);
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
     * scopeAccessible — mirrors Student::scopeAccessible exactly.
     *
     * Additionally: staff always see tasks ASSIGNED TO THEM regardless of student ownership.
     *
     * Rules
     * ─────
     * Admin           → all tasks
     * Admin's staff   → all tasks OR tasks assigned to self
     * Agent           → tasks on own students + staff-under-them students
     * Agent's staff   → tasks on (own students + parent agent's students) OR assigned to self
     */
    public function scopeAccessible($query)
    {
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->is_admin) {
            return $query;
        }

        if ($user->is_staff) {

            // Admin's staff → all tasks
            if ($user->is_admin_staff) {
                return $query;
            }

            // Agent's staff → tasks on accessible students OR assigned directly to them
            if ($user->is_agent_staff) {
                return $query->where(function ($q) use ($user) {
                    $q->whereHas('student', function ($sq) use ($user) {
                        $sq->whereIn('agent_id', [$user->id, $user->parent_id]);
                    })->orWhere('assigned_to', $user->id);
                });
            }

            // Fallback staff → own students' tasks or assigned to self
            return $query->where(function ($q) use ($user) {
                $q->whereHas('student', fn($sq) => $sq->where('agent_id', $user->id))
                    ->orWhere('assigned_to', $user->id);
            });
        }

        if ($user->is_agent) {
            $staffIds        = User::where('parent_id', $user->id)->where('role', 'staff')->pluck('id')->toArray();
            $allowedAgentIds = array_merge([$user->id], $staffIds);

            return $query->whereHas('student', function ($q) use ($allowedAgentIds) {
                $q->whereIn('agent_id', $allowedAgentIds);
            });
        }

        return $query->whereRaw('1 = 0');
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /** Friendly alias → $task->title maps to the 'subject' column */
    public function getTitleAttribute(): string
    {
        return $this->subject ?? '';
    }

    /** $task->priority reads from meta_data['priority'] */
    public function getPriorityAttribute(): string
    {
        return $this->meta_data['priority'] ?? 'medium';
    }

    /** $task->time_slot maps to priority_time_slot */
    public function getTimeSlotAttribute(): ?string
    {
        return $this->priority_time_slot;
    }

    /** $task->due_date maps to scheduled_at */
    public function getDueDateAttribute()
    {
        return $this->scheduled_at;
    }

    public function getTimeSlotLabelAttribute(): string
    {
        return [
            'morning' => '🌅 Morning (9AM–12PM)',
            'day'     => '☀️ Day (12PM–4PM)',
            'evening' => '🌙 Evening (4PM–8PM)',
        ][$this->priority_time_slot] ?? 'Not scheduled';
    }

    public function getStatusBadgeAttribute(): string
    {
        return [
            'pending'   => 'badge-warning',
            'completed' => 'badge-success',
            'cancelled' => 'badge-danger',
            'missed'    => 'badge-dark',
        ][$this->status] ?? 'badge-secondary';
    }

    public function getActivityIconAttribute(): string
    {
        return [
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
        ][$this->activity_type] ?? '📌';
    }

    public function getActivityTypeLabelAttribute(): string
    {
        return [
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
        ][$this->activity_type] ?? ucfirst(str_replace('_', ' ', $this->activity_type ?? ''));
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending'
            && $this->scheduled_at !== null
            && $this->scheduled_at->lt(now());
    }

    // =========================================================================
    // Methods
    // =========================================================================

    public function markAsComplete(): static
    {
        $this->update(['status' => 'completed', 'completed_at' => now(), 'completed_by' => Auth::id()]);
        return $this;
    }

    public function markAsCancelled(): static
    {
        $this->update(['status' => 'cancelled']);
        return $this;
    }

    public function markAsMissed(): static
    {
        $this->update(['status' => 'missed']);
        return $this;
    }

    public function reassignTo(int $userId): static
    {
        $this->update(['assigned_to' => $userId]);
        return $this;
    }
}
