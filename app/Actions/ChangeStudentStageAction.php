<?php

namespace App\Actions;

use App\Models\Student;
use App\Models\StudentNote;
use App\Models\StudentStage;
use App\Models\User;

class ChangeStudentStageAction
{
    public function execute(Student $student, int $stageId, ?string $reason = null, ?User $user = null): Student
    {
        $user = $user ?? auth()->user();
        $currentStage = $student->currentStage;

        if ($currentStage && !$currentStage->canMoveToStage($stageId)) {
            throw new \InvalidArgumentException('This transition is not allowed from the current stage.');
        }

        $oldStageName = $currentStage?->name ?? 'None';
        $student->moveToStage($stageId, $reason);
        $newStage = StudentStage::find($stageId);

        StudentNote::create([
            'student_id' => $student->id,
            'created_by' => $user->id,
            'content' => "Stage changed from '{$oldStageName}' to '{$newStage->name}'" . ($reason ? " Reason: {$reason}" : ""),
            'type' => 'log',
            'title' => 'Stage Changed',
            'is_log' => true,
        ]);

        return $student->fresh();
    }
}
