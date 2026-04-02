<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use App\Notifications\DocumentDeleted;
use App\Notifications\DocumentUploaded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use App\Services\FileUploadService;

class DocumentController extends Controller
{
    /**
     * Allowed document types
     */
    private function allDocumentTypes(): array
    {
        return [
            'passport',
            '10th_certificate',
            '10th_transcript',
            '11th_transcript',
            '12th_certificate',
            '12th_transcript',
            'cv',
            'moi',
            'lor',
            'ielts_pte_language_certificate'
        ];
    }

    /**
     * List documents
     */
    public function index(Student $student)
    {
        $this->authorizeStudentAccess($student);

        $documents = $student->documents()->latest()->get();

        $uploadedTypes = $documents->pluck('document_type')
            ->map(fn($t) => strtolower($t))
            ->toArray();

        $availableTypes = array_values(array_diff($this->allDocumentTypes(), $uploadedTypes));
        $allDocumentTypes = $this->allDocumentTypes();

        $predefinedDocs = $documents->filter(
            fn($doc) =>
            in_array(strtolower($doc->document_type), $allDocumentTypes)
        );

        $otherDocs = $documents->filter(
            fn($doc) =>
            !in_array(strtolower($doc->document_type), $allDocumentTypes)
        );

        return view('agent.documents.index', compact(
            'student',
            'availableTypes',
            'predefinedDocs',
            'otherDocs',
            'allDocumentTypes'
        ));
    }

    /**
     * Upload predefined document
     */
    public function store(Request $request, Student $student)
    {
        $this->authorizeStudentAccess($student);

        $request->validate([
            'document_type' => [
                'required',
                'string',
                function ($attr, $value, $fail) use ($student) {
                    $value = strtolower($value);

                    if (!in_array($value, $this->allDocumentTypes())) {
                        return $fail("Invalid document type.");
                    }

                    if ($student->documents()
                        ->whereRaw('LOWER(document_type) = ?', [$value])
                        ->exists()
                    ) {
                        return $fail("Document already uploaded.");
                    }
                }
            ],
            'file' => 'required|mimes:jpg,jpeg,png,pdf|max:51200',
        ]);

        $this->saveDocument($request->file('file'), $request->document_type, $student);

        return back()->with('success', 'Document uploaded successfully.');
    }

    /**
     * Upload custom document
     */
    public function storeOther(Request $request, Student $student)
    {
        $this->authorizeStudentAccess($student);

        $request->validate([
            'custom_name' => 'required|string|max:255',
            'file' => 'required|mimes:jpg,jpeg,png,pdf|max:51200',
        ]);

        $this->saveDocument($request->file('file'), $request->custom_name, $student);

        return back()->with('success', 'Other document uploaded.');
    }

    /**
     * Save document (CORE LOGIC)
     */
    private function saveDocument($file, $documentType, Student $student)
    {
        $agent = Auth::user();

        // ✅ Upload using service
        $filePath = FileUploadService::uploadStudentDocument(
            $file,
            $agent,
            $student,
            $documentType
        );

        $cleanType = strtolower(str_replace(' ', '_', $documentType));

        $document = Document::create([
            'student_id' => $student->id,
            'uploaded_by' => $agent->id,
            'file_name' => $cleanType . '.' . $file->getClientOriginalExtension(),
            'file_path' => $filePath,
            'file_type' => $file->getMimeType(),
            'document_type' => $cleanType,
            'status' => 'uploaded',
        ]);

        // 🔔 Notify Admin
        $admin = User::find(3);
        if ($admin) {
            Notification::send($admin, new DocumentUploaded($agent, $student, $document));
        }
    }

    /**
     * Delete document
     */
    public function destroy(Student $student, Document $document)
    {
        $this->authorizeStudentAccess($student);

        if ($document->student_id !== $student->id) {
            abort(403);
        }

        // ✅ Delete file
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        // 🔔 Notify Admin
        $admin = User::find(3);
        if ($admin) {
            Notification::send($admin, new DocumentDeleted(Auth::user(), $student, $document));
        }

        return back()->with('success', 'Document deleted.');
    }

    /**
     * Download
     */
    public function download(Student $student, Document $document)
    {
        $this->authorizeStudentAccess($student);

        if ($document->student_id !== $student->id) abort(403);
        if (!Storage::disk('public')->exists($document->file_path)) abort(404);

        return response()->download(
            Storage::disk('public')->path($document->file_path),
            $document->file_name
        );
    }

    /**
     * Authorization
     */
    private function authorizeStudentAccess(Student $student)
    {
        $agent = Auth::user();

        if ($agent->is_agent && $student->agent_id != $agent->id) {
            abort(403, 'Unauthorized');
        }
    }
}
