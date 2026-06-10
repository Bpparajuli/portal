<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly CourseService $courseService
    ) {
        $this->authorizeResource(Course::class, 'course');
    }

    public function index(Request $request)
    {
        $courses = $this->courseService->getFilteredList($request);
        $filters = $this->courseService->getIndexFilters();

        return view('shared.courses.index', array_merge(
            compact('courses'),
            $filters
        ));
    }

    public function create(Request $request)
    {
        return view('shared.courses.create', $this->courseService->getCreateData($request->university_id));
    }

    public function store(StoreCourseRequest $request)
    {
        $this->courseService->store($request->validated());

        return redirect()->route(auth()->user()->role . '.courses.index')
            ->with('success', 'Course created successfully.');
    }

    public function show(Course $course)
    {
        return view('shared.courses.show', [
            'course' => $this->courseService->loadForShow($course),
        ]);
    }

    public function edit(Course $course)
    {
        return view('shared.courses.edit', $this->courseService->loadForEdit($course));
    }

    public function update(StoreCourseRequest $request, Course $course)
    {
        $this->courseService->update($course, $request->validated());

        return redirect()->route(auth()->user()->role . '.courses.edit', $course)
            ->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $this->courseService->destroy($course);

        return redirect()->route(auth()->user()->role . '.courses.index')
            ->with('success', 'Course deleted successfully.');
    }
}
