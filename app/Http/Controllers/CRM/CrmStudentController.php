<?php
// app/Http/Controllers/CRM/CrmStudentController.php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CrmTasks;
use App\Models\Student;
use App\Models\StudentNote;
use App\Models\StudentStage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\StudentService;
use Carbon\Carbon;

class CrmStudentController extends Controller
{
    /**
     * Get navigation for students with tasks today (with role-based filtering)
     * 
     * @param int $currentStudentId
     * @return array|null
     */
    private function getTodayTaskNavigation($currentStudentId)
    {
        try {
            $user = Auth::user();
            $today = Carbon::today();

            // Build query based on user role
            $tasksQuery = CrmTasks::where('status', '!=', 'completed')
                ->where('status', '!=', 'cancelled')
                ->whereDate('scheduled_for', $today)
                ->whereNotNull('student_id');

            // STAFF: Only see tasks assigned to them
            if ($user->is_staff && !$user->is_admin && !$user->is_admin_staff) {
                $tasksQuery->where('assigned_to', $user->id);
            }
            // AGENT STAFF: Only see tasks assigned to them or their staff
            elseif ($user->is_agent_staff) {
                $staffIds = User::where('parent_id', $user->parent_id ?? $user->id)
                    ->where('role', 'staff')
                    ->pluck('id')
                    ->toArray();
                $tasksQuery->whereIn('assigned_to', array_merge([$user->id], $staffIds));
            }
            // AGENT: See tasks assigned to them or their staff
            elseif ($user->is_agent) {
                $staffIds = User::where('parent_id', $user->id)
                    ->where('role', 'staff')
                    ->pluck('id')
                    ->toArray();
                $tasksQuery->whereIn('assigned_to', array_merge([$user->id], $staffIds));
            }
            // ADMIN & ADMIN_STAFF: See ALL tasks (no filter)

            // Get distinct student IDs with their task counts
            $studentsWithTasks = $tasksQuery->select('student_id')
                ->distinct()
                ->get()
                ->map(function ($item) use ($tasksQuery, $today, $user) {
                    // Get task count for this student
                    $taskCountQuery = CrmTasks::where('student_id', $item->student_id)
                        ->where('status', '!=', 'completed')
                        ->where('status', '!=', 'cancelled')
                        ->whereDate('scheduled_for', $today)
                        ->whereNotNull('student_id');

                    // Apply same role filter for count
                    if ($user->is_staff && !$user->is_admin && !$user->is_admin_staff) {
                        $taskCountQuery->where('assigned_to', $user->id);
                    } elseif ($user->is_agent_staff) {
                        $staffIds = User::where('parent_id', $user->parent_id ?? $user->id)
                            ->where('role', 'staff')
                            ->pluck('id')
                            ->toArray();
                        $taskCountQuery->whereIn('assigned_to', array_merge([$user->id], $staffIds));
                    } elseif ($user->is_agent) {
                        $staffIds = User::where('parent_id', $user->id)
                            ->where('role', 'staff')
                            ->pluck('id')
                            ->toArray();
                        $taskCountQuery->whereIn('assigned_to', array_merge([$user->id], $staffIds));
                    }

                    return [
                        'student_id' => $item->student_id,
                        'tasks_count' => $taskCountQuery->count()
                    ];
                })
                ->filter(function ($item) {
                    return $item['tasks_count'] > 0;
                })
                ->values();

            $studentIdsWithTodayTasks = $studentsWithTasks->pluck('student_id')->toArray();

            // If no students with today's tasks, return null
            if (empty($studentIdsWithTodayTasks)) {
                return null;
            }

            // Find current student's position in the array
            $currentIndex = array_search($currentStudentId, $studentIdsWithTodayTasks);

            // If current student doesn't have tasks today, return null
            if ($currentIndex === false) {
                return null;
            }

            // Get previous and next student IDs
            $prevStudentId = $studentIdsWithTodayTasks[$currentIndex - 1] ?? null;
            $nextStudentId = $studentIdsWithTodayTasks[$currentIndex + 1] ?? null;

            // Get task counts for current student
            $currentStudentTaskCount = $studentsWithTasks->where('student_id', $currentStudentId)->first()['tasks_count'] ?? 0;

            $navigation = [
                'prev' => null,
                'next' => null,
                'total' => count($studentIdsWithTodayTasks),
                'current_position' => $currentIndex + 1,
                'current_student_has_tasks' => true,
                'current_student_tasks_count' => $currentStudentTaskCount,
            ];

            // Load previous student data
            if ($prevStudentId) {
                $prevStudent = Student::find($prevStudentId);
                $prevTaskCount = $studentsWithTasks->where('student_id', $prevStudentId)->first()['tasks_count'] ?? 0;

                if ($prevStudent) {
                    $navigation['prev'] = [
                        'id' => $prevStudent->id,
                        'name' => $prevStudent->first_name . ' ' . $prevStudent->last_name,
                        'first_name' => $prevStudent->first_name,
                        'last_name' => $prevStudent->last_name,
                        'tasks_count' => $prevTaskCount
                    ];
                }
            }

            // Load next student data
            if ($nextStudentId) {
                $nextStudent = Student::find($nextStudentId);
                $nextTaskCount = $studentsWithTasks->where('student_id', $nextStudentId)->first()['tasks_count'] ?? 0;

                if ($nextStudent) {
                    $navigation['next'] = [
                        'id' => $nextStudent->id,
                        'name' => $nextStudent->first_name . ' ' . $nextStudent->last_name,
                        'first_name' => $nextStudent->first_name,
                        'last_name' => $nextStudent->last_name,
                        'tasks_count' => $nextTaskCount
                    ];
                }
            }

            return $navigation;
        } catch (\Exception $e) {
            Log::warning('Failed to get today task navigation: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Clear the today tasks navigation cache
     */
    public function clearTodayTasksCache()
    {
        $today = Carbon::today();
        $cacheKey = 'today_task_students_' . $today->format('Y-m-d') . '_user_' . Auth::id();
        Cache::forget($cacheKey);

        return response()->json(['success' => true, 'message' => 'Cache cleared']);
    }

    /**
     * Display student details page
     */
    public function show(Student $student)
    {
        try {
            $this->authorizeStudent($student);
            $user = Auth::user();

            $student->load([
                'currentStage',
                'agent',
                'documents',
                'latestApplication',
                'revenues.creator',
            ]);

            // Get tasks with role-based filtering
            // ==============================================
            // DUE TASKS (overdue)
            // ==============================================
            $dueTasksQuery = CrmTasks::where('student_id', $student->id)
                ->where('status', '!=', 'completed')
                ->where('status', '!=', 'cancelled')
                ->whereNotNull('scheduled_for')
                ->where('scheduled_for', '<', now()->startOfDay());

            // STAFF: Only see tasks assigned to them
            if ($user->is_staff && !$user->is_admin && !$user->is_admin_staff) {
                $dueTasksQuery->where('assigned_to', $user->id);
            }
            // AGENT STAFF: See tasks assigned to them or their staff
            elseif ($user->is_agent_staff) {
                $staffIds = User::where('parent_id', $user->parent_id ?? $user->id)
                    ->where('role', 'staff')
                    ->pluck('id')
                    ->toArray();
                $dueTasksQuery->whereIn('assigned_to', array_merge([$user->id], $staffIds));
            }
            // AGENT: See tasks assigned to them or their staff
            elseif ($user->is_agent) {
                $staffIds = User::where('parent_id', $user->id)
                    ->where('role', 'staff')
                    ->pluck('id')
                    ->toArray();
                $dueTasksQuery->whereIn('assigned_to', array_merge([$user->id], $staffIds));
            }
            // ADMIN & ADMIN_STAFF: See ALL tasks (no filter)

            $dueTasks = $dueTasksQuery->orderBy('scheduled_for', 'asc')->get();

            // ==============================================
            // TODAY'S TASKS
            // ==============================================
            $todayTasksQuery = CrmTasks::where('student_id', $student->id)
                ->where('status', '!=', 'completed')
                ->where('status', '!=', 'cancelled')
                ->whereDate('scheduled_for', now()->toDateString());

            // Apply role-based filter
            if ($user->is_staff && !$user->is_admin && !$user->is_admin_staff) {
                $todayTasksQuery->where('assigned_to', $user->id);
            } elseif ($user->is_agent_staff) {
                $staffIds = User::where('parent_id', $user->parent_id ?? $user->id)
                    ->where('role', 'staff')
                    ->pluck('id')
                    ->toArray();
                $todayTasksQuery->whereIn('assigned_to', array_merge([$user->id], $staffIds));
            } elseif ($user->is_agent) {
                $staffIds = User::where('parent_id', $user->id)
                    ->where('role', 'staff')
                    ->pluck('id')
                    ->toArray();
                $todayTasksQuery->whereIn('assigned_to', array_merge([$user->id], $staffIds));
            }

            $todayTasks = $todayTasksQuery->orderBy('scheduled_for', 'asc')->get();

            // ==============================================
            // PLANNED TASKS (future)
            // ==============================================
            $plannedTasksQuery = CrmTasks::where('student_id', $student->id)
                ->where('status', '!=', 'completed')
                ->where('status', '!=', 'cancelled')
                ->whereDate('scheduled_for', '>', now()->toDateString());

            // Apply role-based filter
            if ($user->is_staff && !$user->is_admin && !$user->is_admin_staff) {
                $plannedTasksQuery->where('assigned_to', $user->id);
            } elseif ($user->is_agent_staff) {
                $staffIds = User::where('parent_id', $user->parent_id ?? $user->id)
                    ->where('role', 'staff')
                    ->pluck('id')
                    ->toArray();
                $plannedTasksQuery->whereIn('assigned_to', array_merge([$user->id], $staffIds));
            } elseif ($user->is_agent) {
                $staffIds = User::where('parent_id', $user->id)
                    ->where('role', 'staff')
                    ->pluck('id')
                    ->toArray();
                $plannedTasksQuery->whereIn('assigned_to', array_merge([$user->id], $staffIds));
            }

            $plannedTasks = $plannedTasksQuery->orderBy('scheduled_for', 'asc')->get();

            // ==============================================
            // COMPLETED TASKS (history)
            // ==============================================
            $completedTasksQuery = CrmTasks::where('student_id', $student->id)
                ->whereIn('status', ['completed', 'cancelled']);

            // Apply role-based filter for completed tasks too
            if ($user->is_staff && !$user->is_admin && !$user->is_admin_staff) {
                $completedTasksQuery->where('assigned_to', $user->id);
            } elseif ($user->is_agent_staff) {
                $staffIds = User::where('parent_id', $user->parent_id ?? $user->id)
                    ->where('role', 'staff')
                    ->pluck('id')
                    ->toArray();
                $completedTasksQuery->whereIn('assigned_to', array_merge([$user->id], $staffIds));
            } elseif ($user->is_agent) {
                $staffIds = User::where('parent_id', $user->id)
                    ->where('role', 'staff')
                    ->pluck('id')
                    ->toArray();
                $completedTasksQuery->whereIn('assigned_to', array_merge([$user->id], $staffIds));
            }

            $completedTasks = $completedTasksQuery->orderBy('completed_at', 'desc')->paginate(10);

            $activityHistory = CrmTasks::where('student_id', $student->id)
                ->where('status', 'completed')
                ->with('assignee', 'creator')
                ->latest('completed_at')
                ->paginate(10);

            $notes = $student->notes()
                ->where('is_log', false)
                ->with('creator')
                ->orderBy('is_pinned', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            $activityLogs = $student->notes()
                ->where('is_log', true)
                ->with('creator')
                ->orderBy('created_at', 'desc')
                ->get();

            $staffUsers = User::where('role', 'staff')
                ->orderBy('name')
                ->get(['id', 'name', 'role', 'business_logo']);

            $stages = StudentStage::active()->ordered()->get();
            $currentStage = $student->currentStage;

            $assignableUsers = collect();

            if ($user->is_admin || $user->is_admin_staff) {
                $assignableUsers = User::where(function ($q) {
                    $q->where('role', 'staff');
                })->select('id', 'name', 'role', 'parent_id')
                    ->orderBy('name')
                    ->get();
            } elseif ($user->is_agent || $user->is_agent_staff) {
                $assignableUsers = User::where('role', 'staff')
                    ->where('parent_id', $user->parent_id ?? $user->id)
                    ->select('id', 'name', 'role', 'parent_id')
                    ->orderBy('name')
                    ->get();
            }

            // Get revenues - paginated
            $revenues = $student->revenues()
                ->with('creator')
                ->orderBy('transaction_date', 'desc')
                ->paginate(10);

            $canEdit = !$user->is_agent;
            $expectedRevenue = $student->expected_revenue ?? 0;
            $collectedRevenue = $student->received_revenue ?? 0;
            $remainingDue = max(0, $expectedRevenue - $collectedRevenue);
            $revenuesCollection = $revenues instanceof \Illuminate\Pagination\LengthAwarePaginator
                ? $revenues->getCollection()
                : $revenues;

            // Get today task navigation (with role-based filtering)
            $todayTaskNavigation = $this->getTodayTaskNavigation($student->id);


            return view('crm.show', compact(
                'student',
                'dueTasks',
                'todayTasks',
                'plannedTasks',
                'activityHistory',
                'completedTasks',
                'notes',
                'activityLogs',
                'stages',
                'currentStage',
                'assignableUsers',
                'canEdit',
                'staffUsers',
                'revenues',
                'remainingDue',
                'revenuesCollection',
                'expectedRevenue',
                'collectedRevenue',
                'todayTaskNavigation',
            ));
        } catch (\Exception $e) {
            Log::error('Error in CrmStudentController@show: ' . $e->getMessage());
            return back()->with('error', 'Failed to load student details: ' . $e->getMessage());
        }
    }

    /**
     * Store a new student (using StudentService)
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone_number' => 'nullable|string|max:20',
            'source' => 'nullable|string|max:255',
            'preferred_country' => 'nullable|string|max:100',
            'current_stage_id' => 'required|exists:student_stages,id',
            'agent_id' => 'nullable|exists:users,id',
        ]);

        try {
            $this->clearTodayTasksCache();

            $duplicate = StudentService::findDuplicate($request);
            if ($duplicate) {
                $duplicateMessage = StudentService::getDuplicateMessage($duplicate);
                return redirect()->back()
                    ->with('error', $duplicateMessage)
                    ->with('duplicate_student', $duplicate)
                    ->withInput();
            }

            $student = StudentService::saveStudent($request);

            $successMessage = sprintf(
                "✅ Student added successfully!\n\nStudent: %s %s\nPhone: %s\nEmail: %s\nSource: %s",
                $student->first_name,
                $student->last_name,
                $student->phone_number ?? 'Not provided',
                $student->email ?? 'Not provided',
                $student->source ?? 'manual'
            );

            Log::info('Student created via CRM modal', [
                'student_id' => $student->id,
                'name' => $student->full_name,
                'source' => $student->source,
                'agent_id' => $student->agent_id,
                'created_by' => Auth::id()
            ]);

            return redirect()->route('crm.dashboard')
                ->with('success', $successMessage)
                ->with('student_created', $student);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return redirect()->back()
                    ->with('error', '❌ A student with this email already exists in the system. Please check and try again.')
                    ->withInput();
            }

            Log::error('Database error creating student: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', '❌ Failed to add student due to database error. Please try again.')
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Failed to create student: ' . $e->getMessage());

            if (str_contains($e->getMessage(), 'Duplicate student found')) {
                return redirect()->back()
                    ->with('error', $e->getMessage())
                    ->withInput();
            }

            return redirect()->back()
                ->with('error', '❌ Failed to add student: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mini update for student (quick edit)
     */
    public function miniUpdate(Request $request, Student $student)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'preferred_country' => 'nullable|string|max:100',
                'applying_for' => 'nullable|string|max:255',
                'expected_revenue' => 'nullable|numeric|min:0',
                'tags' => 'nullable|string',
            ]);

            if (!empty($validated['tags'])) {
                $tags = array_map('trim', explode(',', $validated['tags']));
                $validated['tags'] = array_filter($tags);
            } else {
                $validated['tags'] = [];
            }

            $serviceRequest = new \Illuminate\Http\Request($validated);
            StudentService::saveStudent($serviceRequest, $student);
            $this->clearTodayTasksCache();

            StudentNote::create([
                'student_id' => $student->id,
                'content' => "Student information was updated by " . Auth::user()->name,
                'type' => 'log',
                'title' => 'Student Updated',
                'created_by' => Auth::id(),
                'is_log' => true,
            ]);

            return redirect()->back()
                ->with('success', '✅ Student updated successfully!');
        } catch (\Exception $e) {
            Log::error('Mini update error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', '❌ Failed to update student: ' . $e->getMessage());
        }
    }

    /**
     * Show edit form for student
     */
    public function edit(Student $student)
    {
        $this->authorizeStudent($student);

        $user = Auth::user();
        $agents = collect();

        if ($user->is_admin || $user->is_admin_staff) {
            $agents = User::where('role', 'agent')->orderBy('business_name')->get();
        } elseif ($user->is_agent) {
            $agents = User::where('id', $user->id)->get();
        }

        return view('crm.student-edit', compact('student', 'agents'));
    }

    /**
     * Update student (using StudentService)
     */
    public function update(Request $request, Student $student)
    {
        try {
            $this->authorizeStudent($student);
            $this->denyAgents();

            $validated = $request->validate([
                'first_name' => ['required', 'string', 'max:100'],
                'last_name' => ['required', 'string', 'max:100'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone_number' => ['nullable', 'string', 'max:20'],
                'dob' => ['nullable', 'date'],
                'gender' => ['nullable', 'string', 'in:Male,Female,Other'],
                'marital_status' => ['nullable', 'string', 'in:Single,Married,Other'],
                'students_photo' => ['nullable', 'image', 'max:5120'],
                'permanent_address' => ['nullable', 'string'],
                'temporary_address' => ['nullable', 'string'],
                'nationality' => ['nullable', 'string', 'max:100'],
                'passport_number' => ['nullable', 'string', 'max:50'],
                'passport_expiry' => ['nullable', 'date'],
                'applying_for' => ['nullable', 'string', 'max:255'],
                'qualification' => ['nullable', 'string', 'max:255'],
                'passed_year' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
                'education_board' => ['nullable', 'string', 'max:255'],
                'last_grades' => ['nullable', 'string', 'max:50'],
                'gap' => ['nullable', 'integer', 'min:0', 'max:50'],
                'preferred_country' => ['nullable', 'string', 'max:100'],
                'preferred_city' => ['nullable', 'string', 'max:100'],
                'preferred_course' => ['nullable', 'string', 'max:255'],
                'preferred_university' => ['nullable', 'string', 'max:255'],
                'remarks' => ['nullable', 'string'],
                'rating' => ['nullable', 'integer', 'min:1', 'max:3'],
                'pinned' => ['nullable|boolean'],
                'agent_id' => ['nullable', 'exists:users,id'],
                'source' => ['nullable', 'string', 'max:255'],
            ]);

            $serviceRequest = new Request($validated);

            if ($request->hasFile('students_photo')) {
                $serviceRequest->files->set('students_photo', $request->file('students_photo'));
            }

            $updatedStudent = StudentService::saveStudent($serviceRequest, $student);
            $this->clearTodayTasksCache();

            $successMessage = sprintf(
                "✅ Student updated successfully!\n\nStudent: %s %s\nPhone: %s\nEmail: %s",
                $updatedStudent->first_name,
                $updatedStudent->last_name,
                $updatedStudent->phone_number ?? 'Not provided',
                $updatedStudent->email ?? 'Not provided'
            );

            return redirect()->route('crm.student.show', $updatedStudent)
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            Log::error('Student update failed: ' . $e->getMessage());
            return back()
                ->with('error', '❌ Failed to update student: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete student
     */
    public function destroy(Student $student)
    {
        try {
            $this->authorizeStudent($student);
            $this->denyAgents();

            $studentName = $student->full_name;
            StudentService::deleteStudent($student);
            $this->clearTodayTasksCache();

            return redirect()->route('crm.dashboard')
                ->with('success', "✅ Student '{$studentName}' has been deleted successfully!");
        } catch (\Exception $e) {
            Log::error('Student deletion failed: ' . $e->getMessage());
            return back()
                ->with('error', '❌ Failed to delete student: ' . $e->getMessage());
        }
    }

    /**
     * Save note for student
     */
    public function saveNote(Request $request, Student $student)
    {
        try {
            $this->authorizeStudent($student);
            $this->denyAgents();

            $validated = $request->validate([
                'content'   => ['required', 'string'],
                'is_pinned' => ['boolean'],
            ]);

            StudentNote::create([
                'student_id' => $student->id,
                'created_by' => Auth::id(),
                'content'    => $validated['content'],
                'type'       => 'internal',
                'is_pinned'  => $request->boolean('is_pinned', false),
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => '✅ Note saved successfully.']);
            }
            return back()->with('success', '✅ Note saved successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
            return back()->with('error', '❌ Failed to save note: ' . $e->getMessage());
        }
    }

    /**
     * Change student stage
     */
    public function changeStage(Request $request, Student $student)
    {
        try {
            $this->authorizeStudent($student);
            $this->denyAgents();

            $validated = $request->validate([
                'new_stage_id' => ['required', 'exists:student_stages,id'],
                'reason'       => ['nullable', 'string', 'max:500'],
            ]);

            if ($student->currentStage && !$student->currentStage->canMoveToStage($validated['new_stage_id'])) {
                return back()->withErrors(['new_stage_id' => 'This transition is not allowed from the current stage.']);
            }

            $oldStageName = $student->currentStage?->name ?? 'None';
            $student->moveToStage($validated['new_stage_id'], $validated['reason'] ?? null);
            $newStage = StudentStage::find($validated['new_stage_id']);

            $successMessage = "✅ Stage updated successfully!<br>Moved from '{$oldStageName}' to '{$newStage->name}'";

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $successMessage]);
            }
            return back()->with('success', $successMessage);
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
            return back()->with('error', '❌ Failed to update stage: ' . $e->getMessage());
        }
    }

    /**
     * Update student rating
     */
    public function updateRating(Request $request, $id)
    {
        try {
            $request->validate([
                'rating' => 'nullable|integer|min:1|max:3',
            ]);

            $student = Student::findOrFail($id);
            $user = Auth::user();

            if (!$this->checkAccess($user, $student)) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            $oldRating = $student->rating;
            $student->rating = $request->rating;
            $student->save();

            $ratingText = $request->rating ?: 'No rating';
            $oldRatingText = $oldRating ?: 'No rating';

            return response()->json([
                'success' => true,
                'message' => "✅ Rating updated from '{$oldRatingText}' to '{$ratingText}'",
                'rating' => $student->rating
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // API Endpoints for AJAX Navigation
    // =========================================================================

    /**
     * Get all student IDs that have tasks today (with role-based filtering)
     */
    public function getTodayTaskStudentIds()
    {
        try {
            $user = Auth::user();
            $today = Carbon::today();

            $tasksQuery = CrmTasks::where('status', '!=', 'completed')
                ->where('status', '!=', 'cancelled')
                ->whereDate('scheduled_for', $today)
                ->whereNotNull('student_id');

            // Apply role-based filter
            if ($user->is_staff && !$user->is_admin && !$user->is_admin_staff) {
                $tasksQuery->where('assigned_to', $user->id);
            } elseif ($user->is_agent_staff) {
                $staffIds = User::where('parent_id', $user->parent_id ?? $user->id)
                    ->where('role', 'staff')
                    ->pluck('id')
                    ->toArray();
                $tasksQuery->whereIn('assigned_to', array_merge([$user->id], $staffIds));
            } elseif ($user->is_agent) {
                $staffIds = User::where('parent_id', $user->id)
                    ->where('role', 'staff')
                    ->pluck('id')
                    ->toArray();
                $tasksQuery->whereIn('assigned_to', array_merge([$user->id], $staffIds));
            }

            $studentIds = $tasksQuery->distinct()->pluck('student_id')->values()->toArray();

            $students = Student::whereIn('id', $studentIds)
                ->select('id', 'first_name', 'last_name')
                ->get()
                ->map(function ($student) use ($today, $user) {
                    $taskCountQuery = CrmTasks::where('student_id', $student->id)
                        ->where('status', '!=', 'completed')
                        ->where('status', '!=', 'cancelled')
                        ->whereDate('scheduled_for', $today);

                    // Apply same filter for count
                    if ($user->is_staff && !$user->is_admin && !$user->is_admin_staff) {
                        $taskCountQuery->where('assigned_to', $user->id);
                    } elseif ($user->is_agent_staff) {
                        $staffIds = User::where('parent_id', $user->parent_id ?? $user->id)
                            ->where('role', 'staff')
                            ->pluck('id')
                            ->toArray();
                        $taskCountQuery->whereIn('assigned_to', array_merge([$user->id], $staffIds));
                    } elseif ($user->is_agent) {
                        $staffIds = User::where('parent_id', $user->id)
                            ->where('role', 'staff')
                            ->pluck('id')
                            ->toArray();
                        $taskCountQuery->whereIn('assigned_to', array_merge([$user->id], $staffIds));
                    }

                    return [
                        'id' => $student->id,
                        'name' => $student->first_name . ' ' . $student->last_name,
                        'tasks_count' => $taskCountQuery->count()
                    ];
                });

            return response()->json([
                'success' => true,
                'students' => $students,
                'total' => count($students)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function authorizeStudent(Student $student): void
    {
        $user = Auth::user();

        if ($user->is_admin) return;
        if ($user->is_admin_staff) return;

        if ($user->is_agent) {
            $staffIds = User::where('parent_id', $user->id)->where('role', 'staff')->pluck('id');
            abort_unless(
                $student->agent_id === $user->id || $staffIds->contains($student->agent_id),
                403,
                'Unauthorized access to student record'
            );
            return;
        }

        if ($user->is_agent_staff) {
            abort_unless(in_array($student->agent_id, [$user->id, $user->parent_id]), 403, 'Unauthorized access');
            return;
        }

        if ($user->is_staff) {
            abort_unless($student->agent_id === $user->id, 403, 'Unauthorized access');
            return;
        }

        abort(403, 'Unauthorized access');
    }

    private function checkAccess(User $user, Student $student): bool
    {
        if ($user->is_admin || $user->is_admin_staff) return true;

        if ($user->is_agent) {
            $staffIds = User::where('parent_id', $user->id)->where('role', 'staff')->pluck('id');
            return ($student->agent_id === $user->id || $staffIds->contains($student->agent_id));
        }

        if ($user->is_agent_staff) {
            return in_array($student->agent_id, [$user->id, $user->parent_id]);
        }

        if ($user->is_staff) {
            return $student->agent_id === $user->id;
        }

        return false;
    }

    private function denyAgents(): void
    {
        abort_if(Auth::user()->is_agent, 403, 'Agents have read-only CRM access.');
    }

    // Add this method to your CrmStudentController
    public function debugTaskVisibility(Student $student)
    {
        $user = Auth::user();

        $allTasks = CrmTasks::where('student_id', $student->id)
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->get();

        $myTasks = CrmTasks::where('student_id', $student->id)
            ->where('assigned_to', $user->id)
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->get();

        $debug = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'is_admin' => $user->is_admin,
                'is_staff' => $user->is_staff,
                'is_admin_staff' => $user->is_admin_staff,
                'is_agent' => $user->is_agent,
            ],
            'student_id' => $student->id,
            'all_tasks_count' => $allTasks->count(),
            'my_tasks_count' => $myTasks->count(),
            'tasks' => $allTasks->map(function ($task) use ($user) {
                return [
                    'id' => $task->id,
                    'subject' => $task->subject,
                    'assigned_to' => $task->assigned_to,
                    'is_mine' => $task->assigned_to == $user->id,
                ];
            })
        ];

        return response()->json($debug);
    }
}
