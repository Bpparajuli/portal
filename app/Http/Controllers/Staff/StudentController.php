<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Application;
use App\Models\Course;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['agent', 'applications.status'])->accessible();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $students = $query->orderByDesc('created_at')->paginate(20);

        return view('staff.students.index', compact('students'));
    }

    public function show(Student $student)
    {
        $this->authorizeAccess($student);

        $student->load(['agent', 'documents', 'applications.university', 'applications.course', 'currentStage']);

        return view('staff.students.show', [
            'student' => $student,
            'documentStats' => $student->getDocumentStats(),
        ]);
    }

    public function edit(Student $student)
    {
        $this->authorizeAccess($student);

        return view('staff.students.edit', [
            'student' => $student,
            'agents' => User::agents()->orderBy('business_name')->get(),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $this->authorizeAccess($student);

        $student->update($request->validated());

        return redirect()->route('staff.student.show', $student)
            ->with('success', 'Student updated successfully.');
    }

    public function universities()
    {
        $universities = University::active()->withCount('courses')->orderBy('name')->paginate(20);

        return view('staff.universities.index', compact('universities'));
    }

    public function courses()
    {
        $courses = Course::active()->with('university')->orderBy('title')->paginate(20);

        return view('staff.courses.index', compact('courses'));
    }

    public function applications(Request $request)
    {
        $query = Application::with(['student', 'university', 'status']);

        $user = Auth::user();

        if ($user->is_agent_staff) {
            $query->whereIn('agent_id', [$user->id, $user->parent_id]);
        } elseif ($user->is_staff && !$user->is_admin_staff) {
            $query->where('agent_id', $user->id);
        }

        if ($search = $request->get('search')) {
            $query->whereHas('student', fn($q) => $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%"));
        }

        $applications = $query->latest()->paginate(20);

        return view('staff.applications.index', compact('applications'));
    }

    public function chat()
    {
        return view('shared.chat.index');
    }

    public function notifications()
    {
        $notifications = Auth::user()->notifications()->latest()->paginate(20);

        return view('staff.notifications.index', compact('notifications'));
    }

    private function authorizeAccess(Student $student): void
    {
        $user = Auth::user();

        if ($user->is_admin || $user->is_admin_staff) {
            return;
        }

        $allowedAgentIds = [$user->id];
        if ($user->is_agent_staff) {
            $allowedAgentIds[] = $user->parent_id;
        }

        if (!in_array((int) $student->agent_id, $allowedAgentIds)) {
            abort(403, 'Unauthorized access to this student.');
        }
    }
}
