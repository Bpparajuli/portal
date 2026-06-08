<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Revenue;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class RevenueController extends Controller
{
    public function index(Request $request)
    {
        $query = Revenue::with(['student.agent']);

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }
        if ($request->filled('agent')) {
            $query->whereHas('student', fn($q) => $q->where('agent_id', $request->agent));
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('student', fn($q) => $q->where('first_name', 'like', "%{$s}%")->orWhere('last_name', 'like', "%{$s}%"));
        }

        $grandTotal = (clone $query)->sum('amount');
        $revenues = $query->latest('transaction_date')->paginate(20);
        $filteredTotal = $revenues->sum('amount');
        $agents = User::agents()->orderBy('business_name')->get();

        return view('admin.revenues.index', compact('revenues', 'grandTotal', 'filteredTotal', 'agents'));
    }

    public function edit(Revenue $revenue)
    {
        $revenue->load('student.agent');
        return response()->json($revenue);
    }

    public function update(Request $request, Revenue $revenue)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'transaction_date' => 'required|date',
        ]);

        $revenue->update($validated);

        return back()->with('success', 'Revenue updated.');
    }

    public function destroy(Revenue $revenue)
    {
        if ($revenue->receipt_file && Storage::disk('public')->exists($revenue->receipt_file)) {
            Storage::disk('public')->delete($revenue->receipt_file);
        }
        $revenue->delete();
        return back()->with('success', 'Revenue deleted.');
    }
}
