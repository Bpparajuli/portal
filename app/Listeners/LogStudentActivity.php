<?php

namespace App\Listeners;

use App\Actions\LogActivityAction;
use App\Events\StudentCreated;
use Illuminate\Support\Facades\Auth;

class LogStudentActivity
{
    public function handle(StudentCreated $event): void
    {
        app(LogActivityAction::class)->execute(
            'student_created',
            "Student {$event->student->full_name} was created",
            Auth::user(),
            $event->student->id,
            route('admin.students.show', $event->student)
        );
    }
}
