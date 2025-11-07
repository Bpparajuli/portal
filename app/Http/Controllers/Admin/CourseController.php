<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Course;
use App\Models\University;

class CourseController extends Controller
{
    /**
     * Display a listing of courses with optional filters.
     */
    public function index(Request $request)
    {
        $query = Course::with('university');

        // Search filters
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('course_code', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('country')) {
            $query->whereHas('university', function ($q) use ($request) {
                $q->where('country', 'like', '%' . $request->country . '%');
            });
        }

        if ($request->filled('city')) {
            $query->whereHas('university', function ($q) use ($request) {
                $q->where('city', 'like', '%' . $request->city . '%');
            });
        }

        if ($request->filled('university_id')) {
            $query->where('university_id', $request->university_id);
        }

        $courses = $query->orderBy('created_at', 'desc')->paginate(25);
        $universities = University::orderBy('name')->get();

        return view('admin.courses.index', compact('courses', 'universities'));
    }

    /**
     * Show the form for creating a new course.
     */
    public function create($university_id = null)
    {
        $universities = University::orderBy('name')->get();
        $selectedUniversity = $university_id ? University::find($university_id) : null;

        return view('admin.courses.create', compact('universities', 'selectedUniversity'));
    }

    /**
     * Store a newly created course in the database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'university_id' => 'required|exists:universities,id',
            'course_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courses')->where(function ($query) use ($request) {
                    return $query->where('university_id', $request->university_id);
                }),
            ],
            'title' => 'required|string|max:255',
            'course_type' => 'required|in:UG,PG,Diploma',
            'description' => 'nullable|string',
            'duration' => 'nullable|string|max:255',
            'fee' => 'nullable|string|max:255',
            'intakes' => 'required|string|max:255',
            'ielts_pte_other_languages' => 'nullable|string|max:255',
            'moi_requirement' => 'required|in:Yes,No',
            'application_fee' => 'nullable|string|max:255',
            'scholarships' => 'nullable|string|max:255',
        ]);

        Course::create($request->all());

        return redirect()->route('admin.courses.index')->with('success', 'Course created successfully.');
    }

    /**
     * Show the form for editing a course.
     */
    public function edit($id)
    {
        $course = Course::with('university')->findOrFail($id);
        return view('admin.courses.edit', compact('course'));
    }

    /**
     * Update the specified course in the database.
     */
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $request->validate([
            'university_id' => 'required|exists:universities,id',
            'course_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courses')
                    ->where(function ($query) use ($request) {
                        return $query->where('university_id', $request->university_id);
                    })
                    ->ignore($id),
            ],
            'title' => 'required|string|max:255',
            'course_type' => 'required|in:UG,PG,Diploma',
            'description' => 'nullable|string',
            'duration' => 'nullable|string|max:255',
            'fee' => 'nullable|string|max:255',
            'intakes' => 'required|string|max:255',
            'ielts_pte_other_languages' => 'nullable|string|max:255',
            'moi_requirement' => 'required|in:Yes,No',
            'application_fee' => 'nullable|string|max:255',
            'scholarships' => 'nullable|string|max:255',
        ]);

        $course->update($request->all());

        return redirect()->route('admin.courses.index')->with('success', 'Course updated successfully.');
    }

    /**
     * Remove the specified course from storage.
     */
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', 'Course deleted successfully.');
    }
    public function show($id)
    {
        $course = Course::with('university')->findOrFail($id);
        $university = $course->university;

        return view('admin.courses.show', compact('course', 'university'));
    }
}
