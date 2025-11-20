<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use App\Notifications\DocumentDeleted;
use App\Notifications\DocumentUploaded;
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
            'ielts_pte_language_certificate'
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

        return redirect()->route('admin.documents.index', $student->id)
            ->with('success', 'Document deleted successfully.');
    }


    public function download(Student $student, Document $document)
    {
        if ($document->student_id !== $student->id) abort(403);
        if (!Storage::disk('public')->exists($document->file_path)) abort(404);

        return response()->download(Storage::disk('public')->path($document->file_path), $document->file_name);
    }
    public function downloadAll(Student $student)
    {
        $documents = $student->documents;

        if ($documents->isEmpty()) {
            return back()->with('error', 'No documents found for this student.');
        }

        // Safe zip filename
        $zipFileName = strtolower(str_replace(' ', '_', $student->first_name . '_' . $student->last_name)) . '_documents.zip';

        // Temporary folder
        $tempDir = storage_path('app/public/temp');

        // Ensure temp folder exists
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $zipPath = $tempDir . DIRECTORY_SEPARATOR . $zipFileName;

        // Delete old zip if exists
        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        $zip = new \ZipArchive;

        // Open zip file
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', "Could not create ZIP file at $zipPath");
        }

        // Add each document
        foreach ($documents as $doc) {
            $fileFullPath = Storage::disk('public')->path($doc->file_path);

            if (file_exists($fileFullPath)) {
                // Use relative name inside zip
                $zip->addFile($fileFullPath, basename($doc->file_name));
            }
        }

        $zip->close();

        // Double-check zip exists
        if (!file_exists($zipPath)) {
            return back()->with('error', "ZIP file was not created. Check folder permissions: $tempDir");
        }

        // Download to browser
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }
}
