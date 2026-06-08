<?php

namespace App\Actions;

use App\Models\Student;
use App\Models\StudentNote;
use App\Models\User;

class SaveStudentNoteAction
{
    public function execute(Student $student, string $content, ?User $user = null): StudentNote
    {
        $user = $user ?? auth()->user();

        $note = StudentNote::create([
            'student_id' => $student->id,
            'created_by' => $user->id,
            'content' => $content,
            'type' => 'internal',
        ]);

        StudentNote::create([
            'student_id' => $student->id,
            'created_by' => $user->id,
            'content' => "Note added by {$user->name}",
            'type' => 'log',
            'title' => 'Note Added',
            'is_log' => true,
        ]);

        return $note;
    }
}
