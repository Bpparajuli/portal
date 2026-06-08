<?php

namespace App\Actions;

use App\Contracts\FileUploadServiceInterface;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateStudentAction
{
    public function __construct(
        private readonly FileUploadServiceInterface $fileUploadService,
    ) {}
    public function execute(Student $student, Request $request): Student
    {
        return DB::transaction(function () use ($student, $request) {
            $data = $request->validated();

            $student->update($data);

            if ($request->hasFile('students_photo')) {
                $this->handlePhotoUpload($request, $student);
            }

            return $student->fresh();
        });
    }

    private function handlePhotoUpload(Request $request, Student $student): void
    {
        try {
            $path = $this->fileUploadService->uploadStudentFile(
                $request->file('students_photo'),
                $student->agent,
                $student,
                'photo',
                $student->students_photo
            );
            $student->updateQuietly(['students_photo' => $path]);
        } catch (\Exception $e) {
            Log::error('Failed to upload student photo: ' . $e->getMessage());
        }
    }
}
