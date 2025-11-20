<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\University;

class CourseController extends Controller
{
    /**
     * Show list of courses for agents
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
        $courses = $query->orderBy('id', 'DESC')->paginate(20);

        return view('agent.courses.index', compact('courses'));
    }


    /**
     * Show a single course
     */
    public function show($id)
    {
        $course = Course::with('university')->findOrFail($id);

        return view('agent.courses.show', compact('course'));
    }
}
