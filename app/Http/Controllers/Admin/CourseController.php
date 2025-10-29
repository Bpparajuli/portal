<?php

namespace App\Http\Controllers\Admin;

use App\Models\University; // Make sure you have this line
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;

class CourseController extends Controller
{
    // Only admin access is handled by middleware in web.php

    public function index()
    {
        $courses = Course::all();
        return view('admin.courses.index', compact('courses'));
    }

    // Show create course form

    public function create()
    {
        // Fetch all universities from the database
        $universities = University::all();

        // Pass the $universities variable to the view
        return view('admin.courses.create', compact('universities'));
    }

    // Store new course
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:courses,name',
            'description' => 'nullable|string',
        ]);

        Course::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.admin.courses.index')->with('success', 'Course created successfully.');
    }

    // Show a single course
    public function show($id)
    {
        $course = Course::findOrFail($id);
        return view('admin.courses.show', compact('course'));
    }

    // Show edit form
    public function edit($id)
    {
        $course = Course::findOrFail($id);
        return view('admin.courses.edit', compact('course'));
    }

    // Update course
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:courses,name,' . $id,
            'description' => 'nullable|string',
        ]);

        $course->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.admin.courses.index')->with('success', 'Course updated successfully.');
    }

    // Delete course
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return redirect()->route('admin.admin.courses.index')->with('success', 'Course deleted successfully.');
    }
}
