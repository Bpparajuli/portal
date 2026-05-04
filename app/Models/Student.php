<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

class Student extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    // -------------------------------------------------------------------------
    // Constants
    // -------------------------------------------------------------------------

    const GENDERS = ['Male', 'Female', 'Other'];
    const MARITAL_STATUSES = ['Single', 'Married', 'Other'];
    const REQUIRED_DOCUMENTS = [
        'passport',
        '10th_certificate',
        '10th_transcript',
        '11th_transcript',
        '12th_certificate',
        '12th_transcript',
        'cv',
        'moi',
        'lor',
        'ielts_pte_language_certificate',
    ];

    // -------------------------------------------------------------------------
    // Mass Assignment
    // -------------------------------------------------------------------------

    protected $fillable = [
        'agent_id',
        'first_name',
        'last_name',
        'students_photo',
        'dob',
        'gender',
        'email',
        'phone_number',
        'permanent_address',
        'temporary_address',
        'nationality',
        'passport_number',
        'passport_expiry',
        'marital_status',
        'qualification',
        'passed_year',
        'gap',
        'last_grades',
        'education_board',
        'preferred_country',
        'preferred_city',
        'preferred_course',
        'preferred_university',
        'remarks',
        'current_stage_id',
        'rating',
        'tags',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected $casts = [
        'dob'             => 'date',
        'passport_expiry' => 'date',
        'tags'            => 'array',
        'passed_year'     => 'integer',
        'gap'             => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function currentStage()
    {
        return $this->belongsTo(StudentStage::class, 'current_stage_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function latestApplication()
    {
        return $this->hasOne(Application::class)->latestOfMany();
    }

    // ========================================================================
    // CRM Relationships
    // ========================================================================

    /**
     * Get all CRM activities for this student
     */
    public function activities()
    {
        return $this->hasMany(CrmTasks::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get pending activities (follow-ups, tasks, etc.)
     */
    public function pendingActivities()
    {
        return $this->hasMany(CrmTasks::class)->where('status', 'pending');
    }

    /**
     * Get follow-up activities specifically
     */
    public function followUps()
    {
        return $this->hasMany(CrmTasks::class)
            ->where('activity_type', 'follow_up')
            ->where('status', 'pending');
    }

    /**
     * Get today's scheduled activities
     */
    public function todayActivities()
    {
        return $this->hasMany(CrmTasks::class)
            ->whereDate('scheduled_at', today())
            ->orderBy('priority_time_slot')
            ->orderBy('scheduled_at');
    }

    /**
     * Get upcoming activities (not completed, future date)
     */
    public function upcomingActivities()
    {
        return $this->hasMany(CrmTasks::class)
            ->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '>=', now());
            })
            ->orderBy('scheduled_at');
    }

    /**
     * Get overdue activities
     */
    public function overdueActivities()
    {
        return $this->hasMany(CrmTasks::class)
            ->where('status', 'pending')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<', now())
            ->orderBy('scheduled_at');
    }

    /**
     * Get all CRM notes for this student
     */
    public function notes()
    {
        return $this->hasMany(StudentNote::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get pinned notes
     */
    public function pinnedNotes()
    {
        return $this->hasMany(StudentNote::class)->where('is_pinned', true);
    }

    /**
     * Get reminder notes
     */
    public function reminderNotes()
    {
        return $this->hasMany(StudentNote::class)
            ->where('type', 'reminder')
            ->whereNotNull('remind_at');
    }

    /**
     * Get stage change history for this student
     */
    public function stageHistory()
    {
        return $this->hasMany(StudentStageHistory::class)->orderBy('created_at', 'desc');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute(): ?int
    {
        return $this->dob?->age;
    }

    /**
     * Get days spent in current stage
     */
    public function getDaysInCurrentStageAttribute(): ?int
    {
        if (!$this->current_stage_id) {
            return null;
        }

        $lastHistory = $this->stageHistory()
            ->where('to_stage_id', $this->current_stage_id)
            ->latest()
            ->first();

        if ($lastHistory) {
            return $lastHistory->created_at->diffInDays(now());
        }

        return $this->created_at->diffInDays(now());
    }

    /**
     * Get next follow-up activity (closest pending follow-up)
     */
    public function getNextFollowUpAttribute()
    {
        return $this->followUps()
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->first();
    }

    /**
     * Get avatar URL
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->students_photo) {
            return asset('storage/' . $this->students_photo);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name) . '&background=random&color=fff';
    }

    /**
     * Get completion status based on documents
     */
    public function getCompletionStatusAttribute(): string
    {
        $stats = $this->getDocumentStats();
        return $stats['status'];
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentageAttribute(): int
    {
        $stats = $this->getDocumentStats();
        return $stats['progress'];
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope to filter students by stage
     */
    public function scopeByStage($query, $stageId)
    {
        return $query->where('current_stage_id', $stageId);
    }

    /**
     * Scope to search students
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone_number', 'like', "%{$term}%")
                ->orWhere('passport_number', 'like', "%{$term}%");
        });
    }

    /**
     * Scope to filter by agent
     */
    public function scopeByAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    /**
     * Scope to get students with upcoming activities
     */
    public function scopeWithUpcomingActivities($query)
    {
        return $query->whereHas('activities', function ($q) {
            $q->where('status', 'pending')
                ->where(function ($sq) {
                    $sq->whereNull('scheduled_at')
                        ->orWhere('scheduled_at', '>=', now());
                });
        });
    }

    /**
     * Scope to get students with overdue activities
     */
    public function scopeWithOverdueActivities($query)
    {
        return $query->whereHas('activities', function ($q) {
            $q->where('status', 'pending')
                ->whereNotNull('scheduled_at')
                ->where('scheduled_at', '<', now());
        });
    }
    /**
     * Critical Scope: Shows students based on user role
     * - Admin: All students
     * - Agent: Their students + their staff's students
     * - Staff: Only students where agent_id = their own ID
     */
    // app/Models/Student.php - Replace the scopeAccessible method
    public function scopeAccessible($query)
    {
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        // ── Admin ─────────────────────────────────────────────────────────────
        if ($user->is_admin) {
            return $query;
        }

        // ── Staff ─────────────────────────────────────────────────────────────
        if ($user->is_staff) {

            // Admin's staff → all students
            if ($user->is_admin_staff) {
                return $query;
            }

            // Agent's staff → own students + parent agent's students
            if ($user->is_agent_staff) {
                return $query->whereIn('agent_id', [$user->id, $user->parent_id]);
            }

            // Fallback: staff with unknown/no parent → only own students
            return $query->where('agent_id', $user->id);
        }

        // ── Agent ─────────────────────────────────────────────────────────────
        if ($user->is_agent) {
            $staffIds        = User::where('parent_id', $user->id)->where('role', 'staff')->pluck('id')->toArray();
            $allowedAgentIds = array_merge([$user->id], $staffIds);
            return $query->whereIn('agent_id', $allowedAgentIds);
        }

        return $query->whereRaw('1 = 0');
    }

    // -------------------------------------------------------------------------
    // Methods
    // -------------------------------------------------------------------------

    /**
     * Move student to a new stage with history tracking
     */
    public function moveToStage($newStageId, $reason = null, $metadata = null): bool
    {
        $oldStageId = $this->current_stage_id;
        $newStage = StudentStage::find($newStageId);

        if (!$newStage) {
            return false;
        }

        // Calculate days in previous stage
        $daysInPreviousStage = null;
        if ($oldStageId) {
            $lastHistory = $this->stageHistory()
                ->where('to_stage_id', $oldStageId)
                ->latest()
                ->first();

            if ($lastHistory) {
                $daysInPreviousStage = $lastHistory->created_at->diffInDays(now());
            } else {
                $daysInPreviousStage = $this->created_at->diffInDays(now());
            }
        }

        // Create history record
        StudentStageHistory::create([
            'student_id' => $this->id,
            'from_stage_id' => $oldStageId,
            'to_stage_id' => $newStageId,
            'changed_by' => Auth::id(),
            'reason' => $reason,
            'metadata' => $metadata,
            'days_in_previous_stage' => $daysInPreviousStage,
        ]);

        // Update student's current stage
        $this->current_stage_id = $newStageId;
        $this->save();

        // Create activity for stage change
        CrmTasks::create([
            'student_id' => $this->id,
            'created_by' => Auth::id(),
            'activity_type' => 'stage_change',
            'subject' => 'Stage Changed',
            'description' => "Student moved from " .
                ($oldStageId ? StudentStage::find($oldStageId)?->name : 'Initial') .
                " to " . $newStage->name .
                ($reason ? " Reason: {$reason}" : ""),
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return true;
    }

    /**
     * Get all pending follow-ups for this student
     */
    public function getPendingFollowUps()
    {
        return $this->followUps()
            ->whereNotNull('scheduled_at')
            ->orderBy('scheduled_at')
            ->get();
    }

    /**
     * Get today's follow-ups
     */
    public function getTodayFollowUps()
    {
        return $this->followUps()
            ->whereDate('scheduled_at', today())
            ->orderBy('priority_time_slot')
            ->get();
    }

    /**
     * Check if student has any pending follow-ups
     */
    public function hasPendingFollowUps(): bool
    {
        return $this->followUps()->exists();
    }

    /**
     * Check if student is complete (all documents uploaded)
     */
    public function isComplete(): bool
    {
        $stats = $this->getDocumentStats();
        return $stats['status'] === 'Completed';
    }

    // -------------------------------------------------------------------------
    // Tag Helpers
    // -------------------------------------------------------------------------

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? []);
    }

    public function addTag(string $tag): static
    {
        $tags = $this->tags ?? [];

        if (! in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->tags = $tags;
            $this->saveQuietly();
        }

        return $this;
    }

    public function removeTag(string $tag): static
    {
        if ($this->tags) {
            $this->tags = array_values(
                array_filter($this->tags, fn($t) => $t !== $tag)
            );
            $this->saveQuietly();
        }

        return $this;
    }

    // -------------------------------------------------------------------------
    // Document Stats
    // -------------------------------------------------------------------------

    public function getDocumentStats(): array
    {
        $required       = self::REQUIRED_DOCUMENTS;
        $totalRequired  = count($required);
        $uploadedTypes  = [];

        foreach ($this->documents as $doc) {
            $type = strtolower(str_replace(' ', '', $doc->document_type));
            if (in_array($type, $required)) {
                $uploadedTypes[$type] = true;
            }
        }

        $uploadedCount = count($uploadedTypes);

        $status = match (true) {
            $uploadedCount === $totalRequired => 'Completed',
            $uploadedCount === 0             => 'Not Uploaded',
            default                          => 'Incomplete',
        };

        return [
            'status'         => $status,
            'progress'       => $totalRequired > 0 ? round(($uploadedCount / $totalRequired) * 100) : 0,
            'uploaded_count' => $uploadedCount,
            'total_required' => $totalRequired,
        ];
    }

    public static function countStudentsWithAllCompulsoryDocuments(?int $agentId = null): int
    {
        $required = self::REQUIRED_DOCUMENTS;

        $query = Document::whereIn('document_type', $required)
            ->select('student_id')
            ->groupBy('student_id')
            ->havingRaw('COUNT(DISTINCT document_type) = ?', [count($required)]);

        if ($agentId) {
            $query->whereHas('student', fn($q) => $q->where('agent_id', $agentId));
        }

        return $query->count('student_id');
    }
}
