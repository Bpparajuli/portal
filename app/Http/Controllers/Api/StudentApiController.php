<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentApiController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::with('agent', 'applications.status')
            ->accessible()
            ->paginate(20);
        return response()->json($students);
    }

    public function show(Student $student)
    {
        $student->load('agent', 'documents', 'applications.university', 'applications.course', 'currentStage');
        return response()->json($student);
    }
}
