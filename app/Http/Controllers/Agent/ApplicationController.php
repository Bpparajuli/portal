<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index()
    {
        $applications = Application::with(['student', 'university', 'course'])->latest()->get();
        return view('agent.applications.index', compact('applications'));
    }

    public function create()
    {
        return view('agent.applications.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'university_id' => 'required',
            'course_id' => 'required'
        ]);

        Application::create($request->all());

        return redirect()->route('agent.applications.index')->with('success', 'Application created.');
    }

    public function edit(Application $application)
    {
        return view('agent.applications.edit', compact('application'));
    }

    public function update(Request $request, Application $application)
    {
        $request->validate([
            'status' => 'required'
        ]);

        $application->update($request->all());

        return redirect()->route('agent.applications.index')->with('success', 'Application updated.');
    }
}
