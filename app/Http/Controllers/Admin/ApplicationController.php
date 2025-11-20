<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Application;
use App\Models\Student;
use App\Models\University;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Notifications\ApplicationMessageAdded;
use App\Notifications\ApplicationStatusUpdated;

class ApplicationController extends Controller
{
    /**
     * Display a listing of the applications.
     */
    public function index(Request $request)
    {
        $query = Application::with(['student', 'university', 'course', 'agent'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                // Search by student first_name or last_name
                $q->whereHas('student', function ($q2) use ($search) {
                    $q2->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                })
                    // Search by course name/title
                    ->orWhereHas('course', function ($q3) use ($search) {
                        $q3->where('title', 'like', "%{$search}%"); // replace 'title' with your course column
                    })
                    // Search by university name
                    ->orWhereHas('university', function ($q4) use ($search) {
                        $q4->where('name', 'like', "%{$search}%"); // replace 'name' with your university column
                    });
            });
        }

        $applications = $query->paginate(20)->withQueryString();

        return view('admin.applications.index', compact('applications'));
    }


    /**
     * Show the form for creating a new application.
     */
    public function create(Request $request)
    {
        $selectedStudent = null;
        $selectedUniversityId = $request->query('university_id');
        $selectedCourseId = $request->query('course_id');

        if ($request->has('student_id')) {
            $selectedStudent = Student::find($request->student_id);
        }

        $students = Student::whereHas('documents')->get();
        $universities = University::all();
        $courses = Course::all();

        return view('admin.applications.create', compact(
            'students',
            'universities',
            'courses',
            'selectedStudent',
            'selectedUniversityId',
            'selectedCourseId'
        ));
    }

    /**
     * Store a newly created application in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'university_id' => 'required|exists:universities,id',
            'course_id' => 'nullable|exists:courses,id',
            'sop_file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:15360',
        ]);

        $student = Student::findOrFail($request->student_id);

        $application = new Application();
        $application->student_id = $student->id;
        $application->agent_id = $student->agent_id;
        $application->university_id = $request->university_id;
        $application->course_id = $request->course_id;
        $application->application_status = 'Application started';

        // Store SOP file
        if ($request->hasFile('sop_file')) {
            $file = $request->file('sop_file');
            $folder = "agents/{$student->agent_id}/{$student->id}/applications";
            $filename = 'sop_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($folder, $filename, 'public');
            $application->sop_file = $path;
        }

        $application->save();

        return redirect()->route('admin.applications.index')
            ->with('success', 'Application created successfully.');
    }

    /**
     * Display the specified application.
     */
    public function show(Application $application)
    {
        $application->load(['student', 'university', 'course', 'documents', 'messages.user']);

        $status = Application::STATUSES;
        $statusColors = Application::STATUS_COLORS;

        return view('admin.applications.show', compact('application', 'status', 'statusColors'));
    }


    /**
     * Show the form for editing the specified application.
     */
    public function edit($id)
    {
        $application = Application::with(['student', 'university', 'course'])->findOrFail($id);
        $universities = University::all();
        $courses = Course::all();

        return view('admin.applications.edit', compact('application', 'universities', 'courses'));
    }

    /**
     * Update the specified application.
     */
    public function update(Request $request, $id)
    {
        $application = Application::findOrFail($id);

        $request->validate([
            'university_id' => 'required|exists:universities,id',
            'course_id' => 'nullable|exists:courses,id',
            'application_status' => 'required|string|in:' . implode(',', Application::STATUSES),
            'sop_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:15360',
        ]);

        $oldStatus = $application->application_status;

        $application->update([
            'university_id' => $request->university_id,
            'course_id' => $request->course_id,
            'application_status' => $request->application_status,
        ]);

        // Handle SOP file update
        if ($request->hasFile('sop_file')) {
            $student = $application->student;
            $file = $request->file('sop_file');
            $folder = "agents/{$student->agent_id}/{$student->id}/applications";
            $filename = 'sop_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($folder, $filename, 'public');

            if ($application->sop_file && Storage::disk('public')->exists($application->sop_file)) {
                Storage::disk('public')->delete($application->sop_file);
            }

            $application->update(['sop_file' => $path]);
        }

        // Log & Notify if status changed
        if ($oldStatus !== $request->application_status) {
            $student = $application->student;
            $agentId = $student->agent_id;
            User::notifyAgent($agentId, new ApplicationStatusUpdated($application, Auth::user()));
        }

        return redirect()->route('admin.applications.index')
            ->with('success', 'Application updated successfully.');
    }

    /**
     * Add message to application (for communication).
     */
    public function addMessage(Request $request, Application $application)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $user = Auth::user();
        $userType = $user->is_admin ? 'admin' : 'agent';

        $message = $application->messages()->create([
            'user_id' => $user->id,
            'type' => $userType,
            'message' => $request->message,
        ]);

        if ($userType === 'agent') {
            User::notifyAdmins(new ApplicationMessageAdded($application, $message));
        } else {
            $application->agent?->notify(new ApplicationMessageAdded($application, $message));
        }


        return back()->with('success', 'Message added and notification sent.');
    }

    /**
     * Remove the specified application from storage.
     */
    public function destroy(Application $application)
    {
        if ($application->sop_file && Storage::disk('public')->exists($application->sop_file)) {
            Storage::disk('public')->delete($application->sop_file);
        }

        $application->delete();

        return redirect()->route('admin.applications.index')
            ->with('success', 'Application deleted successfully.');
    }

    /** Get courses by university (AJAX) */
    public function getCourses($universityId)
    {
        $courses = Course::where('university_id', $universityId)
            ->select('id', 'title')
            ->get();

        return response()->json($courses);
    }


    public function forStudent(Student $student)
    {
        // Load applications with related models
        $applications = $student->applications()->with('university', 'course')->latest()->get();

        return view('admin.applications.for_student', compact('student', 'applications'));
    }
}
