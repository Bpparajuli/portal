<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\DocumentUploaded;

class DocumentController extends Controller
{
    // Master list of document types (single source of truth)
    private function allDocumentTypes(): array
    {
        return ['passport', 'id', 'transcript', 'financial', 'other'];
    }

    public function index(Student $student)
    {
        // Load student's documents
        $documents = $student->documents()->latest()->get();

        // Already uploaded types (normalized to lowercase)
        $uploadedTypes = $documents->pluck('document_type')
            ->map(fn($t) => is_null($t) ? null : strtolower($t))
            ->filter()
            ->toArray();

        // Available = all - uploaded
        $availableTypes = array_values(array_diff($this->allDocumentTypes(), $uploadedTypes));

        return view('agent.documents.index', compact('student', 'documents', 'availableTypes'));
    }

    public function create(Student $student)
    {
        // same logic for create view
        $documents = $student->documents()->get();
        $uploadedTypes = $documents->pluck('document_type')
            ->map(fn($t) => is_null($t) ? null : strtolower($t))
            ->filter()
            ->toArray();
        $availableTypes = array_values(array_diff($this->allDocumentTypes(), $uploadedTypes));

        return view('agent.documents.create', compact('student', 'availableTypes'));
    }

    public function store(Request $request, Student $student)
    {
        // Validate and ensure type isn't already uploaded
        $request->validate([
            'document_type' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($student) {
                    $value = strtolower($value);
                    // check against master list
                    if (!in_array($value, $this->allDocumentTypes())) {
                        return $fail("Invalid document type selected.");
                    }
                    // check if already uploaded
                    if ($student->documents()->whereRaw('LOWER(document_type) = ?', [$value])->exists()) {
                        return $fail("A document of type '{$value}' has already been uploaded for this student.");
                    }
                },
            ],
            'file' => 'required|mimes:jpg,jpeg,png,pdf|max:51200', // 50MB
        ]);

        $agent = Auth::user();

        // Build safe folder names
        $safeAgent = preg_replace('/[^A-Za-z0-9_\-]/', '_', strtolower($agent->business_name ?? 'agent'));

        // combine first & last name; fallback to id if empty
        $rawStudentName = trim(($student->first_name ?? '') . '_' . ($student->last_name ?? ''));
        if (empty($rawStudentName) || $rawStudentName === '_') {
            $rawStudentName = 'student_' . $student->id;
        }
        $safeStudent = preg_replace('/[^A-Za-z0-9_\-]/', '_', strtolower($rawStudentName));

        $file = $request->file('file');
        $documentType = strtolower($request->document_type);
        $fileName = $documentType . '.' . $file->getClientOriginalExtension();

        // Path relative to storage/app/public
        $folderPath = "agents/{$safeAgent}/{$safeStudent}";

        // storeAs will create subfolders automatically on the 'public' disk
        $filePath = $file->storeAs($folderPath, $fileName, 'public');

        // Persist
        $document = Document::create([
            'student_id' => $student->id,
            'uploaded_by' => $agent->id,
            'file_name' => $fileName,
            'file_path' => $filePath, // e.g. agents/agent_name/student_name/passport.png
            'file_type' => $file->getMimeType(),
            'document_type' => $documentType,
            'status' => 'uploaded',
        ]);

        // Notify admins (optional)
        $admins = User::where('is_admin', 1)->get();
        Notification::send($admins, new DocumentUploaded($agent, $student, $document));

        return redirect()->route('agent.documents.index', $student->id)
            ->with('success', 'Document uploaded successfully.');
    }

    public function destroy(Student $student, Document $document)
    {
        // ensure document belongs to student
        if ($document->student_id !== $student->id) {
            abort(403);
        }

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Document deleted.');
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
