<?php

namespace App\Actions;

use App\Contracts\FileUploadServiceInterface;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteStudentAction
{
    public function __construct(
        private readonly NotifyUserAction $notifyUserAction,
        private readonly FileUploadServiceInterface $fileUploadService,
    ) {}

    public function execute(Student $student, bool $force = false): void
    {
        DB::transaction(function () use ($student, $force) {
            if ($force) {
                $this->fileUploadService->deleteStudentFiles($student->agent, $student);
                $student->forceDelete();
            } else {
                $student->delete();
            }

            $this->notifyUserAction->notifyStudentDeleted($student);
        });
    }
}
