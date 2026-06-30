<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    private function workActivityTypes(): array
    {
        return [
            'task_completed', 'task_cancelled', 'task_created',
            'student_added', 'student_updated', 'student_deleted',
            'stage_changed',
            'document_uploaded', 'document_deleted',
            'application_submitted', 'application_status_updated', 'application_withdrawn', 'application_message_added',
            'revenue_added', 'revenue_updated', 'revenue_deleted',
            'user_registered', 'user_approved', 'staff_created', 'profile_updated', 'enquiry_created',
            'crm_task_assigned',
        ];
    }

    public function index(Request $request)
    {
        $tab = $request->input('tab', 'all');
        $workTypes = $this->workActivityTypes();

        // Shared filter data
        $types = Activity::select('type')->distinct()->whereIn('type', $workTypes)->pluck('type');
        $userIds = Activity::select('user_id')->distinct()->whereNotNull('user_id')->whereIn('type', $workTypes)->pluck('user_id');
        $users = User::whereIn('id', $userIds)->orderBy('name')->get(['id', 'name']);

        if ($tab === 'users') {
            $staffIds = User::where('role', 'staff')->pluck('id');
            $perPage = $request->integer('per_page', 20);
            $userActivities = Activity::with('user', 'student')
                ->selectRaw('user_id, COUNT(*) as total_count, MAX(created_at) as last_activity')
                ->whereNotNull('user_id')
                ->whereIn('type', $workTypes)
                ->whereIntegerInRaw('user_id', $staffIds)
                ->groupBy('user_id')
                ->orderByDesc('last_activity')
                ->paginate($perPage)
                ->through(function ($item) {
                    $item->recent = Activity::with('student')
                        ->where('user_id', $item->user_id)
                        ->whereIn('type', $this->workActivityTypes())
                        ->latest()
                        ->take(10)
                        ->get();
                    return $item;
                });

            $activities = collect();

            return view('admin.activities', compact('activities', 'types', 'users', 'tab', 'userActivities'));
        }

        $query = Activity::with('user', 'student', 'application')->whereIn('type', $workTypes);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->latest()->paginate(50)->withQueryString();

        return view('admin.activities', compact('activities', 'types', 'users', 'tab'));
    }

    public function show(Activity $activity)
    {
        return redirect()->route('admin.activities.index');
    }

    public function destroy(Activity $activity)
    {
        $activity->delete();
        return redirect()->back()->with('success', 'Activity deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'No activities selected.'], 400);
            }
            return redirect()->back()->with('error', 'No activities selected.');
        }
        Activity::whereIn('id', $ids)->delete();
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => count($ids) . ' activities deleted.']);
        }
        return redirect()->back()->with('success', count($ids) . ' activities deleted.');
    }
}
