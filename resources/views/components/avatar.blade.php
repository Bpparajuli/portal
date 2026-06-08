@props(['name', 'size' => 'md', 'class' => '', 'image' => null])
@php
$initials = collect(explode(' ', $name))->map(fn($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('');
$sizes = ['sm' => '32px', 'md' => '40px', 'lg' => '56px', 'xl' => '72px'];
$fontSizes = ['sm' => '0.75rem', 'md' => '0.875rem', 'lg' => '1.25rem', 'xl' => '1.5rem'];
$px = $sizes[$size] ?? $sizes['md'];
$fs = $fontSizes[$size] ?? $fontSizes['md'];
@endphp
@if($image)
<img src="{{ $image }}" alt="{{ $name }}" class="rounded-circle object-fit-cover {{ $class }}"
     style="width: {{ $px }}; height: {{ $px }};">
@else
<div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary fw-bold {{ $class }}"
     style="width: {{ $px }}; height: {{ $px }}; font-size: {{ $fs }};">
    {{ $initials }}
</div>
@endif
