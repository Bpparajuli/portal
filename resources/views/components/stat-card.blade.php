@props(['icon', 'value', 'label', 'trend' => null, 'trendLabel' => null, 'color' => 'primary', 'link' => null])
<div class="card stat-card shadow-sm h-100">
    <div class="card-body">
        @if($link)
            <a href="{{ $link }}">
        @endif
        <div class="d-flex align-items-center gap-3">
            <div class="stat-icon bg-{{ $color }} bg-opacity-10 text-{{ $color }}">
                <i class="fas {{ $icon }}"></i>
            </div>
            <div class="flex-grow-1 min-w-0">
                <div class="fs-3 fw-bold text-dark mb-0">{{ $value }}</div>
                <div class="text-muted small">{{ $label }}</div>
            </div>
        </div>
        @if($trend !== null)
            <div class="mt-3 d-flex align-items-center gap-1 stat-trend text-{{ $trend >= 0 ? 'success' : 'danger' }}">
                <i class="fas fa-{{ $trend >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                <span>{{ abs($trend) }}%</span>
                @if($trendLabel)
                    <span class="text-muted fw-normal ms-1">{{ $trendLabel }}</span>
                @endif
            </div>
        @endif
        @if($link)
            </a>
        @endif
    </div>
</div>
