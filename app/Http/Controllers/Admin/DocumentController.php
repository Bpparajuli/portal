<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use App\Notifications\DocumentUploaded;
use App\Notifications\DocumentDeleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Helpers\ActivityLogger;

class DocumentController extends Controller
{
    // Master list of document types (same as agent)
    private function allDocumentTypes(): array
    {
        return ['passport', 'id', 'transcript', 'financial', 'other'];
    }

    public function index(Student $student)
    {
        $documents = $student->documents()->latest()->get();

        $uploadedTypes = $documents->pluck('document_type')
            ->map(fn($t) => is_null($t) ? null : strtolower($t))
            ->filter()
            ->toArray();

        $availableTypes = array_values(array_diff($this->allDocumentTypes(), $uploadedTypes));

        return view('admin.documents.index', compact('student', 'documents', 'availableTypes'));
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
                        return $fail("A document of type '{$value}' has already been uploaded for this student.");
                    }
                },
            ],
            'file' => 'required|mimes:jpg,jpeg,png,pdf|max:51200', // 50MB
        ]);

        $admin = Auth::user();

        // Clean student name
        $rawStudentName = trim(($student->first_name ?? '') . '_' . ($student->last_name ?? ''));
        if (empty($rawStudentName) || $rawStudentName === '_') {
            $rawStudentName = 'student_' . $student->id;
        }
        $safeStudent = preg_replace('/[^A-Za-z0-9_\-]/', '_', strtolower($rawStudentName));

        $file = $request->file('file');
        $documentType = strtolower($request->document_type);
        $fileName = $documentType . '.' . $file->getClientOriginalExtension();

        // Store inside "admin_uploads/student_name/"
        $folderPath = "admin_uploads/{$safeStudent}";
        $filePath = $file->storeAs($folderPath, $fileName, 'public');

        // Create record
        $document = Document::create([
            'student_id' => $student->id,
            'uploaded_by' => $admin->id,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_type' => $file->getMimeType(),
            'document_type' => $documentType,
            'status' => 'uploaded',
        ]);

        // Notify agent if assigned to this student
        if ($student->agent_id) {
            $agent = User::find($student->agent_id);
            if ($agent) {
                Notification::send($agent, new DocumentUploaded($admin, $student, $document));
            }
        }

        ActivityLogger::log(
            'document_uploaded_admin',
            "📄 Admin uploaded document: {$documentType}",
            $document->id,
            route('admin.documents.index', $student->id)
        );

        return redirect()->route('admin.documents.index', $student->id)
            ->with('success', 'Document uploaded successfully.');
    }

    public function destroy(Student $student, Document $document)
    {
        $admin = Auth::user();

        if ($document->student_id !== $student->id) {
            abort(403);
        }

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        // Notify agent if assigned
        if ($student->agent_id) {
            $agent = User::find($student->agent_id);
            if ($agent) {
                Notification::send($agent, new DocumentDeleted($admin, $student, $document));
            }
        }

        ActivityLogger::log(
            'document_deleted_admin',
            "❌ Admin deleted document: {$document->document_type}",
            $document->id,
            route('admin.documents.index', $student->id)
        );

        return back()->with('success', 'Document deleted successfully.');
    }

    public function download(Student $student, Document $document)
    {
        if ($document->student_id !== $student->id) {
            abort(403, 'Unauthorized access.');
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        $absolutePath = Storage::disk('public')->path($document->file_path);
        return response()->download($absolutePath, $document->file_name);
    }
}
