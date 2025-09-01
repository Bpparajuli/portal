<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Notifications\DocumentUploaded;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;


class DocumentController extends Controller
{
    // List documents for a student (agent must own the student)
    public function index($studentId)
    {
        $student = Student::where('agent_id', Auth::id())->with('agent')->findOrFail($studentId);
        $documents = $student->documents()->orderBy('created_at', 'desc')->get();
        return view('agent.documents.index', compact('student', 'documents'));
    }

    // Show upload form
    public function create($studentId)
    {
        $student = Student::where('agent_id', Auth::id())->with('agent')->findOrFail($studentId);
        return view('agent.documents.create', compact('student'));
    }

    // Store uploaded file
    public function store(Request $request, $studentId)
    {
        $student = Student::where('agent_id', Auth::id())->with('agent')->findOrFail($studentId);

        $request->validate([
            'file' => 'required|file|max:15360|mimes:pdf,jpg,jpeg,png,doc,docx',
            'document_type' => 'nullable|string|max:100'
        ]);

        $file = $request->file('file');

        $agentSlug = Str::slug($student->agent->business_name ?? $student->agent->username ?? 'agent');
        $studentSlug = Str::slug($student->first_name . ' ' . $student->last_name ?? 'student');

        $folder = "agents/{$agentSlug}/{$studentSlug}/documents";
        $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $file->getClientOriginalName());
        $path = Storage::disk('public')->putFileAs($folder, $file, $filename);

        $document = Document::create([
            'student_id'   => $student->id,
            'uploaded_by'  => Auth::id(),
            'file_name'    => $file->getClientOriginalName(),
            'file_path'    => $path,
            'file_type'    => $file->getClientMimeType(),
            'file_size'    => $file->getSize(),
            'document_type' => $request->document_type,
        ]);

        // Notify all admins about agent-uploaded document
        $admins = User::where('is_admin', 1)->get();
        Notification::send($admins, new DocumentUploaded($document));

        return redirect()->route('agent.documents.index', $student->id)
            ->with('success', 'Document uploaded successfully.');
    }

    public function download($id)
    {
        $document = Document::findOrFail($id);

        // confirm student belongs to agent
        if ($document->student->agent_id != Auth::id()) abort(403);

        $file = storage_path('app/public/' . $document->file_path);
        if (!file_exists($file)) abort(404);

        return response()->download($file, $document->file_name);
    }

    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        if ($document->student->agent_id != Auth::id()) abort(403);

        Storage::disk('public')->delete($document->file_path);
        $studentId = $document->student_id;
        $document->delete();

        return redirect()->route('agent.documents.index', $studentId)
            ->with('success', 'Document deleted.');
    }
}
