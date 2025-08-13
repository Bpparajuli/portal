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

    /**
     * Show form to add a new course.
     */
    public function create()
    {
        $universities = University::orderBy('name')->get();
        return view('courses.create', compact('universities'));
    }

    /**
     * Store a new course.
     */
    public function store(Request $request)
    {
        $request->validate([
            'university_id' => 'required|exists:universities,id',
            'course_code' => 'nullable|string|max:50',
            'title' => 'required|string|max:255',
            'course_type' => 'required|in:ug,pg,diploma',
            'description' => 'nullable|string',
            'duration' => 'nullable|string|max:100',
            'fee' => 'nullable|numeric|min:0',
            'intakes' => 'nullable|string|max:50',
            'ielts_pte_other_languages' => 'nullable|string|max:100',
            'moi_requirement' => 'nullable|string|max:255',
            'application_fee' => 'nullable|numeric|min:0',
        ]);

        Course::create($request->only([
            'university_id',
            'course_code',
            'title',
            'course_type',
            'description',
            'duration',
            'fee',
            'intakes',
            'ielts_pte_other_languages',
            'moi_requirement',
            'application_fee'
        ]));

        return redirect()->route('courses.create')->with('success', 'Course added successfully!');
    }

    /**
     * Show form to edit a course.
     */
    public function edit($id)
    {
        $course = Course::findOrFail($id);
        $university = $course->university; // assuming you have a relationship in Course model

        return view('courses.edit', compact('course', 'university'));
    }

    /**
     * Update a course.
     */
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $request->validate([
            'university_id' => 'required|exists:universities,id',
            'course_code' => 'nullable|string|max:50',
            'title' => 'required|string|max:255',
            'course_type' => 'required|in:ug,pg,diploma',
            'description' => 'nullable|string',
            'duration' => 'nullable|string|max:100',
            'fee' => 'nullable|numeric|min:0',
            'intakes' => 'nullable|string|max:50',
            'ielts_pte_other_languages' => 'nullable|string|max:100',
            'moi_requirement' => 'nullable|string|max:255',
            'application_fee' => 'nullable|numeric|min:0',
        ]);

        $course->update($request->only([
            'university_id',
            'course_code',
            'title',
            'course_type',
            'description',
            'duration',
            'fee',
            'intakes',
            'ielts_pte_other_languages',
            'moi_requirement',
            'application_fee'
        ]));

        return redirect()->route('universities.edit', $course->university_id)
            ->with('success', 'Course updated successfully!');
    }

    /**
     * Delete a course.
     */
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $universityId = $course->university_id;
        $course->delete();

        return redirect()->route('universities.edit', $universityId)
            ->with('success', 'Course deleted successfully!');
    }
}
