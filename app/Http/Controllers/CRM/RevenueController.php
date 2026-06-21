<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Revenue;
use App\Models\Student;
use App\Models\StudentNote;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RevenueController extends Controller
{
    protected $currentAgent;

    public function __construct(
        private readonly FileUploadService $fileUploadService,
    ) {
        $this->middleware('auth');
        $this->middleware(\App\Http\Middleware\IsAdmin::class)->only(['destroy']);

        $this->middleware(function ($request, $next) {
            $this->currentAgent = Auth::user();
            return $next($request);
        });
    }

    /**
     * Store a newly created revenue record.
     */
    public function store(Request $request, Student $student)
    {
        try {
            while (ob_get_level()) {
                ob_end_clean();
            }

            $validated = $request->validate([
                'amount' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
                'method' => 'required|in:cash,bank_transfer,credit_card,cheque,online_payment',
                'transaction_date' => 'required|date',
                'reference_number' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            ]);

            // Clean and format the amount
            $amount = round((float) $validated['amount'], 2);

            // Log the amount for debugging
            Log::info('Revenue amount received', [
                'original' => $validated['amount'],
                'formatted' => $amount
            ]);

            // Handle receipt file upload
            $receiptPath = null;
            if ($request->hasFile('receipt_file')) {
                try {
                    $agent = $student->agent ?? Auth::user();
                    $receiptPath = $this->fileUploadService->uploadRevenueReceipt(
                        $request->file('receipt_file'),
                        $agent,
                        $student
                    );
                } catch (\InvalidArgumentException $e) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage()
                    ], 422);
                }
            }

            $revenue = $student->revenues()->create([
                'amount' => $amount, // Use the formatted amount
                'method' => $validated['method'],
                'transaction_date' => $validated['transaction_date'],
                'reference_number' => $validated['reference_number'] ?? null,
                'description' => $validated['description'] ?? null,
                'receipt_file' => $receiptPath,
                'created_by' => Auth::id(),
            ]);

            // Verify what was saved
            Log::info('Revenue saved', [
                'amount_saved' => $revenue->amount
            ]);

            try {
                StudentNote::create([
                    'student_id' => $student->id,
                    'content' => "💰 Revenue added: $" . number_format($revenue->amount, 2) . " via " . ucfirst(str_replace('_', ' ', $revenue->method)),
                    'type' => 'log',
                    'title' => 'Revenue Added',
                    'created_by' => Auth::id(),
                    'is_log' => true,
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to create revenue log: ' . $e->getMessage());
            }

            $student->refresh();
            $formattedRevenue = $this->formatRevenueData($revenue);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Revenue added successfully',
                    'data' => $formattedRevenue,
                    'student' => [
                        'expected_revenue' => (float) $student->expected_revenue,
                        'received_revenue' => (float) $student->received_revenue,
                        'remaining_due' => (float) $student->remaining_due
                    ]
                ]);
            }

            return redirect()->back()->with('success', 'Revenue added successfully');
        } catch (\Exception $e) {
            Log::error('Revenue store error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add revenue: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to add revenue: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified revenue record.
     */
    public function update(Request $request, Student $student, Revenue $revenue)
    {
        try {
            while (ob_get_level()) {
                ob_end_clean();
            }

            if ($revenue->student_id !== $student->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Revenue record does not belong to this student'
                ], 400);
            }

            $validated = $request->validate([
                'amount' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
                'method' => 'required|in:cash,bank_transfer,credit_card,cheque,online_payment',
                'transaction_date' => 'required|date',
                'reference_number' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            ]);

            // Clean and format the amount
            $amount = round((float) $validated['amount'], 2);

            $updateData = [
                'amount' => $amount,
                'method' => $validated['method'],
                'transaction_date' => $validated['transaction_date'],
                'reference_number' => $validated['reference_number'] ?? null,
                'description' => $validated['description'] ?? null,
            ];

            if ($request->has('remove_receipt') && $request->remove_receipt == '1') {
                if ($revenue->receipt_file && Storage::disk('public')->exists($revenue->receipt_file)) {
                    Storage::disk('public')->delete($revenue->receipt_file);
                }
                $updateData['receipt_file'] = null;
                Log::info('Receipt removed for revenue ID: ' . $revenue->id);
            }

            if ($request->hasFile('receipt_file')) {
                try {
                    $agent = $student->agent ?? Auth::user();
                    $updateData['receipt_file'] = $this->fileUploadService->uploadRevenueReceipt(
                        $request->file('receipt_file'),
                        $agent,
                        $student,
                        $revenue->receipt_file
                    );
                } catch (\InvalidArgumentException $e) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage()
                    ], 422);
                }
            }

            $revenue->update($updateData);
            $student->refresh();

            try {
                $logMessage = "✏️ Revenue updated: $" . number_format($revenue->amount, 2) . " via " . ucfirst(str_replace('_', ' ', $revenue->method));
                if ($request->has('remove_receipt') && $request->remove_receipt == '1') {
                    $logMessage .= " (Receipt removed)";
                }
                StudentNote::create([
                    'student_id' => $student->id,
                    'content' => $logMessage,
                    'type' => 'log',
                    'title' => 'Revenue Updated',
                    'created_by' => Auth::id(),
                    'is_log' => true,
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to create revenue update log: ' . $e->getMessage());
            }

            $formattedRevenue = $this->formatRevenueData($revenue->fresh());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Revenue updated successfully',
                    'data' => $formattedRevenue,
                    'student' => [
                        'expected_revenue' => (float) $student->expected_revenue,
                        'received_revenue' => (float) $student->received_revenue,
                        'remaining_due' => (float) $student->remaining_due
                    ]
                ]);
            }

            return redirect()->back()->with('success', 'Revenue updated successfully');
        } catch (\Exception $e) {
            Log::error('Revenue update error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update revenue: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to update revenue: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified revenue record.
     */
    public function show(Student $student, Revenue $revenue)
    {
        try {
            while (ob_get_level()) {
                ob_end_clean();
            }

            if ($revenue->student_id !== $student->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Revenue record not found'
                ], 404);
            }

            $formattedRevenue = $this->formatRevenueData($revenue);
            $formattedRevenue['receipt_url'] = $revenue->receipt_file
                ? $this->getReceiptUrl($revenue->receipt_file)
                : null;

            return response()->json([
                'success' => true,
                'data' => $formattedRevenue
            ]);
        } catch (\Exception $e) {
            Log::error('Revenue show error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch revenue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified revenue record.
     */
    public function destroy(Student $student, Revenue $revenue)
    {
        try {
            while (ob_get_level()) {
                ob_end_clean();
            }

            if ($revenue->student_id !== $student->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Revenue record does not belong to this student'
                ], 400);
            }

            if ($revenue->receipt_file && Storage::disk('public')->exists($revenue->receipt_file)) {
                Storage::disk('public')->delete($revenue->receipt_file);
                Log::info('Receipt file deleted for revenue ID: ' . $revenue->id);
            }

            $amount = $revenue->amount;
            $method = $revenue->method;
            $revenue->delete();
            $student->refresh();

            try {
                StudentNote::create([
                    'student_id' => $student->id,
                    'content' => "🗑️ Revenue deleted: $" . number_format($amount, 2) . " via " . ucfirst(str_replace('_', ' ', $method)),
                    'type' => 'log',
                    'title' => 'Revenue Deleted',
                    'created_by' => Auth::id(),
                    'is_log' => true,
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to create revenue deletion log: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => "Revenue of $" . number_format($amount, 2) . " deleted successfully",
                'student' => [
                    'expected_revenue' => (float) $student->expected_revenue,
                    'received_revenue' => (float) $student->received_revenue,
                    'remaining_due' => (float) $student->remaining_due
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Revenue delete error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete revenue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download receipt file
     */
    public function downloadReceipt(Student $student, Revenue $revenue)
    {
        try {
            if ($revenue->student_id !== $student->id) {
                abort(404, 'Receipt not found');
            }

            if (!$revenue->receipt_file || !Storage::disk('public')->exists($revenue->receipt_file)) {
                abort(404, 'Receipt file not found');
            }

            return Storage::disk('public')->download($revenue->receipt_file);
        } catch (\Exception $e) {
            Log::error('Receipt download error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to download receipt: ' . $e->getMessage());
        }
    }

    /**
     * Format revenue data for JSON response
     * 
     * @param Revenue $revenue
     * @return array
     */
    private function formatRevenueData(Revenue $revenue): array
    {
        return [
            'id' => $revenue->id,
            'student_id' => $revenue->student_id,
            'amount' => (float) $revenue->amount,
            'method' => $revenue->method,
            'transaction_date' => $revenue->transaction_date instanceof \DateTime
                ? $revenue->transaction_date->format('Y-m-d')
                : ($revenue->transaction_date ?? date('Y-m-d')),
            'reference_number' => $revenue->reference_number,
            'description' => $revenue->description,
            'receipt_file' => $revenue->receipt_file,
            'receipt_url' => $revenue->receipt_file ? $this->getReceiptUrl($revenue->receipt_file) : null,
            'created_by' => $revenue->created_by,
            'created_at' => $revenue->created_at ? $revenue->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $revenue->updated_at ? $revenue->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
    /**
     * Get receipt URL with proper handling (avoid 403)
     */
    private function getReceiptUrl($path)
    {
        if (!$path) {
            return null;
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($path)) {
            Log::warning('Receipt file not found: ' . $path);
            return null;
        }

        // Use route instead of direct storage URL to avoid 403
        return route('receipt.view', ['path' => $path]);
    }
}
