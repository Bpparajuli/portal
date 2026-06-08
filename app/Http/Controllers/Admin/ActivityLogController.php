<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('user', 'student');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->latest()->paginate(50)->withQueryString();

        $types = Activity::select('type')->distinct()->pluck('type');

        return view('admin.activities.index', compact('activities', 'types'));
    }

    public function show(Activity $activity)
    {
        $activity->load('user', 'student', 'application', 'document');
        return view('admin.activities.show', compact('activity'));
    }

    public function destroy(Activity $activity)
    {
        $activity->delete();
        return redirect()->back()->with('success', 'Activity deleted successfully.');
    }

    public function clearAll()
    {
        Activity::query()->delete();
        return redirect()->route('admin.activities.index')->with('success', 'All activities cleared.');
    }
}
