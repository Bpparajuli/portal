<?php
namespace App\Services;

use App\Models\Activity;
use App\Models\Application;
use App\Models\ApplicationStatus;
use App\Models\ApplicationStatusHistory;
use App\Models\Student;
use App\Models\User;
use App\Notifications\ApplicationSubmitted;
use App\Notifications\ApplicationStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ApplicationService
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
        private readonly NotificationDispatcher $notifier,
    ) {}

    /**
     * Build a filtered, scoped Application query for listing.
     *
     * ROLE SCOPING:
     * - Admin: all applications.
     * - Agent: applications where agent_id matches.
     * - Agent staff: applications under parent agent.
     * - Staff: applications linked to students they can access.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildFilteredQuery(Request $request, User $user)
    {
        $query = Application::with(['student', 'course.university', 'status', 'agent']);

        if ($user->is_agent) {
            $query->where('agent_id', $user->id);
        } elseif ($user->is_agent_staff) {
            $query->where('agent_id', $user->parent_id);
        } elseif ($user->is_staff && !$user->is_admin && !$user->is_admin_staff) {
            $accessibleStudentIds = Student::accessible()->pluck('id');
            $query->whereIn('student_id', $accessibleStudentIds);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                  ->orWhereHas('student', fn($s) => $s->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%"))
                  ->orWhereHas('course.university', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status_id')) {
            $query->where('application_status_id', $request->status_id);
        }
        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sortField = $request->sort ?? 'created_at';
        $sortDir = $request->direction ?? 'desc';
        $query->orderBy($sortField, $sortDir);

        return $query;
    }

    /**
     * Create a new application for a student.
     *
     * Auto-generates application number, assigns default status and agent.
     * Notifies admins of new submission.
     */
    public function createApplication(Student $student, Request $request): Application
    {
        return DB::transaction(function () use ($student, $request) {
            $defaultStatus = ApplicationStatus::where('is_active', true)->orderBy('sort_order')->first();

            $application = Application::create([
                'student_id'           => $student->id,
                'university_id'        => $request->input('university_id'),
                'course_id'            => $request->input('course_id'),
                'agent_id'             => $student->agent_id ?? Auth::id(),
                'application_status_id'=> $request->input('application_status_id', $defaultStatus?->id),
                'application_number'   => $this->generateApplicationNumber(),
            ]);

            if ($application->application_status_id) {
                ApplicationStatusHistory::create([
                    'application_id' => $application->id,
                    'from_status_id' => null,
                    'to_status_id' => $application->application_status_id,
                    'changed_by' => Auth::id() ?? $application->agent_id,
                ]);
            }

            $admins = User::whereIn('role', ['superadmin', 'admin'])->get();
            Notification::send($admins, new ApplicationSubmitted($application));

            return $application;
        });
    }

    /**
     * Update an application (fields + SOP file upload + status change notification).
     *
     * Logs activity if the status changes and notifies the agent.
     */
    public function updateApplication(Application $application, Request $request): Application
    {
        return DB::transaction(function () use ($application, $request) {
            $originalStatusId = $application->application_status_id;
            $data = $request->validated();

            if ($request->hasFile('sop_file')) {
                $data['sop_file'] = $this->fileUploadService->uploadStudentSOP(
                    $request->file('sop_file'),
                    $application->agent,
                    $application->student,
                    $application->sop_file
                );
            }

            $application->update($data);

            if ($application->application_status_id !== $originalStatusId) {
                ApplicationStatusHistory::create([
                    'application_id' => $application->id,
                    'from_status_id' => $originalStatusId ?: null,
                    'to_status_id' => $application->application_status_id,
                    'changed_by' => Auth::id() ?? $application->agent_id,
                ]);

                Activity::create([
                    'user_id' => Auth::id(),
                    'type' => 'application_status_updated',
                    'description' => "Application {$application->application_number} status updated to {$application->status?->name}",
                    'notifiable_id' => $application->id,
                    'link' => route('admin.applications.show', $application->id),
                ]);

                if ($application->agent) {
                    $application->agent->notify(
                        new ApplicationStatusUpdated($application->fresh(), Auth::user())
                    );
                }
            }

            return $application->fresh();
        });
    }

    /**
     * Generate a unique application number: APP-{YEAR}-{0001+}
     */
    public function generateApplicationNumber(): string
    {
        $prefix = 'APP-' . date('Y') . '-';
        $last = Application::where('application_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')->value('application_number');
        $next = $last ? (int) substr($last, -4) + 1 : 1;
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
