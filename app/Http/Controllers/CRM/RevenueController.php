<?php
// app/Http/Controllers/CRM/RevenueController.php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Revenue;
use App\Models\Student;
use App\Models\StudentNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RevenueController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin')->only(['destroy']);
    }

    public function store(Request $request, Student $student)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0',
                'method' => 'required|in:cash,bank_transfer,credit_card,cheque,online_payment',
                'transaction_date' => 'required|date',
                'reference_number' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            ]);

            $receiptPath = null;
            if ($request->hasFile('receipt_file')) {
                $receiptPath = $request->file('receipt_file')->store('revenue-receipts', 'public');
            }

            $revenue = $student->revenues()->create([
                'amount' => $validated['amount'],
                'method' => $validated['method'],
                'transaction_date' => $validated['transaction_date'],
                'reference_number' => $validated['reference_number'],
                'description' => $validated['description'],
                'receipt_file' => $receiptPath,
                'created_by' => Auth::id(),
            ]);

            // Create activity log
            StudentNote::create([
                'student_id' => $student->id,
                'content' => "💰 Revenue added: $" . number_format($revenue->amount, 2) . " via " . ucfirst(str_replace('_', ' ', $revenue->method)),
                'type' => 'log',
                'title' => 'Revenue Added',
                'created_by' => Auth::id(),
                'is_log' => true,
            ]);

            // Return JSON response for AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Revenue added successfully',
                    'data' => $revenue,
                    'student' => [
                        'expected_revenue' => $student->expected_revenue,
                        'received_revenue' => $student->received_revenue,
                        'remaining_due' => $student->remaining_due
                    ]
                ]);
            }

            return redirect()->back()->with('success', 'Revenue added successfully');
        } catch (\Exception $e) {
            Log::error('Revenue store error: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add revenue: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to add revenue: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Student $student, Revenue $revenue)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0',
                'method' => 'required|in:cash,bank_transfer,credit_card,cheque,online_payment',
                'transaction_date' => 'required|date',
                'reference_number' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            ]);

            if ($request->hasFile('receipt_file')) {
                if ($revenue->receipt_file && Storage::disk('public')->exists($revenue->receipt_file)) {
                    Storage::disk('public')->delete($revenue->receipt_file);
                }
                $validated['receipt_file'] = $request->file('receipt_file')->store('revenue-receipts', 'public');
            }

            $revenue->update($validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Revenue updated successfully',
                    'data' => $revenue,
                    'student' => [
                        'expected_revenue' => $student->expected_revenue,
                        'received_revenue' => $student->received_revenue,
                        'remaining_due' => $student->remaining_due
                    ]
                ]);
            }

            return redirect()->back()->with('success', 'Revenue updated successfully');
        } catch (\Exception $e) {
            Log::error('Revenue update error: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update revenue: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to update revenue: ' . $e->getMessage());
        }
    }

    public function show(Student $student, Revenue $revenue)
    {
        return response()->json([
            'success' => true,
            'data' => $revenue
        ]);
    }

    public function destroy(Student $student, Revenue $revenue)
    {
        try {
            if ($revenue->receipt_file && Storage::disk('public')->exists($revenue->receipt_file)) {
                Storage::disk('public')->delete($revenue->receipt_file);
            }

            $amount = $revenue->amount;
            $revenue->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Revenue of $" . number_format($amount, 2) . " deleted successfully",
                    'student' => [
                        'expected_revenue' => $student->expected_revenue,
                        'received_revenue' => $student->received_revenue,
                        'remaining_due' => $student->remaining_due
                    ]
                ]);
            }

            return redirect()->back()->with('success', "Revenue of $" . number_format($amount, 2) . " deleted successfully");
        } catch (\Exception $e) {
            Log::error('Revenue delete error: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete revenue: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to delete revenue: ' . $e->getMessage());
        }
    }
}
