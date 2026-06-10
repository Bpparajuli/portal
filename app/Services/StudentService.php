<?php
// app/Services/StudentService.php
//
// UNIFIED STUDENT SERVICE
// Consolidates: StudentService (Services/), CreateStudentAction, UpdateStudentAction,
// DeleteStudentAction, ManageStudentTagsAction, SaveStudentNoteAction,
// ChangeStudentStageAction, UpdateStudentRatingAction, StudentDashboardService (task/revenue parts)
//
// Every public method accepts an optional ?User $user parameter for role-based data scoping.
// Role hierarchy: superadmin/admin (all data), admin_staff (near-admin),
// agent (own students), agent_staff (agent's students via parent_id), staff (accessible scope).

namespace App\Services;

use App\Models\CrmTasks;
use App\Models\Student;
use App\Models\StudentNote;
use App\Models\StudentStage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StudentService
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {}

    // -----------------------------------------------------------------------
    //  DUPLICATE DETECTION
    // -----------------------------------------------------------------------

    /**
     * Check if a student with matching email, phone, or name+phone already exists.
     *
     * Excludes the given $student during updates so the current record is not flagged.
     * Phone numbers are normalized (non-numeric chars removed, last 10 digits kept).
     *
     * @param  Request      $request  Incoming form/API request with student fields.
     * @param  Student|null $student  Existing student being updated (null for create).
     * @return Student|null           Matching student or null if no duplicate found.
     */
    public function findDuplicate(Request $request, ?Student $student = null): ?Student
    {
        $excludeId = $student?->id;

        // Check by email
        if ($request->filled('email')) {
            $existing = Student::where('id', '!=', $excludeId)
                ->where('email', $request->email)
                ->first();
            if ($existing) {
                return $existing;
            }
        }

        // Check by phone number (raw + cleaned)
        if ($request->filled('phone_number')) {
            $cleanPhone = $this->cleanPhoneNumber($request->phone_number);
            $existing = Student::where('id', '!=', $excludeId)
                ->where(function ($q) use ($cleanPhone, $request) {
                    $q->where('phone_number', $request->phone_number)
                      ->orWhere('phone_number', $cleanPhone);
                })->first();
            if ($existing) {
                return $existing;
            }
        }

        // Check by first_name + last_name + phone (catches walk-ins without email)
        if ($request->filled('first_name') && $request->filled('last_name') && $request->filled('phone_number')) {
            $existing = Student::where('id', '!=', $excludeId)
                ->where('first_name', $request->first_name)
                ->where('last_name', $request->last_name)
                ->where(function ($q) use ($request) {
                    $q->where('phone_number', $request->phone_number)
                      ->orWhere('phone_number', $this->cleanPhoneNumber($request->phone_number));
                })->first();
            if ($existing) {
                return $existing;
            }
        }

        return null;
    }

    /**
     * Format a human-readable duplicate warning message with the matched student's details.
     *
     * @param  Student $existingStudent The already-existing student record.
     * @return string                   User-facing message with name, email/phone, created date.
     */
    public function getDuplicateMessage(Student $existingStudent): string
    {
        $details = [];
        if ($existingStudent->email) {
            $details[] = "Email: {$existingStudent->email}";
        }
        if ($existingStudent->phone_number) {
            $details[] = "Phone: {$existingStudent->phone_number}";
        }
        $detailText = $details ? ' (' . implode(', ', $details) . ')' : '';

        return "Duplicate student found! Student '{$existingStudent->full_name}' already exists{$detailText}. "
             . "Last added on: {$existingStudent->created_at->format('Y-m-d H:i')}";
    }

    // -----------------------------------------------------------------------
    //  CREATE / UPDATE STUDENT
    // -----------------------------------------------------------------------

    /**
     * Create or update a student record with full data preparation, photo upload,
     * and folder structure.
     *
     * ROLE NOTES:
     * - Admins can assign any agent_id via the request.
     * - Agents are automatically set as the student's agent_id.
     * - agent_staff users have their parent_id used as the student's agent_id.
     * - New students without an explicit agent_id fall through a priority chain:
     *   request field → intake method default → logged user → system default.
     *
     * @param  Request      $request  Validated request with student fields + optional students_photo file.
     * @param  Student|null $student  Existing student for updates; null to create.
     * @return Student                Freshly created or updated student.
     * @throws \Exception             If a duplicate is detected during creation.
     */
    public function saveStudent(Request $request, ?Student $student = null): Student
    {
        $isNew = !$student;

        // Guard: reject new students that match an existing record
        if ($isNew) {
            $duplicate = $this->findDuplicate($request);
            if ($duplicate) {
                throw new \Exception($this->getDuplicateMessage($duplicate));
            }
        }

        return DB::transaction(function () use ($request, $student, $isNew) {
            $data = $this->prepareStudentData($request, $student);

            if ($isNew) {
                $student = Student::create($data);
            } else {
                $student->update($data);
            }

            // Handle photo upload
            if ($request->hasFile('students_photo')) {
                $this->handlePhotoUpload($request->file('students_photo'), $student);
            }

            // Ensure agent student folder exists on disk
            $this->ensureStudentFolderExists($student);

            return $student->fresh();
        });
    }

    /**
     * Map all incoming request fields to the Student model's fillable array.
     *
     * Handles: field mappings, phone cleaning, source defaults, tags JSON,
     * agent assignment with priority chain, initial stage, and created_by.
     *
     * @param  Request      $request  Incoming form data.
     * @param  Student|null $student  Existing student (null for new).
     * @return array                  Key-value array ready for create() or update().
     */
    private function prepareStudentData(Request $request, ?Student $student = null): array
    {
        $data = [];
        $intakeMethod = $request->input('_intake_method');

        // Map standard fields from request to model attributes
        $fieldMappings = [
            'first_name', 'last_name', 'email', 'phone_number', 'gender', 'dob',
            'marital_status', 'permanent_address', 'temporary_address', 'nationality',
            'passport_number', 'passport_expiry', 'applying_for', 'qualification',
            'passed_year', 'gap', 'last_grades', 'education_board',
            'preferred_country', 'preferred_city', 'preferred_course', 'preferred_university',
            'remarks', 'rating', 'expected_revenue', 'current_stage_id', 'created_by',
        ];
        foreach ($fieldMappings as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->input($field);
            }
        }

        // Normalize phone number to clean digits
        if ($request->has('phone_number') && $request->phone_number) {
            $data['phone_number'] = $this->cleanPhoneNumber($request->phone_number);
        }

        // Source field: user-provided custom source, or intake-method default, or 'manual'
        if ($request->filled('source')) {
            $data['source'] = $request->input('source');
        } elseif (!$student) {
            $data['source'] = match (true) {
                $intakeMethod === 'api_intake' => 'api_intake',
                $intakeMethod === 'quick_add'  => 'quick_add',
                default                         => 'manual',
            };
        }

        // Tags: accept JSON string or array, store as array
        if ($request->has('tags')) {
            $tags = $request->input('tags');
            $data['tags'] = is_string($tags) ? (json_decode($tags, true) ?: []) : $tags;
        }

        // Agent assignment (new students only)
        if (!$student) {
            $data['agent_id'] = $this->resolveAgentId($request, $intakeMethod);
        }

        // Stage: default to first pipeline stage if none provided
        if (!$student && empty($data['current_stage_id'])) {
            $initialStage = StudentStage::where('stage_order', 1)->first();
            $data['current_stage_id'] = $initialStage?->id;
        }

        // created_by: always record who created the record
        if (!$student && empty($data['created_by']) && Auth::check()) {
            $data['created_by'] = Auth::id();
        }

        return $data;
    }

    /**
     * Resolve which agent a new student belongs to using a priority chain:
     * 1. Explicit agent_id in the request.
     * 2. API/quick-add intake → default system agent (ID 12 or first agent).
     * 3. Logged-in user if they are an agent, agent_staff (uses parent_id), or staff.
     * 4. Fallback to first agent in the database.
     *
     * @param  Request     $request
     * @param  string|null $intakeMethod
     * @return int
     */
    private function resolveAgentId(Request $request, ?string $intakeMethod = null): int
    {
        if ($request->filled('agent_id')) {
            return (int) $request->input('agent_id');
        }

        if (in_array($intakeMethod, ['api_intake', 'quick_add'])) {
            return $this->getDefaultAgentId();
        }

        if (Auth::check()) {
            $user = Auth::user();
            if ($user->is_agent) {
                return $user->id;
            }
            if ($user->is_agent_staff) {
                return $user->parent_id;
            }
            if ($user->is_staff) {
                return $user->id;
            }
        }

        return $this->getDefaultAgentId();
    }

    /**
     * Get the agent for photo/folder purposes: prefer request field, then student's
     * existing agent, then the default agent.
     */
    private function getAgentForFileOps(Request $request, Student $student): ?User
    {
        if ($request->filled('agent_id')) {
            return User::find($request->input('agent_id'));
        }
        if ($student->agent_id) {
            return $student->agent;
        }
        return User::find($this->getDefaultAgentId());
    }

    /**
     * @return int ID of the first agent in the database, or 12 as hard fallback.
     */
    private function getDefaultAgentId(): int
    {
        return User::where('role', 'agent')->first()?->id ?? 12;
    }

    /**
     * Upload and assign a student photo, replacing any existing photo.
     */
    private function handlePhotoUpload($file, Student $student): void
    {
        $agent = $student->agent;
        if (!$agent) {
            Log::warning('Cannot upload photo: student has no agent', ['student_id' => $student->id]);
            return;
        }

        try {
            $path = $this->fileUploadService->uploadStudentFile(
                file: $file,
                agent: $agent,
                student: $student,
                type: 'photo',
                existingPath: $student->students_photo,
            );
            $student->updateQuietly(['students_photo' => $path]);
        } catch (\Exception $e) {
            Log::error('Failed to upload student photo: ' . $e->getMessage());
        }
    }

    // -----------------------------------------------------------------------
    //  DELETE STUDENT
    // -----------------------------------------------------------------------

    /**
     * Delete a student and optionally their associated files from disk.
     *
     * @param  Student $student  The student to delete.
     * @param  bool    $force    If true, permanently delete (forceDelete) and remove files.
     */
    public function deleteStudent(Student $student, bool $force = false): void
    {
        DB::transaction(function () use ($student, $force) {
            if ($force && $student->agent) {
                $this->fileUploadService->deleteStudentFiles($student->agent, $student);
                $student->forceDelete();
            } else {
                $student->delete();
            }
        });
    }

    // -----------------------------------------------------------------------
    //  STUDENT FOLDER MANAGEMENT
    // -----------------------------------------------------------------------

    /**
     * Ensure the student's cloud storage folder exists under agents/{slug}/{studentName}/.
     * Creates the directory if missing.
     */
    public function ensureStudentFolderExists(Student $student): void
    {
        $agent = $student->agent;
        if (!$agent) {
            Log::warning('Cannot create folder: student has no agent', ['student_id' => $student->id]);
            return;
        }

        $folderPath = sprintf(
            'agents/%s/%s',
            $agent->slug,
            $this->sanitizeName($student->first_name . ' ' . $student->last_name)
        );

        if (!Storage::disk('public')->exists($folderPath)) {
            Storage::disk('public')->makeDirectory($folderPath);
        }
    }

    // -----------------------------------------------------------------------
    //  TAG MANAGEMENT
    // -----------------------------------------------------------------------

    /**
     * Add a single tag to a student's tag array if it does not already exist.
     *
     * @return Student  Freshly saved student with updated tags.
     */
    public function addTag(Student $student, string $tag): Student
    {
        $tags = $student->tags ?? [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $student->tags = $tags;
            $student->save();
        }
        return $student;
    }

    /**
     * Remove a single tag from a student's tag array.
     *
     * @return Student  Freshly saved student with updated tags.
     */
    public function removeTag(Student $student, string $tag): Student
    {
        if ($student->tags) {
            $student->tags = array_values(
                array_filter($student->tags, fn($t) => $t !== $tag)
            );
            $student->save();
        }
        return $student;
    }

    // -----------------------------------------------------------------------
    //  NOTES
    // -----------------------------------------------------------------------

    /**
     * Save an internal note on a student and automatically create a corresponding
     * activity-log entry.
     *
     * @param  Student   $student  The student to attach the note to.
     * @param  string    $content  The note body text.
     * @param  User|null $user     The author (defaults to current authenticated user).
     * @return StudentNote         The created note record.
     */
    public function saveNote(Student $student, string $content, ?User $user = null): StudentNote
    {
        $user = $user ?? Auth::user();

        return DB::transaction(function () use ($student, $content, $user) {
            // The actual internal note
            $note = StudentNote::create([
                'student_id' => $student->id,
                'created_by' => $user->id,
                'content'    => $content,
                'type'       => 'internal',
            ]);

            // Activity log entry tracking who added it
            StudentNote::create([
                'student_id' => $student->id,
                'created_by' => $user->id,
                'content'    => "Note added by {$user->name}",
                'type'       => 'log',
                'title'      => 'Note Added',
                'is_log'     => true,
            ]);

            return $note;
        });
    }

    // -----------------------------------------------------------------------
    //  STAGE MANAGEMENT
    // -----------------------------------------------------------------------

    /**
     * Move a student to a new pipeline stage, with optional reason.
     *
     * Validates that the current stage allows the transition. If not allowed,
     * throws \InvalidArgumentException.
     * Automatically logs a StudentNote entry recording the change.
     *
     * @param  Student   $student  The student to move.
     * @param  int       $stageId  Target StudentStage ID.
     * @param  string    $reason   Optional justification for the change.
     * @param  User|null $user     Who performed the change (defaults to current user).
     * @return Student             Freshly reloaded student with new stage.
     * @throws \InvalidArgumentException
     */
    public function changeStage(Student $student, int $stageId, ?string $reason = null, ?User $user = null): Student
    {
        $user = $user ?? Auth::user();
        $currentStage = $student->currentStage;
        $oldStageName = $currentStage?->name ?? 'None';

        // Validate the transition is permitted
        if ($currentStage && !$currentStage->canMoveToStage($stageId)) {
            throw new \InvalidArgumentException(
                'This transition is not allowed from the current stage.'
            );
        }

        return DB::transaction(function () use ($student, $stageId, $reason, $user, $oldStageName) {
            $student->moveToStage($stageId, $reason);
            $newStage = StudentStage::find($stageId);

            // Log the change in the student's activity feed
            StudentNote::create([
                'student_id' => $student->id,
                'created_by' => $user->id,
                'content'    => "Stage changed from '{$oldStageName}' to '{$newStage?->name}'"
                              . ($reason ? " Reason: {$reason}" : ''),
                'type'       => 'log',
                'title'      => 'Stage Changed',
                'is_log'     => true,
            ]);

            return $student->fresh();
        });
    }

    // -----------------------------------------------------------------------
    //  RATING MANAGEMENT
    // -----------------------------------------------------------------------

    /**
     * Update a student's rating (1-3 stars or 0 for no rating).
     *
     * Automatically logs the change as a StudentNote entry.
     *
     * @param  Student   $student  The student to re-rate.
     * @param  int       $rating   New rating value (0-3).
     * @param  User|null $user     Who performed the update (defaults to current user).
     * @return Student             Freshly reloaded student with new rating.
     */
    public function updateRating(Student $student, int $rating, ?User $user = null): Student
    {
        $user = $user ?? Auth::user();
        $oldRating = $student->rating;

        return DB::transaction(function () use ($student, $rating, $user, $oldRating) {
            $student->rating = $rating;
            $student->save();

            $ratingText = $rating ?: 'No rating';
            $oldText    = $oldRating ?: 'No rating';

            StudentNote::create([
                'student_id' => $student->id,
                'created_by' => $user->id,
                'content'    => "Rating updated from '{$oldText}' to '{$ratingText}' by {$user->name}",
                'type'       => 'log',
                'title'      => 'Rating Updated',
                'is_log'     => true,
            ]);

            return $student->fresh();
        });
    }

    // -----------------------------------------------------------------------
    //  TASK CATEGORIES (for CRM student detail page)
    // -----------------------------------------------------------------------

    /**
     * Get categorized CRM tasks for a student, scoped to the viewing user's role.
     *
     * ROLE SCOPING:
     * - Admin/admin_staff: all tasks.
     * - Agent: own tasks + tasks assigned to their staff.
     * - Staff: only tasks assigned to themselves.
     * - agent_staff: own tasks + tasks assigned to sibling staff under the same agent.
     *
     * @param  Student $student  The student whose tasks to fetch.
     * @param  User    $user     The currently authenticated user.
     * @return array             Keys: dueTasks, todayTasks, plannedTasks, completedTasks, activityHistory.
     */
    public function getTaskCategories(Student $student, User $user): array
    {
        $dueTasks = $this->buildTaskQuery($student, $user)
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('scheduled_for')
            ->where('scheduled_for', '<', now()->startOfDay())
            ->orderBy('scheduled_for', 'asc')
            ->get();

        $todayTasks = $this->buildTaskQuery($student, $user)
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->whereDate('scheduled_for', now()->toDateString())
            ->orderBy('scheduled_for', 'asc')
            ->get();

        $plannedTasks = $this->buildTaskQuery($student, $user)
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->whereDate('scheduled_for', '>', now()->toDateString())
            ->orderBy('scheduled_for', 'asc')
            ->get();

        $completedTasks = $this->buildTaskQuery($student, $user)
            ->whereIn('status', ['completed', 'cancelled'])
            ->orderBy('completed_at', 'desc')
            ->paginate(10);

        $activityHistory = CrmTasks::where('student_id', $student->id)
            ->where('status', 'completed')
            ->with('assignee', 'creator')
            ->latest('completed_at')
            ->paginate(10);

        return compact('dueTasks', 'todayTasks', 'plannedTasks', 'completedTasks', 'activityHistory');
    }

    /**
     * Build a base CrmTasks query for the given student, scoped by the viewer's role.
     */
    private function buildTaskQuery(Student $student, User $user)
    {
        $query = CrmTasks::where('student_id', $student->id);

        if ($user->is_staff && !$user->is_admin && !$user->is_admin_staff) {
            // Staff see only their own assigned tasks
            $query->where('assigned_to', $user->id);
        } elseif ($user->is_agent_staff) {
            // agent_staff see their own + sibling staff tasks under their agent
            $staffIds = User::where('parent_id', $user->parent_id ?? $user->id)
                ->where('role', 'staff')->pluck('id');
            $query->whereIn('assigned_to', array_merge([$user->id], $staffIds->toArray()));
        } elseif ($user->is_agent) {
            // Agents see their own + their staff's tasks
            $staffIds = User::where('parent_id', $user->id)
                ->where('role', 'staff')->pluck('id');
            $query->whereIn('assigned_to', array_merge([$user->id], $staffIds->toArray()));
        }

        return $query;
    }

    // -----------------------------------------------------------------------
    //  ASSIGNABLE USERS (who can be assigned CRM tasks for a student)
    // -----------------------------------------------------------------------

    /**
     * Get the list of users eligible for task assignment, scoped by the current user's role.
     *
     * @param  User $user  The currently authenticated user.
     * @return \Illuminate\Support\Collection  Collection of User objects with id, name, role, parent_id.
     */
    public function getAssignableUsers(User $user): \Illuminate\Support\Collection
    {
        if ($user->is_admin || $user->is_admin_staff) {
            return User::where('role', 'staff')
                ->select('id', 'name', 'role', 'parent_id')
                ->orderBy('name')
                ->get();
        }

        if ($user->is_agent || $user->is_agent_staff) {
            return User::where('role', 'staff')
                ->where('parent_id', $user->parent_id ?? $user->id)
                ->select('id', 'name', 'role', 'parent_id')
                ->orderBy('name')
                ->get();
        }

        return collect();
    }

    // -----------------------------------------------------------------------
    //  STUDENT QUERY SCOPING (for listing/filtering by role)
    // -----------------------------------------------------------------------

    /**
     * Apply role-based scoping to a Student query.
     *
     * ROLE BEHAVIOR:
     * - Admin/superadmin: no filter (sees all).
     * - Agent: scoped to agent_id = user.id.
     * - agent_staff: scoped to agent_id = user.parent_id.
     * - Staff: uses the model's accessible() scope (defined in HasRoles trait).
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query  The student query to scope.
     * @param  User|null                             $user   The viewing user (defaults to current).
     * @return \Illuminate\Database\Eloquent\Builder         The scoped query.
     */
    public function scopeQueryByRole($query, ?User $user = null)
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            return $query;
        }

        if ($user->is_admin || $user->is_superadmin) {
            return $query;
        }

        if ($user->is_agent) {
            return $query->where('agent_id', $user->id);
        }

        if ($user->is_agent_staff) {
            return $query->where('agent_id', $user->parent_id);
        }

        // staff: uses accessible() scope from HasRoles trait
        return $query->accessible();
    }

    // -----------------------------------------------------------------------
    //  INTERNAL HELPERS
    // -----------------------------------------------------------------------

    /**
     * Normalize a phone number by stripping all non-numeric characters
     * and keeping the last 10 digits.
     */
    private function cleanPhoneNumber($phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleaned) > 10) {
            $cleaned = substr($cleaned, -10);
        }
        return $cleaned;
    }

    /**
     * Convert a name to a URL-safe slug for filesystem folder names.
     */
    private function sanitizeName($name): string
    {
        return strtolower(\Illuminate\Support\Str::slug($name));
    }
}
