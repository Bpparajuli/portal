<?php

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
    public function __construct(
        private readonly StudentService $studentService,
    ) {}

    public function debugAllTasksForStaff()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $allTasks = CrmTasks::whereDate('scheduled_for', $today)
            ->whereNotNull('student_id')
            ->get();

        $myTasks = CrmTasks::whereDate('scheduled_for', $today)
            ->where('assigned_to', $user->id)
            ->whereNotNull('student_id')
            ->get();

        $studentsWithMyTasks = $myTasks->groupBy('student_id')->map(function ($tasks) {
            return [
                'task_ids' => $tasks->pluck('id'),
                'statuses' => $tasks->pluck('status'),
                'count' => $tasks->count()
            ];
        });

        return response()->json([
            'staff_id' => $user->id,
            'all_tasks_today_count' => $allTasks->count(),
            'my_tasks_count' => $myTasks->count(),
            'students_with_my_tasks' => $studentsWithMyTasks,
            'all_tasks_raw' => $allTasks->map(function ($task) {
                return [
                    'task_id' => $task->id,
                    'student_id' => $task->student_id,
                    'assigned_to' => $task->assigned_to,
                    'status' => $task->status
                ];
            })
        ]);
    }

    private function getTodayTaskNavigation($currentStudentId)
    {
        try {
            $user = Auth::user();
            $today = Carbon::today();

            $isStaff = ($user->role === 'staff');
            $isAdmin = ($user->role === 'admin');

            if ($isStaff) {
                $studentIdsWithTodayTasks = CrmTasks::whereDate('scheduled_for', $today)
                    ->where('assigned_to', $user->id)
                    ->whereNotNull('student_id')
                    ->distinct()
                    ->pluck('student_id')
                    ->toArray();
            } elseif ($isAdmin) {
                $studentIdsWithTodayTasks = CrmTasks::whereDate('scheduled_for', $today)
                    ->whereNotNull('student_id')
                    ->distinct()
                    ->pluck('student_id')
                    ->toArray();

                Log::info('Admin students (all students with tasks today)', [
                    'student_ids' => $studentIdsWithTodayTasks
                ]);
            } else {
                $studentIdsWithTodayTasks = CrmTasks::whereDate('scheduled_for', $today)
                    ->where('assigned_to', $user->id)
                    ->whereNotNull('student_id')
                    ->distinct()
                    ->pluck('student_id')
                    ->toArray();
            }

            if (empty($studentIdsWithTodayTasks)) {
                return null;
            }

            $currentIndex = array_search($currentStudentId, $studentIdsWithTodayTasks);
            if ($currentIndex === false) {
                return null;
            }

            $prevStudentId = $studentIdsWithTodayTasks[$currentIndex - 1] ?? null;
            $nextStudentId = $studentIdsWithTodayTasks[$currentIndex + 1] ?? null;

            $currentStudentTaskCount = CrmTasks::where('student_id', $currentStudentId)
                ->whereDate('scheduled_for', $today)
                ->count();

            $navigation = [
                'prev' => null,
                'next' => null,
                'total' => count($studentIdsWithTodayTasks),
                'current_position' => $currentIndex + 1,
                'current_student_tasks_count' => $currentStudentTaskCount,
            ];

            if ($prevStudentId) {
                $prevStudent = Student::find($prevStudentId);
                if ($prevStudent) {
                    $prevTaskCount = CrmTasks::where('student_id', $prevStudentId)
                        ->whereDate('scheduled_for', $today)
                        ->count();

                    $navigation['prev'] = [
                        'id' => $prevStudent->id,
                        'first_name' => $prevStudent->first_name,
                        'last_name' => $prevStudent->last_name,
                        'tasks_count' => $prevTaskCount
                    ];
                }
            }

            if ($nextStudentId) {
                $nextStudent = Student::find($nextStudentId);
                if ($nextStudent) {
                    $nextTaskCount = CrmTasks::where('student_id', $nextStudentId)
                        ->whereDate('scheduled_for', $today)
                        ->count();

                    $navigation['next'] = [
                        'id' => $nextStudent->id,
                        'first_name' => $nextStudent->first_name,
                        'last_name' => $nextStudent->last_name,
                        'tasks_count' => $nextTaskCount
                    ];
                }
            }

            return $navigation;
        } catch (\Exception $e) {
            Log::error('Navigation error: ' . $e->getMessage());
            return null;
        }
    }

    public function show(Student $student)
    {
        try {
            $this->authorizeStudent($student);
            $user = Auth::user();

            $student->load(['currentStage', 'agent', 'documents', 'latestApplication', 'revenues.creator']);
            $data = $this->studentService->getTaskCategories($student, $user);
            $data['student'] = $student;
            $data['notes'] = $student->notes()->where('is_log', false)->with('creator')->orderBy('is_pinned', 'desc')->orderBy('created_at', 'desc')->get();
            $data['activityLogs'] = $student->notes()->where('is_log', true)->with('creator')->orderBy('created_at', 'desc')->get();
            $data['staffUsers'] = User::where('role', 'staff')->orderBy('name')->get(['id', 'name', 'role', 'business_logo']);
            $data['stages'] = StudentStage::active()->ordered()->get();
            $data['currentStage'] = $student->currentStage;
            $data['assignableUsers'] = $this->studentService->getAssignableUsers($user);
            $data['canEdit'] = !$user->is_agent;
            $data['revenues'] = $student->revenues()->with('creator')->orderBy('transaction_date', 'desc')->paginate(10);
            $data['expectedRevenue'] = $student->expected_revenue ?? 0;
            $data['collectedRevenue'] = $student->received_revenue ?? 0;
            $data['remainingDue'] = max(0, $data['expectedRevenue'] - $data['collectedRevenue']);
            $data['revenuesCollection'] = $data['revenues'] instanceof \Illuminate\Pagination\LengthAwarePaginator ? $data['revenues']->getCollection() : $data['revenues'];
            $data['todayTaskNavigation'] = $this->getTodayTaskNavigation($student->id);

            return view('crm.show', $data);
        } catch (\Exception $e) {
            Log::error('Error in CrmStudentController@show: ' . $e->getMessage());
            return back()->with('error', 'Failed to load student details: ' . $e->getMessage());
        }
    }

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

            $duplicate = $this->studentService->findDuplicate($request);
            if ($duplicate) {
                $duplicateMessage = $this->studentService->getDuplicateMessage($duplicate);
                return redirect()->back()
                    ->with('error', $duplicateMessage)
                    ->with('duplicate_student', $duplicate)
                    ->withInput();
            }

            if (!$request->has('agent_id') || empty($request->agent_id)) {
                $request->merge(['agent_id' => 12]);
            }

            $student = $this->studentService->saveStudent($request);

            $successMessage = sprintf(
                "Student added successfully!\n\nStudent: %s %s\nPhone: %s\nEmail: %s\nSource: %s\nAgent ID: %s",
                $student->first_name,
                $student->last_name,
                $student->phone_number ?? 'Not provided',
                $student->email ?? 'Not provided',
                $student->source ?? 'manual',
                $student->agent_id ?? 'Not assigned'
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
                    ->with('error', 'A student with this email already exists in the system. Please check and try again.')
                    ->withInput();
            }

            Log::error('Database error creating student: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to add student due to database error. Please try again.')
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Failed to create student: ' . $e->getMessage());

            if (str_contains($e->getMessage(), 'Duplicate student found')) {
                return redirect()->back()
                    ->with('error', $e->getMessage())
                    ->withInput();
            }

            return redirect()->back()
                ->with('error', 'Failed to add student: ' . $e->getMessage())
                ->withInput();
        }
    }

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
            $this->studentService->saveStudent($serviceRequest, $student);
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
                ->with('success', 'Student updated successfully!');
        } catch (\Exception $e) {
            Log::error('Mini update error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update student: ' . $e->getMessage());
        }
    }

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

            $updatedStudent = $this->studentService->saveStudent($serviceRequest, $student);
            $this->clearTodayTasksCache();

            $successMessage = sprintf(
                "Student updated successfully!\n\nStudent: %s %s\nPhone: %s\nEmail: %s",
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
                ->with('error', 'Failed to update student: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Student $student)
    {
        try {
            $this->authorizeStudent($student);
            $this->denyAgents();

            $studentName = $student->full_name;
            $this->studentService->deleteStudent($student);
            $this->clearTodayTasksCache();

            return redirect()->route('crm.dashboard')
                ->with('success', "Student '{$studentName}' has been deleted successfully!");
        } catch (\Exception $e) {
            Log::error('Student deletion failed: ' . $e->getMessage());
            return back()
                ->with('error', 'Failed to delete student: ' . $e->getMessage());
        }
    }

    public function saveNote(Request $request, Student $student)
    {
        try {
            $this->authorizeStudent($student);
            $this->denyAgents();

            $validated = $request->validate([
                'content'   => ['required', 'string'],
                'is_pinned' => ['boolean'],
            ]);

            $note = $this->studentService->saveNote($student, $validated['content'], Auth::user());

            if ($request->boolean('is_pinned')) {
                $note->update(['is_pinned' => true]);
            }

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Note saved successfully.']);
            }
            return back()->with('success', 'Note saved successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to save note: ' . $e->getMessage());
        }
    }

    public function changeStage(Request $request, Student $student)
    {
        try {
            $this->authorizeStudent($student);
            $this->denyAgents();

            $validated = $request->validate([
                'new_stage_id' => ['required', 'exists:student_stages,id'],
                'reason'       => ['nullable', 'string', 'max:500'],
            ]);

            $this->studentService->changeStage(
                $student,
                $validated['new_stage_id'],
                $validated['reason'] ?? null,
                Auth::user()
            );

            $successMessage = "Stage updated successfully!";

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $successMessage]);
            }
            return back()->with('success', $successMessage);
        } catch (\InvalidArgumentException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
            }
            return back()->withErrors(['new_stage_id' => $e->getMessage()]);
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to update stage: ' . $e->getMessage());
        }
    }

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

            $rating = $request->rating ?? 0;
            $this->studentService->updateRating($student, $rating, $user);

            return response()->json([
                'success' => true,
                'message' => "Rating updated successfully",
                'rating' => $rating,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getTodayTaskStudentIds()
    {
        try {
            $user = Auth::user();
            $today = Carbon::today();

            $tasksQuery = CrmTasks::where('status', '!=', 'completed')
                ->where('status', '!=', 'cancelled')
                ->whereDate('scheduled_for', $today)
                ->whereNotNull('student_id');

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

    private function clearTodayTasksCache(): void
    {
        Cache::forget('crm_today_tasks');
        Cache::forget('crm_dashboard_stats');
        Cache::forget('crm_task_stats');

        Log::info('CRM cache cleared', [
            'user_id' => Auth::id()
        ]);
    }
}
