<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Models\Course;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CourseController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Course::class, 'course');
    }

    public function index(Request $request)
    {
        $search  = $request->input('search');
        $country = $request->input('country');
        $city    = $request->input('city');

        $query = Course::with('university');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%')
                    ->orWhere('course_code', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('university', function ($u) use ($search) {
                        $u->where('name', 'LIKE', '%' . $search . '%')
                            ->orWhere('short_name', 'LIKE', '%' . $search . '%');
                    });
            });
        }

        if ($country) {
            $query->whereHas('university', fn($q) => $q->where('country', $country));
        }

        if ($city) {
            $query->whereHas('university', fn($q) => $q->where('city', $city));
        }

        $courses = $query->orderBy('id', auth()->user()?->is_admin ? 'ASC' : 'DESC')
            ->paginate(auth()->user()?->is_admin ? 50 : 20);

        $countries = University::select('country')->distinct()->pluck('country');
        $courseTypes = Course::distinct()->pluck('course_type');

        return view('shared.courses.index', compact('courses', 'countries', 'courseTypes'));
    }

    public function create(Request $request)
    {
        $universities = University::orderBy('name')->get();
        $selectedUniversity = $request->university_id ? University::find($request->university_id) : null;

        return view('shared.courses.create', compact('universities', 'selectedUniversity'));
    }

    public function store(StoreCourseRequest $request)
    {
        Course::create($request->validated());

        return redirect()->route(auth()->user()->role . '.courses.index')
            ->with('success', 'Course created successfully.');
    }

    public function show(Course $course)
    {
        $course->load('university');
        return view('shared.courses.show', compact('course'));
    }

    public function edit(Course $course)
    {
        $course->load('university');
        $previous = Course::where('id', '<', $course->id)->orderBy('id', 'desc')->first();
        $next = Course::where('id', '>', $course->id)->orderBy('id', 'asc')->first();
        $universities = University::orderBy('name')->get();

        return view('shared.courses.edit', compact('course', 'previous', 'next', 'universities'));
    }

    public function update(StoreCourseRequest $request, Course $course)
    {
        $course->update($request->validated());

        return redirect()->route(auth()->user()->role . '.courses.edit', $course)
            ->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()->route(auth()->user()->role . '.courses.index')
            ->with('success', 'Course deleted successfully.');
    }
}
