<?php
namespace App\Services;

use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use App\Services\FileUploadService;
use App\Notifications\DocumentUploaded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class DocumentService
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {}

    /**
     * Upload and store a student document.
     *
     * Uploads via FileUploadService, creates a Document record with pending status,
     * logs activity, and notifies admins.
     *
     * @return Document  The created document record.
     */
    public function storeDocument(Student $student, User $agent, Request $request, string $documentType): Document
    {
        $file = $request->file('file');

        $path = $this->fileUploadService->uploadStudentDocument($file, $agent, $student, $documentType);

        $document = Document::create([
            'student_id'    => $student->id,
            'uploaded_by'   => Auth::id(),
            'file_name'     => $file->getClientOriginalName(),
            'file_path'     => $path,
            'file_type'     => $file->getClientOriginalExtension(),
            'document_type' => $documentType,
            'status'        => 'pending',
        ]);

        Activity::create([
            'user_id' => Auth::id(),
            'type' => 'document_uploaded',
            'description' => "{$documentType} uploaded for {$student->first_name} {$student->last_name}",
            'notifiable_id' => $student->id,
            'link' => route('admin.documents.index', ['student' => $student->id]),
        ]);

        $admins = User::whereIn('role', ['superadmin', 'admin'])->get();
        Notification::send($admins, new DocumentUploaded($agent, $student, $document));

        return $document;
    }

    /**
     * Delete a document and its file from storage.
     */
    public function destroyDocument(Document $document): void
    {
        if ($document->file_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
        }

        Activity::create([
            'user_id' => Auth::id(),
            'type' => 'document_deleted',
            'description' => "{$document->document_type} deleted for student #{$document->student_id}",
            'notifiable_id' => $document->student_id,
        ]);

        $document->delete();
    }
}
