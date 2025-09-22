<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Notifications\DocumentUploaded;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class DocumentController extends Controller
{
    public function index($studentId)
    {
        $student = Student::with('agent')->findOrFail($studentId);
        $documents = $student->documents()->latest()->get();

        return view('admin.documents.index', compact('student', 'documents'));
    }

    public function create($studentId)
    {
        $student = Student::with('agent')->findOrFail($studentId);
        return view('admin.documents.create', compact('student'));
    }

    public function store(Request $request, $studentId)
    {
        $student = Student::with('agent')->findOrFail($studentId);

        $request->validate([
            'file'          => 'required|file|max:15360|mimes:pdf,jpg,jpeg,png,doc,docx',
            'document_type' => 'nullable|string|max:100',
            'notes'         => 'nullable|string',
        ]);

        $file = $request->file('file');

        $agentSlug   = Str::slug($student->agent->business_name ?? $student->agent->username ?? 'agent');
        $studentSlug = Str::slug($student->first_name . ' ' . $student->last_name ?? 'student');

        $folder   = "agents/{$agentSlug}/{$studentSlug}/documents";
        $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $file->getClientOriginalName());
        $path     = Storage::disk('public')->putFileAs($folder, $file, $filename);

        $document = Document::create([
            'student_id'    => $student->id,
            'uploaded_by'   => Auth::id(),
            'file_name'     => $file->getClientOriginalName(),
            'file_path'     => $path,
            'file_type'     => $file->getClientMimeType(),
            'file_size'     => $file->getSize(),
            'document_type' => $request->document_type,
            'notes'         => $request->notes,
        ]);

        // Notify agent (if any)
        if ($student->agent) {
            // Pass the required arguments to DocumentUploaded constructor
            $student->agent->notify(new DocumentUploaded(Auth::user(), $student, $document));
        }

        return redirect()->route('admin.documents.index', $student->id)
            ->with('success', 'Document uploaded successfully.');
    }

    public function download($id)
    {
        $document = Document::with('student')->findOrFail($id);
        $file = storage_path('app/public/' . $document->file_path);
        abort_unless(file_exists($file), 404);

        return response()->download($file, $document->file_name);
    }

    public function destroy($id)
    {
        $document = Document::with('student')->findOrFail($id);
        $studentId = $document->student_id;

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return redirect()->route('admin.documents.index', $studentId)
            ->with('success', 'Document deleted.');
    }
}
