<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use App\Notifications\DocumentDeleted;
use App\Notifications\DocumentUploaded;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use ZipArchive;

class DocumentController extends Controller
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {}

    public static function allDocumentTypes(): array
    {
        return [
            'passport', '10th_certificate', '10th_transcript', '11th_transcript',
            '12th_certificate', '12th_transcript', 'cv', 'moi', 'lor',
            'ielts_pte_language_certificate'
        ];
    }

    private function authorizeStudentAccess(Student $student): void
    {
        $user = Auth::user();
        if ($user->is_agent && $student->agent_id != $user->id) {
            abort(403, 'Unauthorized');
        }
    }

    public function index(Student $student)
    {
        $this->authorizeStudentAccess($student);
        $documents = $student->documents()->latest()->get();
        $allDocumentTypes = self::allDocumentTypes();
        $predefinedDocs = $documents->filter(fn($doc) => in_array(strtolower($doc->document_type), $allDocumentTypes));
        $otherDocs = $documents->filter(fn($doc) => !in_array(strtolower($doc->document_type), $allDocumentTypes));
        $user = Auth::user();
        $routePrefix = $user->is_admin_staff ? 'admin' : $user->role;
        return view('shared.documents.index', compact('student', 'predefinedDocs', 'otherDocs', 'allDocumentTypes', 'routePrefix'));
    }

    public function store(Request $request, Student $student)
    {
        $this->authorizeStudentAccess($student);
        $request->validate([
            'document_type' => [
                'required', 'string',
                function ($attr, $value, $fail) use ($student) {
                    $value = strtolower($value);
                    if (!in_array($value, self::allDocumentTypes())) {
                        $fail('Invalid document type selected.');
                    }
                    if ($student->documents()->whereRaw('LOWER(document_type) = ?', [$value])->exists()) {
                        $fail("A document of type '{$value}' already exists for this student.");
                    }
                },
            ],
            'file' => 'required|mimes:jpg,jpeg,png,pdf|max:51200',
        ]);
        $this->saveDocument($request->file('file'), $request->document_type, $student);
        return redirect()->route(Auth::user()->role . '.documents.index', $student->id)
            ->with('success', 'Document uploaded successfully.');
    }

    public function storeOther(Request $request, Student $student)
    {
        $this->authorizeStudentAccess($student);
        $request->validate([
            'custom_name' => 'required|string|max:255',
            'file' => 'required|mimes:jpg,jpeg,png,pdf|max:51200',
        ]);
        $this->saveDocument($request->file('file'), $request->custom_name, $student);
        return redirect()->route(Auth::user()->role . '.documents.index', $student->id)
            ->with('success', 'Other document uploaded successfully.');
    }

    private function saveDocument($file, $documentType, Student $student)
    {
        $user = Auth::user();
        $agent = $student->agent ?? $user;
        $filePath = $this->fileUploadService->uploadStudentDocument($file, $agent, $student, $documentType);
        $document = Document::create([
            'student_id' => $student->id,
            'uploaded_by' => $user->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $file->getMimeType(),
            'document_type' => $documentType,
            'status' => 'uploaded',
        ]);
        // Synchronous activity logging (not dependent on queue)
        Activity::create([
            'user_id' => $user->id,
            'type' => 'document_uploaded',
            'description' => "📤 {$documentType} uploaded for {$student->full_name}",
            'notifiable_id' => $student->id,
            'link' => route(Auth::user()->role . '.documents.index', $student->id),
        ]);

        if ($student->agent_id) {
            $agent = User::find($student->agent_id);
            if ($agent) {
                Notification::send($agent, new DocumentUploaded($user, $student, $document));
            }
        }
    }

    public function destroy(Student $student, Document $document)
    {
        $this->authorizeStudentAccess($student);
        if ($document->student_id !== $student->id) abort(403);
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();
        if ($student->agent_id) {
            $agent = User::find($student->agent_id);
            if ($agent) {
                Notification::send($agent, new DocumentDeleted(Auth::user(), $student, $document));
            }
        }
        return redirect()->route(Auth::user()->role . '.documents.index', $student->id)
            ->with('success', 'Document deleted successfully.');
    }

    public function download(Student $student, Document $document)
    {
        $this->authorizeStudentAccess($student);
        if ($document->student_id !== $student->id) abort(403);
        if (!Storage::disk('public')->exists($document->file_path)) abort(404);
        return response()->download(Storage::disk('public')->path($document->file_path), $document->file_name);
    }

    public function downloadAll(Student $student)
    {
        $user = Auth::user();
        if (!$user->is_admin && !$user->is_staff) abort(403);
        $documents = $student->documents;
        if ($documents->isEmpty()) {
            return back()->with('error', 'No documents found for this student.');
        }
        $zipFileName = strtolower(str_replace(' ', '_', $student->first_name . '_' . $student->last_name)) . '_documents.zip';
        $tempDir = rtrim(sys_get_temp_dir(), '\\/');
        $zipPath = $tempDir . DIRECTORY_SEPARATOR . $zipFileName;
        if (file_exists($zipPath)) unlink($zipPath);
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Could not create ZIP file.');
        }
        $added = 0;
        foreach ($documents as $doc) {
            $fileFullPath = Storage::disk('public')->path($doc->file_path);
            if (file_exists($fileFullPath)) {
                $zip->addFile($fileFullPath, basename($doc->file_name));
                $added++;
            }
        }
        $zip->close();
        if ($added === 0) {
            if (file_exists($zipPath)) unlink($zipPath);
            return back()->with('error', 'No document files found on server to download.');
        }
        $response = response()->download($zipPath, $zipFileName);
        register_shutdown_function(function () use ($zipPath) {
            if (file_exists($zipPath)) @unlink($zipPath);
        });
        return $response;
    }

    public function updateStatus(Request $request, Student $student, Document $document)
    {
        if (!Auth::user()->is_admin) abort(403);
        if ($document->student_id !== $student->id) abort(403);
        $request->validate(['status' => 'required|in:uploaded,missing,reviewed,downloaded']);
        $data = ['status' => $request->status];
        if ($request->status === 'reviewed') {
            $data['reviewed_at'] = now();
            $data['reviewed_by'] = Auth::id();
        }
        $document->update($data);
        return redirect()->back()->with('success', 'Document status updated.');
    }
}
