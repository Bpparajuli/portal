<?php

namespace App\Actions;

use App\Models\Student;
use App\Models\StudentNote;
use App\Models\User;

class UpdateStudentRatingAction
{
    public function execute(Student $student, int $rating, ?User $user = null): Student
    {
        $user = $user ?? auth()->user();
        $oldRating = $student->rating;

        $student->rating = $rating;
        $student->save();

        $ratingText = $rating ?: 'No rating';
        $oldRatingText = $oldRating ?: 'No rating';

        StudentNote::create([
            'student_id' => $student->id,
            'created_by' => $user->id,
            'content' => "Rating updated from '{$oldRatingText}' to '{$ratingText}' by {$user->name}",
            'type' => 'log',
            'title' => 'Rating Updated',
            'is_log' => true,
        ]);

        return $student->fresh();
    }
}
