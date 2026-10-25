@props(['type' => null])

@php
    $meta = \App\Models\CrmTasks::activityMeta($type);
@endphp

<span {{ $attributes->merge(['class' => 'task-type-badge']) }}
    style="--tt-bg:{{ $meta['bg'] }};--tt-color:{{ $meta['color'] }};">
    <span class="task-type-badge__icon">{{ $meta['icon'] }}</span>{{ $meta['label'] }}
</span>
