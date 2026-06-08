<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\University;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $countries = University::select('country')->distinct()->pluck('country');

        $query = Course::with('university');

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%$keyword%")
                    ->orWhere('course_code', 'like', "%$keyword%")
                    ->orWhere('course_type', 'like', "%$keyword%");
            });
        }

        if ($request->filled('country')) {
            $country = $request->country;
            $query->whereHas('university', fn($q) => $q->where('country', $country));
        }

        if ($request->filled('university_id')) {
            $query->where('university_id', $request->university_id);
        }

        if ($request->filled('course_type')) {
            $query->where('course_type', $request->course_type);
        }

        $courseTypes = Course::distinct()->pluck('course_type');

        $courses = $query->paginate(12)->withQueryString();

        return view('guest.courses.index', compact('courses', 'countries', 'courseTypes'));
    }

    public function show($id)
    {
        $course = Course::with('university')->findOrFail($id);
        return view('guest.courses.show', compact('course'));
    }
}
