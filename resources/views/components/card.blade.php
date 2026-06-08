@props(['title' => null, 'subtitle' => null, 'padding' => true, 'class' => ''])
<div class="card {{ $class }}">
    @if($title || $subtitle || !empty($header))
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            @if($title)<h6 class="mb-0 fw-semibold">{{ $title }}</h6>@endif
            @if($subtitle)<small class="text-muted">{{ $subtitle }}</small>@endif
        </div>
        @if(isset($header))<div>{{ $header }}</div>@endif
    </div>
    @endif
    <div class="{{ $padding ? 'card-body' : '' }}">
        {{ $slot }}
    </div>
    @if(isset($footer))
    <div class="card-footer">
        {{ $footer }}
    </div>
    @endif
</div>
