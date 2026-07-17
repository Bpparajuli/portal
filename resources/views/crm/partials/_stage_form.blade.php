{{-- resources/views/crm/partials/_stage_form.blade.php --}}
{{-- REQUIRED:
    Pass:
    'mode' => 'create'
    OR
    'mode' => 'edit'
--}}

@php
    $selected = old('color', $stage?->color ?? '#3b82f6');

    $presets = [
        ['#ef4444', 'Red'],
        ['#dc2626', 'Red dark'],
        ['#f97316', 'Orange'],
        ['#ea580c', 'Orange dark'],
        ['#f59e0b', 'Amber'],
        ['#d97706', 'Amber dark'],
        ['#eab308', 'Yellow'],
        ['#ca8a04', 'Yellow dark'],
        ['#84cc16', 'Lime'],
        ['#65a30d', 'Lime dark'],
        ['#22c55e', 'Green'],
        ['#16a34a', 'Green dark'],
        ['#10b981', 'Emerald'],
        ['#059669', 'Emerald dark'],
        ['#14b8a6', 'Teal'],
        ['#0d9488', 'Teal dark'],
        ['#06b6d4', 'Cyan'],
        ['#0891b2', 'Cyan dark'],
        ['#0ea5e9', 'Sky'],
        ['#0284c7', 'Sky dark'],
        ['#3b82f6', 'Blue'],
        ['#2563eb', 'Blue dark'],
        ['#6366f1', 'Indigo'],
        ['#4f46e5', 'Indigo dark'],
        ['#8b5cf6', 'Violet'],
        ['#7c3aed', 'Violet dark'],
        ['#a855f7', 'Purple'],
        ['#9333ea', 'Purple dark'],
        ['#d946ef', 'Fuchsia'],
        ['#c026d3', 'Fuchsia dark'],
        ['#ec4899', 'Pink'],
        ['#db2777', 'Pink dark'],
        ['#f43f5e', 'Rose'],
        ['#e11d48', 'Rose dark'],
        ['#64748b', 'Slate'],
        ['#475569', 'Slate dark'],
        ['#6b7280', 'Gray'],
        ['#374151', 'Gray dark'],
    ];
@endphp

<div class="mb-3">
    <label class="form-label">Stage Name *</label>
    <input type="text" class="form-control" name="name" value="{{ old('name', $stage?->name) }}"
        placeholder="e.g. Counselling, Applied, Visa Approved…" required>
</div>

<div class="mb-3">
    <label class="form-label">Stage Color *</label>
    <input type="hidden" name="color" id="{{ $mode }}-color-input" value="{{ $selected }}">
    <div class="cfg-color-row">
        <div class="cfg-color-swatch" id="{{ $mode }}-preview-swatch" style="background:{{ $selected }}"></div>
        <span class="cfg-color-hex" id="{{ $mode }}-preview-label">{{ $selected }}</span>
        <label class="cfg-color-custom">
            <i class="fas fa-palette me-1"></i> Custom
            <input type="color" id="{{ $mode }}-native-picker" value="{{ $selected }}"
                oninput="syncColor(this, '{{ $mode }}')">
        </label>
    </div>
    <div class="cfg-color-grid" id="{{ $mode }}-preset-grid">
        @foreach ($presets as [$hex, $label])
            <button type="button" class="cfg-color-swatch-btn {{ $hex === $selected ? 'selected' : '' }}"
                data-color="{{ $hex }}" data-scope="{{ $mode }}" title="{{ $label }}"
                style="background:{{ $hex }}" onclick="selectPreset(this)">
            </button>
        @endforeach
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea class="form-control" name="description" rows="2" placeholder="What does this stage mean?">{{ old('description', $stage?->description) }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">Max Days in Stage</label>
    <input type="number" class="form-control" name="max_days_in_stage"
        value="{{ old('max_days_in_stage', $stage?->max_days_in_stage) }}"
        placeholder="Leave empty for no limit" min="1">
    <div class="form-text">Students exceeding this will be flagged as overdue.</div>
</div>

<div class="border-top pt-3 mt-3">
    <div class="cfg-toggle">
        <label for="{{ $mode }}-won">Mark as "Won" stage</label>
        <label class="cfg-switch">
            <input type="checkbox" id="{{ $mode }}-won" name="is_won_stage" value="1"
                {{ old('is_won_stage', $stage?->is_won_stage) ? 'checked' : '' }}>
            <span class="cfg-switch-slider"></span>
        </label>
    </div>
    <div class="cfg-toggle">
        <label for="{{ $mode }}-lost">Mark as "Lost" stage</label>
        <label class="cfg-switch">
            <input type="checkbox" id="{{ $mode }}-lost" name="is_lost_stage" value="1"
                {{ old('is_lost_stage', $stage?->is_lost_stage) ? 'checked' : '' }}>
            <span class="cfg-switch-slider"></span>
        </label>
    </div>
</div>

@once
    @push('scripts')
        <script>
            function selectPreset(btn) {
                const scope = btn.dataset.scope;
                const color = btn.dataset.color;
                const grid = document.getElementById(scope + '-preset-grid');
                grid.querySelectorAll('.cfg-color-swatch-btn').forEach(el => el.classList.remove('selected'));
                btn.classList.add('selected');
                applyColor(scope, color);
            }

            function syncColor(picker, scope) {
                const color = picker.value;
                document.getElementById(scope + '-preset-grid')
                    .querySelectorAll('.cfg-color-swatch-btn')
                    .forEach(btn => {
                        btn.classList.toggle('selected', btn.dataset.color.toLowerCase() === color.toLowerCase());
                    });
                applyColor(scope, color);
            }

            function applyColor(scope, color) {
                document.getElementById(scope + '-color-input').value = color;
                document.getElementById(scope + '-preview-swatch').style.background = color;
                document.getElementById(scope + '-preview-label').textContent = color;
                document.getElementById(scope + '-native-picker').value = color;
            }
        </script>
    @endpush
@endonce
