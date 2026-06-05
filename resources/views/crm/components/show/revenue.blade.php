@php
    $expectedRevenue = $student->expected_revenue ?? 0;
    $collectedRevenue = $student->received_revenue ?? 0;
    $remainingDue = $student->remaining_due ?? 0;
    $revenuesCollection = $student->revenues ?? collect();

    // Helper function to get receipt URL via route
    function getReceiptUrl($path)
    {
        if (!$path) {
            return null;
        }
        return route('receipt.view', ['path' => $path]);
    }
@endphp

<div id="revenueSection">
    <div class="revenue-stats"
        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 1rem; margin-bottom: 1rem; color: white;">
        <div class="row">
            <div class="col-sm-4 text-center" style="padding: 0.5rem;">
                <div style="font-size: 0.7rem; opacity: 0.9; margin-bottom: 0.25rem;">Expected</div>
                <div class="revenue-stats-value" style="font-size: 0.55rem; font-weight: 700;">
                    ${{ number_format($expectedRevenue, 2) }}
                </div>
            </div>
            <div class="col-sm-4 text-center" style="padding: 0.5rem;">
                <div style="font-size: 0.7rem; opacity: 0.9; margin-bottom: 0.25rem;">Collected</div>
                <div class="revenue-stats-value" style="font-size: 0.55rem; font-weight: 700;">
                    ${{ number_format($collectedRevenue, 2) }}
                </div>
            </div>
            <div class="col-sm-4 text-center" style="padding: 0.5rem;">
                <div style="font-size: 0.7rem; opacity: 0.9; margin-bottom: 0.25rem;">Due</div>
                <div class="revenue-stats-value" style="font-size: 0.55rem; font-weight: 700;">
                    ${{ number_format($remainingDue, 2) }}
                </div>
            </div>
        </div>
    </div>

    <div class="revenue-list">
        <div class="revenue-title mt-2">📋 Transaction History</div>
        @forelse($revenuesCollection as $revenue)
            @php
                $receiptUrl = $revenue->receipt_file ? getReceiptUrl($revenue->receipt_file) : null;
            @endphp
            <div class="revenue-item d-flex flex-column justify-content-between align-items-start"
                data-revenue-id="{{ $revenue->id }}">
                <div class="flex-grow-1 w-100">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                        <strong>${{ number_format($revenue->amount, 2) }}</strong>
                        <span class="revenue-method-badge revenue-method-{{ $revenue->method }}">
                            {{ ucfirst(str_replace('_', ' ', $revenue->method)) }}
                        </span>
                        <small> 📅
                            @php
                                try {
                                    if ($revenue->transaction_date instanceof \DateTime) {
                                        echo $revenue->transaction_date->format('d M Y');
                                    } elseif (is_string($revenue->transaction_date)) {
                                        echo \Carbon\Carbon::parse($revenue->transaction_date)->format('d M Y');
                                    } else {
                                        echo 'Invalid date';
                                    }
                                } catch (\Exception $e) {
                                    echo 'Date error';
                                }
                            @endphp
                        </small>
                    </div>
                    @if ($revenue->description)
                        <div class="small text-muted mt-1">{{ $revenue->description }}</div>
                    @endif
                    <div class="small text-muted mt-1">
                        Verified by {{ $revenue->creator?->name ?? 'Unknown' }}
                    </div>

                    {{-- Receipt indicator with VIEW button in list --}}
                    @if ($receiptUrl)
                        <div class="mt-2"
                            style="background: #f0f9ff; padding: 8px 12px; border-radius: 6px; border-left: 3px solid #4299e1;">
                            <div
                                style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 16px;">📎</span>
                                    <span style="font-size: 12px; color: #2c5282;">
                                        Receipt: {{ basename($revenue->receipt_file) }}
                                    </span>
                                </div>
                                <button type="button" class="btn btn-sm btn-info"
                                    onclick="viewReceiptFromList('{{ $receiptUrl }}', '{{ basename($revenue->receipt_file) }}')"
                                    style="background: #4299e1; color: white; border: none; padding: 4px 12px; border-radius: 4px; font-size: 12px; cursor: pointer;">
                                    👁️ View Receipt
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
                @if (auth()->user()->is_admin)
                    <div class="mt-2 w-100">
                        <div class="d-flex gap-2 justify-content-end">
                            <button class="btn btn-sm btn-outline-primary"
                                onclick="openRevenueModal({{ $student->id }}, {{ $revenue->id }})">
                                ✏️ Edit
                            </button>
                            <button class="btn btn-sm btn-outline-danger"
                                onclick="deleteRevenue({{ $student->id }}, {{ $revenue->id }}, '{{ number_format($revenue->amount, 2) }}')">
                                🗑️ Delete
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="text-muted text-center py-3">No revenue records yet.</div>
        @endforelse
    </div>
</div>
