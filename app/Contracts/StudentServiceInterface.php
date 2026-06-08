<?php
namespace App\Contracts;

use App\Models\Student;
use Illuminate\Http\Request;

interface StudentServiceInterface
{
    public function findDuplicate(Request $request, ?Student $student = null): ?Student;
    public function getDuplicateMessage(Student $existingStudent): string;
    public function saveStudent(Request $request, ?Student $student = null): Student;
    public function ensureStudentFolderExists(Student $student): void;
    public function deleteStudent(Student $student): void;
}
