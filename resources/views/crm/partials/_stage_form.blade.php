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

<div class="cform-group">
    <label>Stage Name *</label>

    <input type="text" name="name" value="{{ old('name', $stage?->name) }}"
        placeholder="e.g. Counselling, Applied, Visa Approved…" required>
</div>

{{-- ───────────────── Color Picker ───────────────── --}}
<div class="cform-group">

    <label>Stage Color *</label>

    {{-- Hidden value --}}
    <input type="hidden" name="color" id="{{ $mode }}-color-input" value="{{ $selected }}">

    {{-- Preview --}}
    <div class="color-preview-row">

        <div class="color-preview-swatch" id="{{ $mode }}-preview-swatch"
            style="background: {{ $selected }}">
        </div>

        <span class="color-preview-label" id="{{ $mode }}-preview-label">
            {{ $selected }}
        </span>

        {{-- Native picker --}}
        <label class="color-native-btn">

            <input type="color" id="{{ $mode }}-native-picker" value="{{ $selected }}"
                oninput="syncColor(this, '{{ $mode }}')">

            🎨 Custom
        </label>
    </div>

    {{-- Presets --}}
    <div class="color-preset-grid" id="{{ $mode }}-preset-grid">

        @foreach ($presets as [$hex, $label])
            <button type="button" class="color-preset {{ $hex === $selected ? 'selected' : '' }}"
                data-color="{{ $hex }}" data-scope="{{ $mode }}" title="{{ $label }}"
                style="background: {{ $hex }}" onclick="selectPreset(this)">
            </button>
        @endforeach
    </div>
</div>

<div class="cform-group">
    <label>Description</label>

    <textarea name="description" rows="2" placeholder="What does this stage mean?">{{ old('description', $stage?->description) }}</textarea>
</div>

<div class="cform-group">
    <label>Max Days in Stage</label>

    <input type="number" name="max_days_in_stage" value="{{ old('max_days_in_stage', $stage?->max_days_in_stage) }}"
        placeholder="Leave empty for no limit" min="1">

    <div class="text-muted mt-1" style="font-size:.72rem">
        Students exceeding this will be flagged as overdue.
    </div>
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

@once

    @push('styles')
        <style>
            .color-preview-row {
                display: flex;
                align-items: center;
                gap: .65rem;
                margin-bottom: .65rem;
            }

            .color-preview-swatch {
                width: 32px;
                height: 32px;
                border-radius: 8px;
                border: 2px solid rgba(0, 0, 0, .12);
                flex-shrink: 0;
            }

            .color-preview-label {
                font-size: .8rem;
                font-family: monospace;
                color: #6b7280;
                flex: 1;
            }

            .color-native-btn {
                display: inline-flex;
                align-items: center;
                gap: .35rem;
                font-size: .78rem;
                font-weight: 500;
                padding: .3rem .65rem;
                border: 1px solid #e5e7eb;
                border-radius: 6px;
                cursor: pointer;
                background: #f9fafb;
            }

            .color-native-btn input[type="color"] {
                position: absolute;
                opacity: 0;
                width: 0;
                height: 0;
            }

            .color-preset-grid {
                display: grid;
                grid-template-columns: repeat(10, 1fr);
                gap: .35rem;
                padding: .65rem;
                background: #f9fafb;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
            }

            .color-preset {
                width: 100%;
                aspect-ratio: 1;
                border-radius: 6px;
                border: 2px solid transparent;
                cursor: pointer;
                transition: .15s;
                padding: 0;
            }

            .color-preset:hover {
                transform: scale(1.15);
            }

            .color-preset.selected {
                border-color: #fff;
                box-shadow: 0 0 0 2.5px #4f46e5;
                transform: scale(1.12);
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function selectPreset(btn) {

                const scope = btn.dataset.scope;
                const color = btn.dataset.color;

                const grid = document.getElementById(scope + '-preset-grid');

                grid.querySelectorAll('.color-preset')
                    .forEach(el => el.classList.remove('selected'));

                btn.classList.add('selected');

                applyColor(scope, color);
            }

            function syncColor(picker, scope) {

                const color = picker.value;

                document.getElementById(scope + '-preset-grid')
                    .querySelectorAll('.color-preset')
                    .forEach(btn => {
                        btn.classList.toggle(
                            'selected',
                            btn.dataset.color.toLowerCase() === color.toLowerCase()
                        );
                    });

                applyColor(scope, color);
            }

            function applyColor(scope, color) {

                document.getElementById(scope + '-color-input').value = color;

                document.getElementById(scope + '-preview-swatch')
                    .style.background = color;

                document.getElementById(scope + '-preview-label')
                    .textContent = color;

                document.getElementById(scope + '-native-picker')
                    .value = color;
            }
        </script>
    @endpush

@endonce
