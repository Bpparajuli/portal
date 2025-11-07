<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use App\Notifications\DocumentDeleted;
use App\Notifications\DocumentUploaded;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;

class DocumentController extends Controller
{
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
            'ielts_pte_language_certificate',
            'sop',
        ];
    }

    public function index(Student $student)
    {
        $documents = $student->documents()->latest()->get();

        $allDocumentTypes = $this->allDocumentTypes();

        $predefinedDocs = $documents->filter(fn($doc) => in_array(strtolower($doc->document_type), $allDocumentTypes));
        $otherDocs = $documents->filter(fn($doc) => !in_array(strtolower($doc->document_type), $allDocumentTypes));

        return view('admin.documents.index', compact('student', 'predefinedDocs', 'otherDocs', 'allDocumentTypes'));
    }

    public function store(Request $request, Student $student)
    {
        $request->validate([
            'document_type' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($student) {
                    $value = strtolower($value);
                    if (!in_array($value, $this->allDocumentTypes())) {
                        return $fail("Invalid document type selected.");
                    }
                    if ($student->documents()->whereRaw('LOWER(document_type) = ?', [$value])->exists()) {
                        return $fail("A document of type '{$value}' already exists for this student.");
                    }
                },
            ],
            'file' => 'required|mimes:jpg,jpeg,png,pdf|max:51200',
        ]);

        $this->saveDocument($request->file('file'), $request->document_type, $student);

        return redirect()->route('admin.documents.index', $student->id)
            ->with('success', 'Document uploaded successfully.');
    }

    public function storeOther(Request $request, Student $student)
    {
        $request->validate([
            'custom_name' => 'required|string|max:255',
            'file' => 'required|mimes:jpg,jpeg,png,pdf|max:51200',
        ]);

        $this->saveDocument($request->file('file'), $request->custom_name, $student);

        return redirect()->route('admin.documents.index', $student->id)
            ->with('success', 'Other document uploaded successfully.');
    }

    private function saveDocument($file, $documentType, Student $student)
    {
        $admin = Auth::user();

        $rawStudentName = trim(($student->first_name ?? '') . '_' . ($student->last_name ?? ''));
        $safeStudent = empty($rawStudentName) ? 'student_' . $student->id : preg_replace('/[^A-Za-z0-9_\-]/', '_', strtolower($rawStudentName));

        $fileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', strtolower($documentType)) . '.' . $file->getClientOriginalExtension();
        $folderPath = "admin_uploads/{$safeStudent}";
        $filePath = $file->storeAs($folderPath, $fileName, 'public');

        $document = Document::create([
            'student_id' => $student->id,
            'uploaded_by' => $admin->id,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_type' => $file->getMimeType(),
            'document_type' => $documentType,
            'status' => 'uploaded',
        ]);

        // Notify assigned agent if any
        if ($student->agent_id) {
            $agent = User::find($student->agent_id);
            if ($agent) {
                Notification::send($agent, new DocumentUploaded($admin, $student, $document));
            }
        }

        ActivityLogger::log(
            'document_uploaded_admin',
            "📄 Admin uploaded document: {$documentType} for {$student->first_name} {$student->last_name}",
            $document->id,
            route('admin.documents.index', $student->id)
        );
    }

    public function destroy(Student $student, Document $document)
    {
        $admin = Auth::user();

        // Extra safeguard for mismatched student-document
        if ($document->student_id !== $student->id) {
            abort(403, 'Unauthorized document access.');
        }

        // Delete from storage if exists
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $documentType = $document->document_type;
        $document->delete();

        // Notify assigned agent if exists
        if ($student->agent_id) {
            $agent = User::find($student->agent_id);
            if ($agent) {
                Notification::send($agent, new DocumentDeleted($admin, $student, $document));
            }
        }

        // Log the deletion
        ActivityLogger::log(
            'document_deleted_admin',
            "❌ Admin deleted document: {$documentType} for {$student->first_name} {$student->last_name}",
            $document->id,
            route('admin.documents.index', $student->id)
        );

        return redirect()->route('admin.documents.index', $student->id)
            ->with('success', 'Document deleted successfully.');
    }


    public function download(Student $student, Document $document)
    {
        if ($document->student_id !== $student->id) abort(403);
        if (!Storage::disk('public')->exists($document->file_path)) abort(404);

        return response()->download(Storage::disk('public')->path($document->file_path), $document->file_name);
    }
}
