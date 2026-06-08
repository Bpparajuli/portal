<?php

namespace App\Services;

use App\Models\CrmTasks;
use App\Models\Student;
use App\Models\StudentStage;
use App\Models\User;

class StudentDashboardService
{
    public function getDashboardData(Student $student, User $user): array
    {
        $student->load([
            'currentStage',
            'agent',
            'documents',
            'latestApplication',
            'revenues.creator',
        ]);

        $taskCategories = $this->getTaskCategories($student, $user);
        $revenueSummary = $this->getRevenueSummary($student);

        $dueTasks = $taskCategories['dueTasks'];
        $todayTasks = $taskCategories['todayTasks'];
        $plannedTasks = $taskCategories['plannedTasks'];
        $completedTasks = $taskCategories['completedTasks'];
        $activityHistory = $taskCategories['activityHistory'];

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

        $assignableUsers = $this->getAssignableUsers($user);

        $revenues = $student->revenues()
            ->with('creator')
            ->orderBy('transaction_date', 'desc')
            ->paginate(10);

        $canEdit = !$user->is_agent;

        $revenuesCollection = $revenues instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? $revenues->getCollection()
            : $revenues;

        $expectedRevenue = $revenueSummary['expectedRevenue'];
        $collectedRevenue = $revenueSummary['collectedRevenue'];
        $remainingDue = $revenueSummary['remainingDue'];

        return compact(
            'student', 'dueTasks', 'todayTasks', 'plannedTasks',
            'activityHistory', 'completedTasks', 'notes', 'activityLogs',
            'stages', 'currentStage', 'assignableUsers', 'canEdit',
            'staffUsers', 'revenues', 'remainingDue', 'revenuesCollection',
            'expectedRevenue', 'collectedRevenue'
        );
    }

    public function getTaskCategories(Student $student, User $user): array
    {
        $dueTasksQuery = CrmTasks::where('student_id', $student->id)
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('scheduled_for')
            ->where('scheduled_for', '<', now()->startOfDay());
        $this->applyTaskRoleFilter($dueTasksQuery, $user);
        $dueTasks = $dueTasksQuery->orderBy('scheduled_for', 'asc')->get();

        $todayTasksQuery = CrmTasks::where('student_id', $student->id)
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->whereDate('scheduled_for', now()->toDateString());
        $this->applyTaskRoleFilter($todayTasksQuery, $user);
        $todayTasks = $todayTasksQuery->orderBy('scheduled_for', 'asc')->get();

        $plannedTasksQuery = CrmTasks::where('student_id', $student->id)
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->whereDate('scheduled_for', '>', now()->toDateString());
        $this->applyTaskRoleFilter($plannedTasksQuery, $user);
        $plannedTasks = $plannedTasksQuery->orderBy('scheduled_for', 'asc')->get();

        $completedTasksQuery = CrmTasks::where('student_id', $student->id)
            ->whereIn('status', ['completed', 'cancelled']);
        $this->applyTaskRoleFilter($completedTasksQuery, $user);
        $completedTasks = $completedTasksQuery->orderBy('completed_at', 'desc')->paginate(10);

        $activityHistory = CrmTasks::where('student_id', $student->id)
            ->where('status', 'completed')
            ->with('assignee', 'creator')
            ->latest('completed_at')
            ->paginate(10);

        return compact('dueTasks', 'todayTasks', 'plannedTasks', 'completedTasks', 'activityHistory');
    }

    public function getRevenueSummary(Student $student): array
    {
        $expectedRevenue = $student->expected_revenue ?? 0;
        $collectedRevenue = $student->received_revenue ?? 0;
        $remainingDue = max(0, $expectedRevenue - $collectedRevenue);

        return compact('expectedRevenue', 'collectedRevenue', 'remainingDue');
    }

    private function applyTaskRoleFilter($query, User $user): void
    {
        if ($user->is_staff && !$user->is_admin && !$user->is_admin_staff) {
            $query->where('assigned_to', $user->id);
        } elseif ($user->is_agent_staff) {
            $staffIds = User::where('parent_id', $user->parent_id ?? $user->id)
                ->where('role', 'staff')
                ->pluck('id')
                ->toArray();
            $query->whereIn('assigned_to', array_merge([$user->id], $staffIds));
        } elseif ($user->is_agent) {
            $staffIds = User::where('parent_id', $user->id)
                ->where('role', 'staff')
                ->pluck('id')
                ->toArray();
            $query->whereIn('assigned_to', array_merge([$user->id], $staffIds));
        }
    }

    private function getAssignableUsers(User $user): \Illuminate\Support\Collection
    {
        if ($user->is_admin || $user->is_admin_staff) {
            return User::where(function ($q) {
                $q->where('role', 'staff');
            })->select('id', 'name', 'role', 'parent_id')
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
}
