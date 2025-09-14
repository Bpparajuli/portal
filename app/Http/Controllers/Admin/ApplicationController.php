<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;
use App\Models\University;
use App\Models\Activity;
use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    public function index()
    {
        $applications = Application::with(['student', 'university', 'course'])->latest()->get();
        return view('admin.applications.index', compact('applications'));
    }

    public function create()
    {
        return view('admin.applications.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'university_id' => 'required',
            'course_id' => 'required'
        ]);

        Application::create($request->all());

        return redirect()->route('admin.applications.index')->with('success', 'Application created.');
    }

    public function edit(Application $application)
    {
        $universities = University::all();
        $students = Student::all();

        return view('admin.applications.edit', compact('application'));
    }

    public function update(Request $request, Application $application)
    {
        $request->validate([
            'status' => 'required'
        ]);

        $application->update($request->all());

        return redirect()->route('admin.applications.index')->with('success', 'Application updated.');
    }

    public function destroy(Application $application)
    {
        $application->delete();
        return redirect()->route('admin.applications.index')->with('success', 'Application deleted.');
    }

    public function show(Application $application)
    {
        // No agent check needed, admin can view all applications.

        return view('admin.applications.show', compact('application'));
    }
}
