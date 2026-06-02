{{-- resources/views/crm/components/show/revenue.blade.php --}}
<div class="revenue-stats"
    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 1rem; margin-bottom: 1rem; color: white;">
    <div class="row">
        <div class="col-sm-4 text-center" style="padding: 0.5rem;">
            <div style="font-size: 0.7rem; opacity: 0.9; margin-bottom: 0.25rem;">Expected</div>
            <div style="font-size: 0.55rem; font-weight: 700;">${{ number_format($expectedRevenue, 2) }}
            </div>
        </div>
        <div class="col-sm-4 text-center" style="padding: 0.5rem;">
            <div style="font-size: 0.7rem; opacity: 0.9; margin-bottom: 0.25rem;">Collected</div>
            <div style="font-size: 0.55rem; font-weight: 700;">${{ number_format($collectedRevenue, 2) }}
            </div>
        </div>
        <div class="col-sm-4 text-center" style="padding: 0.5rem;">
            <div style="font-size: 0.7rem; opacity: 0.9; margin-bottom: 0.25rem;">Due</div>
            <div style="font-size: 0.55rem; font-weight: 700;">${{ number_format($remainingDue, 2) }}
            </div>
        </div>
    </div>
</div>
<div class="revenue-list">
    <div class="revenue-title mt-2">📋 Transaction History</div>
    @forelse($revenuesCollection ?? [] as $revenue)
        <div class="revenue-item d-flex flex-column justify-content-between align-items-start">
            <div class="flex-grow-1">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                    <strong>${{ number_format($revenue->amount, 2) }}</strong>
                    <span class="revenue-method-badge revenue-method-{{ $revenue->method }}">
                        {{ ucfirst(str_replace('_', ' ', $revenue->method)) }}
                    </span>
                    <small> 📅 {{ $revenue->transaction_date->format('d M Y') }}</small>
                </div>
                @if ($revenue->description)
                    <div class="small text-muted mt-1">{{ $revenue->description }}</div>
                @endif

            </div>
            <div class="d-flex justify-content-between">
                <div class="small text-muted mt-1">
                    Verified by {{ $revenue->creator?->name ?? 'Unknown' }}
                </div>
                @if (auth()->user()->is_admin)
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary"
                            onclick="openRevenueModal({{ $student->id }}, {{ $revenue->id }})">
                            ✏️
                        </button>
                        <button class="btn btn-sm btn-outline-danger"
                            onclick="deleteRevenue({{ $student->id }}, {{ $revenue->id }}, '${{ number_format($revenue->amount, 2) }}')">
                            🗑️
                        </button>
                    </div>
                @endif
            </div>

        </div>
    @empty
        <div class="text-muted text-center py-3">No revenue records yet.</div>
    @endforelse
</div>
