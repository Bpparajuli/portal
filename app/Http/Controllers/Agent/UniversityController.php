<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\University;
use Illuminate\Http\Request;
use App\Models\Course;

class UniversityController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $countries = University::select('country')->distinct()->pluck('country');

        // BASE QUERY: University with courses
        $query = University::with('courses');

        // ---------------------------------------
        //  SEARCH FILTER (University + Course)
        // ---------------------------------------
        if ($search) {
            $query->where(function ($q) use ($search) {

                // UNIVERSITY fields
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('short_name', 'LIKE', "%{$search}%")
                    ->orWhere('country', 'LIKE', "%{$search}%")
                    ->orWhere('city', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")

                    // COURSE fields (search inside related course table)
                    ->orWhereHas('courses', function ($c) use ($search) {
                        $c->where('course_code', 'LIKE', "%{$search}%")
                            ->orWhere('title', 'LIKE', "%{$search}%")
                            ->orWhere('course_type', 'LIKE', "%{$search}%")
                            ->orWhere('description', 'LIKE', "%{$search}%");
                    });
            });
        }

        // ---------------------------------------
        //  COUNTRY FILTER
        // ---------------------------------------
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        // ---------------------------------------
        //  CITY FILTER
        // ---------------------------------------
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        // ---------------------------------------
        //  SPECIFIC UNIVERSITY FILTER
        // ---------------------------------------
        if ($request->filled('university_id')) {
            $query->where('id', $request->university_id);
        }

        // ---------------------------------------
        //  FILTER UNIVERSITIES THAT HAVE A SPECIFIC COURSE
        // ---------------------------------------
        if ($request->filled('course_id')) {
            $query->whereHas('courses', fn($q) => $q->where('id', $request->course_id));
        }

        // ---------------------------------------
        //  PAGINATION
        // ---------------------------------------
        $universities = $query->paginate(16)->withQueryString();

        return view('agent.universities.index', compact('universities', 'countries'));
    }

    public function show(University $university)
    {
        $university->load('courses');
        return view('agent.universities.show', compact('university'));
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
