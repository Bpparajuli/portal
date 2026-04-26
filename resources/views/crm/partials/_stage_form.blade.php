{{-- resources/views/crm/partials/_stage_form.blade.php --}}
{{-- Shared by configure.blade.php for both create (#stageCreateForm) and edit (#stageEditForm) --}}

@php
    $colors = [
        '#ef4444',
        '#f97316',
        '#f59e0b',
        '#eab308',
        '#84cc16',
        '#22c55e',
        '#10b981',
        '#14b8a6',
        '#06b6d4',
        '#3b82f6',
        '#6366f1',
        '#8b5cf6',
        '#a855f7',
        '#ec4899',
        '#64748b',
    ];
    $selected = $stage?->color ?? '#3b82f6';
@endphp

<div class="form-group">
    <label>Stage Name *</label>
    <input type="text" name="name" value="{{ old('name', $stage?->name) }}" placeholder="e.g. Counselling, Applied…"
        required>
</div>

<div class="form-group">
    <label>Color *</label>
    <input type="hidden" name="color" value="{{ $selected }}">
    <div class="color-grid">
        @foreach ($colors as $color)
            <div class="color-swatch {{ $color === $selected ? 'selected' : '' }}"
                style="background:{{ $color }}" data-color="{{ $color }}" title="{{ $color }}">
            </div>
        @endforeach
    </div>
</div>

<div class="form-group">
    <label>Description</label>
    <textarea name="description" rows="2" placeholder="What does this stage mean?">{{ old('description', $stage?->description) }}</textarea>
</div>

<div class="form-group">
    <label>Max Days in Stage</label>
    <input type="number" name="max_days_in_stage" value="{{ old('max_days_in_stage', $stage?->max_days_in_stage) }}"
        placeholder="Leave empty for no limit" min="1">
    <div class="text-muted mt-1" style="font-size:.72rem">Students exceeding this will be flagged as overdue.</div>
</div>

<div class="toggle-row">
    <label>Mark as "Won" stage</label>
    <label class="toggle-switch">
        <input type="checkbox" name="is_won_stage" value="1"
            {{ old('is_won_stage', $stage?->is_won_stage) ? 'checked' : '' }}>
        <span class="toggle-slider"></span>
    </label>
</div>

<div class="toggle-row">
    <label>Mark as "Lost" stage</label>
    <label class="toggle-switch">
        <input type="checkbox" name="is_lost_stage" value="1"
            {{ old('is_lost_stage', $stage?->is_lost_stage) ? 'checked' : '' }}>
        <span class="toggle-slider"></span>
    </label>
</div>
