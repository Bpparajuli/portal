<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentNoteController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'content' => 'required|string',
            'type' => 'nullable|in:internal,log',
            'is_pinned' => 'nullable|boolean',
            'title' => 'nullable|string|max:255',
        ]);

        $isLog = $request->input('type') === 'log';

        $note = StudentNote::create([
            'student_id' => $validated['student_id'],
            'content' => $validated['content'], // SAME AS NORMAL
            'created_by' => Auth::id(),
            'is_pinned' => $request->boolean('is_pinned', false),
            'is_log' => $isLog,
            'title' => $validated['title'] ?? null, // SAME AS NORMAL
            'type' => $validated['type'] ?? 'internal', // NO OVERRIDE
        ]);
        $message = $note->is_log ? 'Activity logged successfully.' : 'Note added successfully.';

        return redirect()->back()->with('success', $message);
    }

    public function update(Request $request, StudentNote $note)
    {
        $this->denyAgents();
        $this->authorizeNote($note);

        $validated = $request->validate([
            'content' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'in:internal,log'],
            'is_pinned' => ['boolean'],
        ]);

        $oldContent = $note->content;

        $note->update([
            'content' => $validated['content'],
            'title' => $validated['title'] ?? $note->title,
            'updated_by' => Auth::id(),
        ]);

        if ($request->filled('type')) {
            $isLog = $validated['type'] === 'log';
            $note->update([
                'type' => $validated['type'],
                'is_log' => $isLog,
                'title' => $isLog ? ($note->title ?? 'Note Converted') : null,
            ]);
        }

        // Log the edit
        Log::info('Note edited', [
            'note_id' => $note->id,
            'student_id' => $note->student_id,
            'edited_by' => Auth::id(),
            'old_content_length' => strlen($oldContent),
            'new_content_length' => strlen($validated['content'])
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Note updated successfully',
                'last_editor' => Auth::user()->name,
                'updated_at' => $note->updated_at->format('d M Y, g:i A')
            ]);
        }

        return back()->with('success', 'Note updated.');
    }

    public function convertType(Request $request, StudentNote $note)
    {
        $this->denyAgents();
        $this->authorizeNote($note);

        $type = $request->input('type', $note->is_log ? 'internal' : 'log');
        $isLog = $type === 'log';

        $note->update([
            'type' => $type,
            'is_log' => $isLog,
            'title' => $isLog ? ($note->title ?? 'Note Converted') : null,
            'updated_by' => Auth::id(),
        ]);

        $label = $isLog ? 'activity log' : 'note';
        return back()->with('success', "Moved to {$label} successfully.");
    }

    public function togglePin(StudentNote $note)
    {
        $this->denyAgents();
        $this->authorizeNote($note);

        $note->togglePin();

        return response()->json(['success' => true, 'is_pinned' => $note->is_pinned]);
    }

    public function destroy(StudentNote $note)
    {
        $this->denyAgents();
        $this->authorizeNote($note);
        $note->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Note deleted.');
    }

    private function denyAgents(): void
    {
        abort_if(Auth::user()->is_agent, 403, 'Agents have read-only CRM access.');
    }

    private function authorizeNote(StudentNote $note): void
    {
        $this->authorizeStudentAccess($note->student_id);
    }

    private function authorizeStudentAccess(int $studentId): void
    {
        $user    = Auth::user();
        $student = Student::findOrFail($studentId);

        if ($user->is_admin)       return;
        if ($user->is_admin_staff) return;

        if ($user->is_agent) {
            $staffIds        = User::where('parent_id', $user->id)->where('role', 'staff')->pluck('id')->toArray();
            $allowedAgentIds = array_merge([$user->id], $staffIds);
            abort_unless(in_array($student->agent_id, $allowedAgentIds), 403);
            return;
        }

        if ($user->is_agent_staff) {
            abort_unless(in_array($student->agent_id, [$user->id, $user->parent_id]), 403);
            return;
        }

        if ($user->is_staff) {
            abort_unless($student->agent_id === $user->id, 403);
            return;
        }

        abort(403);
    }
}
