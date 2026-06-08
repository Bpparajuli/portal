<?php

namespace App\Actions;

use App\Models\Student;

class ManageStudentTagsAction
{
    public function addTag(Student $student, string $tag): Student
    {
        $tags = $student->tags ?? [];

        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $student->tags = $tags;
            $student->save();
        }

        return $student;
    }

    public function removeTag(Student $student, string $tag): Student
    {
        if ($student->tags) {
            $student->tags = array_values(
                array_filter($student->tags, fn($t) => $t !== $tag)
            );
            $student->save();
        }

        return $student;
    }
}
