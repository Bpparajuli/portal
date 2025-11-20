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
        // Search filters
        $search  = $request->input('search');
        $country = $request->input('country');
        $city    = $request->input('city');

        // Query
        $query = Course::with('university');

        // Apply Search Filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                // Search in courses table
                $q->where('title', 'LIKE', '%' . $search . '%')
                    ->orWhere('course_code', 'LIKE', '%' . $search . '%')
                    // Search in universities table
                    ->orWhereHas('university', function ($u) use ($search) {
                        $u->where('name', 'LIKE', '%' . $search . '%')
                            ->orWhere('short_name', 'LIKE', '%' . $search . '%');
                    });
            });
        }

        // Filter by Country
        if ($country) {
            $query->whereHas('university', function ($q) use ($country) {
                $q->where('country', $country);
            });
        }

        // Filter by City
        if ($city) {
            $query->whereHas('university', function ($q) use ($city) {
                $q->where('city', $city);
            });
        }

        // IMPORTANT: must use paginate() to fix firstItem() error
        $courses = $query->orderBy('id', 'ASC')->paginate(50);

        return view('admin.courses.index', compact('courses'));
    }


    /**
     * Show the form for creating a new course.
     */
    public function create(Request $request)
    {
        $universities = University::orderBy('name')->get();

        $selectedUniversity = $request->university_id
            ? University::find($request->university_id)
            : null;

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
            'course_link' => 'nullable|string',
            'course_type' => 'required|in:UG,PG,Diploma',
            'academic_requirement' => 'nullable|string',
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
            'course_link' => 'nullable|string',
            'course_type' => 'required|in:UG,PG,Diploma',
            'academic_requirement' => 'nullable|string',
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
