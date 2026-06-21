<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Revenue;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
        $students = Student::with('agent')->orderBy('first_name')->get();

        return view('admin.revenues', compact('revenues', 'grandTotal', 'filteredTotal', 'agents', 'students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|numeric|min:0',
            'method' => 'required|in:cash,bank_transfer,credit_card,cheque,online_payment',
            'transaction_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $validated['amount'] = round((float) $validated['amount'], 2);
        $validated['created_by'] = Auth::id();

        if ($request->hasFile('receipt_file')) {
            $validated['receipt_file'] = $request->file('receipt_file')->store('revenues/receipts', 'public');
        }

        Revenue::create($validated);

        return back()->with('success', 'Revenue added successfully.');
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
