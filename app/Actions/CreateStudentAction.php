<?php

namespace App\Actions;

use App\Contracts\FileUploadServiceInterface;
use App\Models\Student;
use App\Models\StudentStage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateStudentAction
{
    public function __construct(
        private readonly NotifyUserAction $notifyUserAction,
        private readonly FileUploadServiceInterface $fileUploadService,
    ) {}

    public function execute(Request $request): Student
    {
        return DB::transaction(function () use ($request) {
            $data = $this->prepareData($request);

            $student = Student::create($data);

            if ($request->hasFile('students_photo')) {
                $this->handlePhotoUpload($request, $student);
            }

            $this->notifyUserAction->notifyStudentAdded($student, Auth::user());

            return $student->fresh();
        });
    }

    private function prepareData(Request $request): array
    {
        $data = $request->validated();

        $data['agent_id'] = $this->resolveAgentId($request);

        if (empty($data['current_stage_id'])) {
            $initialStage = StudentStage::where('stage_order', 1)->first();
            $data['current_stage_id'] = $initialStage?->id;
        }

        if (empty($data['source'])) {
            $data['source'] = 'manual';
        }

        $data['created_by'] = Auth::id();

        return $data;
    }

    private function resolveAgentId(Request $request): int
    {
        if ($request->filled('agent_id')) {
            return (int) $request->input('agent_id');
        }

        if (Auth::check() && (Auth::user()->is_agent || Auth::user()->is_agent_staff || Auth::user()->is_staff)) {
            return Auth::user()->is_agent_staff ? Auth::user()->parent_id : Auth::id();
        }

        return User::where('role', 'agent')->first()?->id ?? 1;
    }

    private function handlePhotoUpload(Request $request, Student $student): void
    {
        try {
            $path = $this->fileUploadService->uploadStudentFile(
                $request->file('students_photo'),
                $student->agent,
                $student,
                'photo',
            );
            $student->updateQuietly(['students_photo' => $path]);
        } catch (\Exception $e) {
            Log::error('Failed to upload student photo: ' . $e->getMessage());
        }
    }
}
