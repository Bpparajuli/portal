<?php

namespace App\Actions;

use App\Models\Application;
use App\Models\ApplicationStatus;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateApplicationAction
{
    public function __construct(
        private readonly NotifyUserAction $notifyUserAction,
    ) {}

    public function execute(Student $student, Request $request): Application
    {
        return DB::transaction(function () use ($student, $request) {
            $defaultStatus = ApplicationStatus::where('is_active', true)
                ->orderBy('sort_order')
                ->first();

            $application = Application::create([
                'student_id' => $student->id,
                'university_id' => $request->input('university_id'),
                'course_id' => $request->input('course_id'),
                'agent_id' => $student->agent_id ?? Auth::id(),
                'application_status_id' => $request->input('application_status_id', $defaultStatus?->id),
                'application_number' => $this->generateApplicationNumber(),
            ]);

            $this->notifyUserAction->notifyApplicationSubmitted($application);

            return $application;
        });
    }

    private function generateApplicationNumber(): string
    {
        $prefix = 'APP-' . date('Y') . '-';
        $last = Application::where('application_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('application_number');

        $next = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
