<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentStageHistory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class StudentStageHistoryController extends Controller
{
    /**
     * Return JSON stage history for a student.
     * Consumed by the History tab in crm/show.blade.php via fetch().
     */
    public function forStudent(Student $student)
    {
        $this->authorizeAccess($student);

        $history = StudentStageHistory::where('student_id', $student->id)
            ->with(['fromStage', 'toStage', 'changer'])
            ->latest()
            ->get()
            ->map(fn($h) => [
                'id'               => $h->id,
                'from'             => $h->fromStage?->name ?? 'Initial Stage',
                'to'               => $h->toStage?->name   ?? '—',
                'changed_by'       => $h->changer?->name   ?? 'Unknown',
                'reason'           => $h->reason,
                'days_in_previous' => $h->days_in_previous_stage,
                'date'             => $h->created_at->format('d M Y'),
                'description'      => $h->stage_change_description ?? null,
                'label'            => $h->reason === 'Student created'
                    ? 'Student created by ' . ($h->changer?->name ?? 'Unknown')
                    : 'By ' . ($h->changer?->name ?? 'Unknown'),
            ]);

        return response()->json($history);
    }

    /**
     * Delete a stage history record (admin only).
     */
    public function destroy(Student $student, StudentStageHistory $history)
    {
        abort_unless(Auth::user()->is_admin, 403);

        if ($history->student_id !== $student->id) {
            return response()->json(['message' => 'History record does not belong to this student.'], 422);
        }

        $history->delete();

        return response()->json(['success' => true, 'message' => 'Stage history deleted.']);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function authorizeAccess(Student $student): void
    {
        $user = Auth::user();

        if ($user->is_admin)       return;
        if ($user->is_admin_staff) return;

        if ($user->is_agent) {
            $staffIds = User::where('parent_id', $user->id)->where('role', 'staff')->pluck('id');
            abort_unless(
                $student->agent_id === $user->id || $staffIds->contains($student->agent_id),
                403
            );
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
