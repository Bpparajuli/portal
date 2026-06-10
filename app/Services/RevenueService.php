<?php
namespace App\Services;

use App\Models\Revenue;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RevenueService
{
    /**
     * Create a revenue record for a student.
     *
     * Optionally handles receipt file upload via the request.
     *
     * @return Revenue
     */
    public function store(Student $student, Request $request): Revenue
    {
        $data = $request->validate([
            'amount'           => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'payment_method'   => 'nullable|string|max:255',
            'reference'        => 'nullable|string|max:255',
            'remarks'          => 'nullable|string',
            'type'             => 'nullable|string|in:tuition,application,service,other',
            'receipt'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('receipt')) {
            $data['receipt'] = $request->file('receipt')->store('revenues/receipts', 'public');
        }

        $data['student_id'] = $student->id;
        $data['created_by'] = Auth::id();

        return Revenue::create($data);
    }

    /**
     * Update a revenue record.
     */
    public function update(Revenue $revenue, Request $request): Revenue
    {
        $data = $request->validate([
            'amount'           => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'payment_method'   => 'nullable|string|max:255',
            'reference'        => 'nullable|string|max:255',
            'remarks'          => 'nullable|string',
            'type'             => 'nullable|string|in:tuition,application,service,other',
            'receipt'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('receipt')) {
            if ($revenue->receipt && \Illuminate\Support\Facades\Storage::disk('public')->exists($revenue->receipt)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($revenue->receipt);
            }
            $data['receipt'] = $request->file('receipt')->store('revenues/receipts', 'public');
        }

        $revenue->update($data);
        return $revenue->fresh();
    }

    /**
     * Delete a revenue record and its receipt file.
     */
    public function destroy(Revenue $revenue): void
    {
        if ($revenue->receipt && \Illuminate\Support\Facades\Storage::disk('public')->exists($revenue->receipt)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($revenue->receipt);
        }
        $revenue->delete();
    }

    /**
     * Get receipt file path for download.
     */
    public function getReceiptPath(Revenue $revenue): string
    {
        return storage_path('app/public/' . $revenue->receipt);
    }

    /**
     * Get revenue summary for a student.
     *
     * @return array  Keys: expectedRevenue, collectedRevenue, remainingDue.
     */
    public function getStudentSummary(Student $student): array
    {
        $expectedRevenue = $student->expected_revenue ?? 0;
        $collectedRevenue = $student->received_revenue ?? 0;
        $remainingDue = max(0, $expectedRevenue - $collectedRevenue);
        return compact('expectedRevenue', 'collectedRevenue', 'remainingDue');
    }
}
