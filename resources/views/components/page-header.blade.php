@props(['title', 'subtitle' => null])
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="mb-1 fw-bold" style="font-size:var(--text-xl);">{{ $title }}</h4>
        @if($subtitle)
            <p class="text-muted mb-0" style="font-size:var(--text-sm);">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="d-flex gap-2">
        {{ $actions ?? '' }}
    </div>
</div>
