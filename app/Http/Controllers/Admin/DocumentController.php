<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Notifications\DocumentUploaded; // replace with your notification class
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    // List documents for a student
    public function index($studentId)
    {
        $student = Student::with('agent')->findOrFail($studentId);
        $documents = $student->documents()->orderBy('created_at', 'desc')->get();

        return view('admin.documents.index', compact('student', 'documents'));
    }

    // Show upload form
    public function create($studentId)
    {
        $student = Student::with('agent')->findOrFail($studentId);
        return view('admin.documents.create', compact('student'));
    }

    // Store uploaded file
    public function store(Request $request, $studentId)
    {
        $student = Student::with('agent')->findOrFail($studentId);

        $request->validate([
            'file' => 'required|file|max:15360|mimes:pdf,jpg,jpeg,png,doc,docx',
            'document_type' => 'nullable|string|max:100'
        ]);

        $file = $request->file('file');

        // sanitize names
        $agentSlug = Str::slug($student->agent->business_name ?? $student->agent->username ?? 'agent');
        $studentSlug = Str::slug($student->first_name . ' ' . $student->last_name ?? 'student');

        $folder = "agents/{$agentSlug}/{$studentSlug}/documents";
        $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $file->getClientOriginalName());
        $path = Storage::disk('public')->putFileAs($folder, $file, $filename);

        $document = Document::create([
            'student_id'   => $student->id,
            'uploaded_by'  => Auth::user()->id,
            'file_name'    => $file->getClientOriginalName(),
            'file_path'    => $path,
            'file_type'    => $file->getClientMimeType(),
            'file_size'    => $file->getSize(),
            'document_type' => $request->document_type,
        ]);

        // Notify agent if admin uploaded
        if (Auth::user()->is_admin && $student->agent) {
            $student->agent->notify(new DocumentUploaded($document));
        }

        return redirect()->route('admin.documents.index', $student->id)
            ->with('success', 'Document uploaded successfully.');
    }

    // Download document
    public function download($id)
    {
        $document = Document::findOrFail($id);
        $file = storage_path('app/public/' . $document->file_path);
        if (!file_exists($file)) {
            abort(404);
        }
        return response()->download($file, $document->file_name);
    }

    // Delete document
    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        $studentId = $document->student_id;

        // delete file
        Storage::disk('public')->delete($document->file_path);

        $document->delete();

        return redirect()->route('admin.documents.index', $studentId)
            ->with('success', 'Document deleted.');
    }
}
