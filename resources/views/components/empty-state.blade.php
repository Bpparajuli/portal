@props(['icon' => 'fa-inbox', 'title', 'description' => null, 'actionLabel' => null, 'actionUrl' => null])
<div class="empty-state">
    <div class="empty-state-icon mb-3">
        <i class="fas {{ $icon }}"></i>
    </div>
    <h6 class="fw-semibold">{{ $title }}</h6>
    @if($description)
        <p class="text-muted small mb-4" style="max-width:400px;">{{ $description }}</p>
    @endif
    @if($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>{{ $actionLabel }}
        </a>
    @endif
</div>
