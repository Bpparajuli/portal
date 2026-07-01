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

<div id="revenueSection" style="border-top:1px solid #e8e5ee;margin-top:4px;padding-top:4px;">
    <div style="font-size:.68rem;font-weight:700;color:#64748b;padding:.4rem .6rem .2rem;">📋 Transaction History</div>
    <div class="revenue-list">
        @forelse($revenuesCollection as $revenue)
            @php
                $receiptUrl = $revenue->receipt_file ? getReceiptUrl($revenue->receipt_file) : null;
                $dateStr = '';
                try {
                    if ($revenue->transaction_date instanceof \DateTime) {
                        $dateStr = $revenue->transaction_date->format('d M Y');
                    } elseif (is_string($revenue->transaction_date)) {
                        $dateStr = \Carbon\Carbon::parse($revenue->transaction_date)->format('d M Y');
                    } else {
                        $dateStr = '—';
                    }
                } catch (\Exception $e) {
                    $dateStr = '—';
                }
            @endphp
            <div class="revenue-item" data-revenue-id="{{ $revenue->id }}"
                style="border-bottom:1px solid #f1f0f5;padding:.4rem .6rem;">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="revenue-method-badge revenue-method-{{ $revenue->method }}"
                        style="font-size:.62rem;padding:.15rem .5rem;border-radius:6px;">
                        {{ ucfirst(str_replace('_', ' ', $revenue->method)) }}
                    </span>
                    <strong style="font-size:.85rem;color:#1a0262;">${{ number_format($revenue->amount, 2) }}</strong>
                </div>
                <div style="font-size:.68rem;color:#64748b;">
                    <span>{{ $dateStr }}</span>
                    <span class="mx-1">•</span>
                    <span>{{ $revenue->creator?->name ?? 'Unknown' }}</span>
                </div>
                @if ($revenue->description)
                    <div style="font-size:.68rem;color:#475569;margin-top:2px;">{{ $revenue->description }}</div>
                @endif
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:4px;">
                    @if ($receiptUrl)
                        <button type="button" class="btn btn-sm"
                            onclick="viewReceiptFromList('{{ $receiptUrl }}', '{{ basename($revenue->receipt_file) }}')"
                            style="background:#ede5f8;color:#1a0262;border:1px solid #d4c4ec;border-radius:6px;font-size:.62rem;padding:1px 10px;cursor:pointer;">
                            📎 Receipt
                        </button>
                    @else
                        <div></div>
                    @endif
                    <div class="d-flex gap-1">
                        <a href="{{ route('crm.student.revenues.print', ['student' => $student, 'revenue' => $revenue]) }}" target="_blank"
                            style="background:#1a0262;color:#fff;border:none;border-radius:6px;font-size:.65rem;padding:2px 12px;cursor:pointer;text-decoration:none;font-weight:600;white-space:nowrap;">
                            🖨️ Print Receipt
                        </a>
                        <button class="btn btn-sm"
                            onclick="openRevenueModal({{ $student->id }}, {{ $revenue->id }})"
                            style="background:transparent;color:#820b5c;border:1px solid #d4c4ec;border-radius:6px;font-size:.62rem;padding:1px 8px;cursor:pointer;">✏️</button>
                        @if (auth()->user()->is_admin)
                            <button class="btn btn-sm"
                                onclick="deleteRevenue({{ $student->id }}, {{ $revenue->id }}, '{{ number_format($revenue->amount, 2) }}')"
                                style="background:transparent;color:#ef4444;border:1px solid #fecaca;border-radius:6px;font-size:.62rem;padding:1px 8px;cursor:pointer;">🗑️</button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-muted text-center py-3" style="font-size:.75rem">No revenue records yet.</div>
        @endforelse
    </div>
</div>
