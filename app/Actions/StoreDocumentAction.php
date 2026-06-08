<?php

namespace App\Actions;

use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use App\Notifications\DocumentUploaded;
use App\Contracts\FileUploadServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class StoreDocumentAction
{
    public function __construct(
        private readonly FileUploadServiceInterface $fileUploadService,
        private LogActivityAction $logActivity,
        private NotifyUserAction $notifyUser
    ) {}

    public function execute(Student $student, User $agent, Request $request, string $documentType): Document
    {
        $file = $request->file('file');

        $path = $this->fileUploadService->uploadStudentDocument(
            $file,
            $agent,
            $student,
            $documentType
        );

        $document = Document::create([
            'student_id' => $student->id,
            'uploaded_by' => Auth::id(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'document_type' => $documentType,
            'status' => 'pending',
        ]);

        $this->logActivity->execute(
            type: 'document_uploaded',
            description: "{$documentType} uploaded for {$student->first_name} {$student->last_name}",
            user: Auth::user(),
            notifiableId: $student->id,
            link: route('students.documents', $student->id)
        );

        $admins = User::whereIn('role', ['superadmin', 'admin'])->get();
        Notification::send($admins, new DocumentUploaded($agent, $student, $document));

        return $document;
    }
}
