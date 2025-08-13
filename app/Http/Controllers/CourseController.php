<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\University;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Store new course (from university edit page)
    public function store(Request $request)
    {
        $request->validate([
            'university_id' => 'required|exists:universities,id',
            'course_code' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'nullable|string|max:100',
            'fee' => 'nullable|numeric|min:0',
            'intakes' => 'nullable|string|max:255',
            'moi_requirement' => 'nullable|string|max:255',
        ]);

        Course::create($request->all());

        return redirect()->back()->with('success', 'Course added successfully!');
    }

    // Show edit form for a course
    public function edit($id)
    {
        $course = Course::findOrFail($id);
        $university = $course->university;

        return view('courses.edit', compact('course', 'university'));
    }

    // Update a course
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $request->validate([
            'course_code' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'nullable|string|max:100',
            'fee' => 'nullable|numeric|min:0',
            'intakes' => 'nullable|string|max:255',
            'moi_requirement' => 'nullable|string|max:255',
        ]);

        $course->update($request->all());

        return redirect()->route('universities.edit', $course->university_id)
            ->with('success', 'Course updated successfully!');
    }

    // Delete a course
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $universityId = $course->university_id;
        $course->delete();

        return redirect()->route('universities.edit', $universityId)
            ->with('success', 'Course deleted successfully!');
    }
}
