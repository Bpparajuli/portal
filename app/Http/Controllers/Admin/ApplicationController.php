<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\User;
use App\Notifications\ApplicationStatusUpdatedNotification;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index()
    {
        $applications = Application::with('student', 'university', 'course')->latest()->get();
        return view('admin.applications.index', compact('applications'));
    }

    public function show(Application $application)
    {
        return view('admin.applications.show', compact('application'));
    }

    public function updateStatus(Request $request, Application $application)
    {
        $request->validate(['status' => 'required|string']);
        $application->update(['application_status' => $request->status]);

        // Notify the agent who created this application
        $agent = $application->student->agent;
        $agent->notify(new ApplicationStatusUpdatedNotification($application));

        return back()->with('success', 'Application status updated successfully.');
    }
}
