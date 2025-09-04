<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\University;
use App\Models\Course;

class UniversityController extends Controller
{
    public function index(Request $request)
    {
        $countries = University::select('country')->distinct()->pluck('country');

        $query = University::with('courses');

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%")
                    ->orWhereHas('courses', fn($qc) => $qc->where('title', 'like', "%$keyword%"));
            });
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('university_id')) {
            $query->where('id', $request->university_id);
        }

        if ($request->filled('course_id')) {
            $query->whereHas('courses', fn($q) => $q->where('id', $request->course_id));
        }

        $universities = $query->paginate(10)->withQueryString();

        return view('guest.universities.index', compact('countries', 'universities'));
    }
    public function show(University $university)
    {
        $university->load('courses');
        return view('guest.universities.show', compact('university'));
    }

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

    public function getCourses($universityId)
    {
        $courses = Course::where('university_id', $universityId)
            ->select('id', 'title')->get();
        return response()->json($courses);
    }
}
