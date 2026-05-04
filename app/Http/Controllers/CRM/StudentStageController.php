<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\StudentStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * StudentStageController
 *
 * Handles the configure.blade.php page — admin only.
 * Manages creating, editing, reordering, and toggling stages.
 */
class StudentStageController extends Controller
{
    public function __construct()
    {
        // All methods here are admin-only
        $this->middleware(function ($request, $next) {
            abort_unless(Auth::user()->is_admin, 403, 'Only admins can configure CRM stages.');
            return $next($request);
        });
    }

    /**
     * Configure page: list all stages + create/edit form.
     */
    public function index()
    {
        $stages = StudentStage::ordered()->get();
        return view('crm.configure', compact('stages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:100', 'unique:student_stages,name'],
            'color'              => ['required', 'string'],
            'description'        => ['nullable', 'string'],
            'is_won_stage'       => ['boolean'],
            'is_lost_stage'      => ['boolean'],
            'max_days_in_stage'  => ['nullable', 'integer', 'min:1'],
            'allowed_next_stages' => ['nullable', 'array'],
        ]);

        // Place at end
        $validated['stage_order'] = StudentStage::max('stage_order') + 1;
        $validated['is_active']   = true;

        StudentStage::create($validated);

        return back()->with('success', 'Stage created.');
    }

    public function update(Request $request, StudentStage $stage)
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:100', 'unique:student_stages,name,' . $stage->id],
            'color'              => ['required', 'string'],
            'description'        => ['nullable', 'string'],
            'is_won_stage'       => ['boolean'],
            'is_lost_stage'      => ['boolean'],
            'max_days_in_stage'  => ['nullable', 'integer', 'min:1'],
            'allowed_next_stages' => ['nullable', 'array'],
        ]);

        $stage->update($validated);

        return back()->with('success', 'Stage updated.');
    }

    /**
     * Toggle is_active on a stage.
     */
    public function toggleActive(StudentStage $stage)
    {
        $stage->update(['is_active' => !$stage->is_active]);
        return response()->json(['success' => true, 'is_active' => $stage->is_active]);
    }

    /**
     * Reorder stages via drag-and-drop (receives ordered array of IDs).
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer', 'exists:student_stages,id'],
        ]);

        foreach ($request->order as $position => $id) {
            StudentStage::where('id', $id)->update(['stage_order' => $position + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(StudentStage $stage)
    {
        abort_if($stage->students()->exists(), 422, 'Cannot delete a stage that has students assigned to it.');
        $stage->delete();
        return back()->with('success', 'Stage deleted.');
    }
}
