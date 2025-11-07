<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use App\Notifications\DocumentDeleted;
use App\Notifications\DocumentUploaded;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

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

        $uploadedTypes = $documents->pluck('document_type')
            ->map(fn($t) => is_null($t) ? null : strtolower($t))
            ->filter()
            ->toArray();

        $availableTypes = array_values(array_diff($this->allDocumentTypes(), $uploadedTypes));
        $allDocumentTypes = $this->allDocumentTypes();

        $predefinedDocs = $documents->filter(fn($doc) => in_array(strtolower($doc->document_type), $allDocumentTypes));
        $otherDocs = $documents->filter(fn($doc) => !in_array(strtolower($doc->document_type), $allDocumentTypes));

        return view('agent.documents.index', compact('student', 'availableTypes', 'predefinedDocs', 'otherDocs', 'allDocumentTypes'));
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
            'file' => 'required|mimes:jpg,jpeg,png,pdf|max:51200',
        ]);

        $this->saveDocument($request->file('file'), $request->document_type, $student);

        return redirect()->route('agent.documents.index', $student->id)
            ->with('success', 'Document uploaded successfully.');
    }

    public function storeOther(Request $request, Student $student)
    {
        $request->validate([
            'custom_name' => 'required|string|max:255',
            'file' => 'required|mimes:jpg,jpeg,png,pdf|max:51200',
        ]);

        $this->saveDocument($request->file('file'), $request->custom_name, $student);

        return redirect()->route('agent.documents.index', $student->id)
            ->with('success', 'Other document uploaded successfully.');
    }

    private function saveDocument($file, $documentType, Student $student)
    {
        $agent = Auth::user();

        $safeAgent = preg_replace('/[^A-Za-z0-9_\-]/', '_', strtolower($agent->business_name ?? 'agent'));
        $rawStudentName = trim(($student->first_name ?? '') . '_' . ($student->last_name ?? ''));
        $safeStudent = empty($rawStudentName) || $rawStudentName === '_'
            ? 'student_' . $student->id
            : preg_replace('/[^A-Za-z0-9_\-]/', '_', strtolower($rawStudentName));

        $fileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', strtolower($documentType)) . '.' . $file->getClientOriginalExtension();
        $folderPath = "agents/{$safeAgent}/{$safeStudent}";
        $filePath = $file->storeAs($folderPath, $fileName, 'public');

        $document = Document::create([
            'student_id' => $student->id,
            'uploaded_by' => $agent->id,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_type' => $file->getMimeType(),
            'document_type' => $documentType,
            'status' => 'uploaded',
        ]);

        $admin = User::find(3); // or adjust your admin logic
        Notification::send($admin, new DocumentUploaded($agent, $student, $document));

        ActivityLogger::log(
            'document_uploaded',
            "📄 Document uploaded: {$documentType}",
            $document->id,
            route('agent.documents.index', $student->id)
        );
    }

    public function destroy(Student $student, Document $document)
    {
        $agent = Auth::user();

        if ($document->student_id !== $student->id) abort(403);

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        $admin = User::find(3);
        Notification::send($admin, new DocumentDeleted($agent, $student, $document));

        return back()->with('success', 'Document deleted.');
    }

    public function download(Student $student, Document $document)
    {
        if ($document->student_id !== $student->id) abort(403);
        if (!Storage::disk('public')->exists($document->file_path)) abort(404);

        return response()->download(Storage::disk('public')->path($document->file_path), $document->file_name);
    }
}
