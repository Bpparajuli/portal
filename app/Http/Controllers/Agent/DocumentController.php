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
    public function index(Student $student)
    {
        $documents = $student->documents()->get();
        return view('agent.documents.index', compact('student', 'documents'));
    }

    public function create(Student $student)
    {
        return view('agent.documents.create', compact('student'));
    }

    public function store(Request $request, Student $student)
    {
        $request->validate([
            'document_type' => [
                'required',
                'string',
                // Check if document type already exists for this student
                function ($attribute, $value, $fail) use ($student) {
                    if ($student->documents()->where('document_type', $value)->exists()) {
                        $fail("A document of type '{$value}' has already been uploaded.");
                    }
                }
            ],
            'file' => 'required|mimes:jpg,jpeg,png,pdf|max:51200', // 50MB
        ]);

        $agent = Auth::user();

        // Create safe folder names
        $safeAgent = preg_replace('/[^A-Za-z0-9_\-]/', '_', strtolower($agent->business_name ?? 'agent'));

        // Combine first_name and last_name for student folder
        $fullStudentName = trim($student->first_name . '_' . $student->last_name);
        $safeStudent = preg_replace('/[^A-Za-z0-9_\-]/', '_', strtolower($fullStudentName));

        $file = $request->file('file');
        $fileName = $request->document_type . '.' . $file->getClientOriginalExtension();

        // Folder: storage/app/public/agents/{agent}/{student}
        $folderPath = "agents/{$safeAgent}/{$safeStudent}";

        // Store the file using 'public' disk
        $filePath = $file->storeAs($folderPath, $fileName, 'public');

        // Save document record in DB
        $document = Document::create([
            'student_id' => $student->id,
            'uploaded_by' => $agent->id,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_type' => $file->getMimeType(),
            'document_type' => $request->document_type,
            'status' => 'uploaded',
        ]);

        // Notify admins
        $admins = User::where('is_admin', 1)->get();
        Notification::send($admins, new DocumentUploaded($agent, $student, $document));

        return redirect()->route('agent.documents.index', $student->id)
            ->with('success', 'Document uploaded successfully.');
    }

    public function destroy(Student $student, Document $document)
    {
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
