<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentNoteController extends Controller
{
    // =========================================================================
    // STORE
    // =========================================================================

    public function store(Request $request)
    {
        $this->denyAgents();

        $validated = $request->validate([
            'student_id'         => ['required', 'exists:students,id'],
            'content'            => ['required', 'string'],
            'type'               => ['required', 'in:internal,customer_visible,reminder'],
            'is_pinned'          => ['boolean'],
            'remind_at'          => ['nullable', 'date', 'required_if:type,reminder'],
            'reminder_time_slot' => ['nullable', 'in:morning,day,evening'],
        ]);

        $this->authorizeStudentAccess((int) $validated['student_id']);

        $note = StudentNote::create([
            ...$validated,
            'created_by' => Auth::id(),
            'is_pinned'  => $request->boolean('is_pinned'),
        ]);

        if ($request->expectsJson()) {
            $note->load('creator');
            return response()->json(['success' => true, 'note' => $note]);
        }

        return back()->with('success', 'Note saved.');
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function update(Request $request, StudentNote $note)
    {
        $this->denyAgents();
        $this->authorizeNote($note);

        $validated = $request->validate([
            'content'            => ['required', 'string'],
            'type'               => ['required', 'in:internal,customer_visible,reminder'],
            'is_pinned'          => ['boolean'],
            'remind_at'          => ['nullable', 'date'],
            'reminder_time_slot' => ['nullable', 'in:morning,day,evening'],
        ]);

        $note->update([
            ...$validated,
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        return back()->with('success', 'Note updated.');
    }

    // =========================================================================
    // TOGGLE PIN
    // =========================================================================

    public function togglePin(StudentNote $note)
    {
        $this->denyAgents();
        $this->authorizeNote($note);

        $note->togglePin();

        return response()->json(['success' => true, 'is_pinned' => $note->is_pinned]);
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

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

    // =========================================================================
    // Helpers
    // =========================================================================

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
