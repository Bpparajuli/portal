<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;

class CourseController extends Controller
{
    // Only admin access is handled by middleware in web.php

    // Display list of courses
    public function index()
    {
        $courses = Course::all();
        return view('guest.courses.index', compact('courses'));
    }

    // Show a single course
    public function show($id)
    {
        $course = Course::findOrFail($id);
        return view('guest.courses.show', compact('course'));
    }
}
