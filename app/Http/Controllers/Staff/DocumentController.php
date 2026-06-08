<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use App\Contracts\FileUploadServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function __construct(
        private readonly FileUploadServiceInterface $fileUploadService,
    ) {}
    public function index(Student $student)
    {
        $documents = $student->documents()->latest()->get();
        $allDocumentTypes = ['passport','10th_certificate','10th_transcript','11th_transcript','12th_certificate','12th_transcript','cv','moi','lor','ielts_pte_language_certificate'];
        $predefinedDocs = $documents->filter(fn($doc) => in_array(strtolower($doc->document_type), $allDocumentTypes));
        $otherDocs = $documents->filter(fn($doc) => !in_array(strtolower($doc->document_type), $allDocumentTypes));

        return view('staff.documents.index', compact('student', 'predefinedDocs', 'otherDocs', 'allDocumentTypes'));
    }

    public function store(Request $request, Student $student)
    {
        $request->validate([
            'document_type' => 'required|string',
            'file' => 'required|mimes:jpg,jpeg,png,pdf|max:51200',
        ]);

        $user = Auth::user();
        $agent = $student->agent ?? $user;

        $filePath = $this->fileUploadService->uploadStudentDocument($request->file('file'), $agent, $student, $request->document_type);

        Document::create([
            'student_id' => $student->id,
            'uploaded_by' => $user->id,
            'file_name' => $request->file('file')->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $request->file('file')->getMimeType(),
            'document_type' => $request->document_type,
            'status' => 'uploaded',
        ]);

        return redirect()->back()->with('success', 'Document uploaded successfully.');
    }

    public function storeOther(Request $request, Student $student)
    {
        $request->validate([
            'custom_name' => 'required|string|max:255',
            'file' => 'required|mimes:jpg,jpeg,png,pdf|max:51200',
        ]);

        $user = Auth::user();
        $agent = $student->agent ?? $user;

        $filePath = $this->fileUploadService->uploadStudentDocument($request->file('file'), $agent, $student, $request->custom_name);

        Document::create([
            'student_id' => $student->id,
            'uploaded_by' => $user->id,
            'file_name' => $request->file('file')->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $request->file('file')->getMimeType(),
            'document_type' => $request->custom_name,
            'custom_name' => $request->custom_name,
            'status' => 'uploaded',
        ]);

        return redirect()->back()->with('success', 'Document uploaded successfully.');
    }

    public function destroy(Student $student, Document $document)
    {
        if ($document->student_id !== $student->id) abort(403);
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();
        return redirect()->back()->with('success', 'Document deleted.');
    }

    public function download(Student $student, Document $document)
    {
        if ($document->student_id !== $student->id) abort(403);
        if (!Storage::disk('public')->exists($document->file_path)) abort(404);
        return response()->download(Storage::disk('public')->path($document->file_path), $document->file_name);
    }
}
