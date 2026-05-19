<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\University;
use App\Models\Course;
use Illuminate\Http\Request;

class UniversityController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $countries = University::select('country')->distinct()->pluck('country');

        $query = University::with('courses');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('short_name', 'LIKE', "%{$search}%")
                    ->orWhere('country', 'LIKE', "%{$search}%")
                    ->orWhere('city', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhereHas('courses', function ($c) use ($search) {
                        $c->where('course_code', 'LIKE', "%{$search}%")
                            ->orWhere('title', 'LIKE', "%{$search}%")
                            ->orWhere('course_type', 'LIKE', "%{$search}%")
                            ->orWhere('description', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('country')) $query->where('country', $request->country);
        if ($request->filled('city')) $query->where('city', $request->city);
        if ($request->filled('university_id')) $query->where('id', $request->university_id);
        if ($request->filled('course_id')) $query->whereHas('courses', fn($q) => $q->where('id', $request->course_id));

        $universities = $query->paginate(15)->withQueryString();

        return view('guest.universities.index', compact('universities', 'countries'));
    }

    public function show(University $university)
    {
        $university->load('courses');
        return view('guest.universities.show', compact('university'));
    }

    // -------- AJAX Dropdowns --------

    public function getCities($country)
    {
        $cities = University::where('country', $country)
            ->select('city')->distinct()->pluck('city');

        return response()->json($cities);
    }

    public function getUniversities($city)
    {
        $unis = University::where('city', $city)
            ->select('id', 'name')->get();

        return response()->json($unis);
    }

    public function getCourseTypes($universityId)
    {
        $types = Course::where('university_id', $universityId)
            ->distinct()
            ->pluck('course_type');

        return response()->json($types);
    }

    public function getCoursesByType($universityId, $courseType)
    {
        $courses = Course::where('university_id', $universityId)
            ->where('course_type', $courseType)
            ->select('id', 'title')
            ->get();

        return response()->json($courses);
    }
}
