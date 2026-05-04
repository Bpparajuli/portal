<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationStatus;
use Illuminate\Http\Request;

class ApplicationStatusController extends Controller
{
    /**
     * Show all application statuses
     */
    public function index()
    {
        $statuses = ApplicationStatus::orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.applications.status', compact('statuses'));
    }

    /**
     * Store new status
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'bg_color'      => 'nullable|string|max:255',
            'text_color' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        ApplicationStatus::create([
            'name'       => $request->name,
            'bg_color'      => $request->bg_color,
            'text_color' => $request->text_color,
            'sort_order' => $request->sort_order ?? 0,
            'is_active'  => $request->input('is_active', 0),
        ]);

        return redirect()
            ->route('admin.application-status.index')
            ->with('success', 'Application status added successfully.');
    }

    /**
     * Update existing status
     */
    public function update(Request $request, $id)
    {
        $status = ApplicationStatus::findOrFail($id);

        $request->validate([
            'name'       => 'required|string|max:255',
            'bg_color'      => 'nullable|string|max:255',
            'text_color' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $status->update([
            'name'       => $request->name,
            'bg_color'      => $request->bg_color,
            'text_color' => $request->text_color,
            'sort_order' => $request->sort_order ?? 0,
            'is_active'  => $request->input('is_active', 0),
        ]);

        return redirect()
            ->route('admin.application-status.index')
            ->with('success', 'Application status updated successfully.');
    }

    /**
     * Delete status
     */
    public function destroy($id)
    {
        $status = ApplicationStatus::findOrFail($id);

        $status->delete();

        return redirect()
            ->route('admin.application-status.index')
            ->with('success', 'Application status deleted successfully.');
    }
}
