{{-- resources/views/crm/partials/_stage_form.blade.php --}}
{{-- Shared by configure.blade.php for both create and edit forms --}}

@php
    $selected = $stage?->color ?? '#3b82f6';

    // Preset palette — covers a wide hue range at consistent saturation/lightness
    $presets = [
        // Reds
        ['#ef4444', 'Red'],
        ['#dc2626', 'Red dark'],
        // Oranges
        ['#f97316', 'Orange'],
        ['#ea580c', 'Orange dark'],
        // Ambers
        ['#f59e0b', 'Amber'],
        ['#d97706', 'Amber dark'],
        // Yellows
        ['#eab308', 'Yellow'],
        ['#ca8a04', 'Yellow dark'],
        // Limes
        ['#84cc16', 'Lime'],
        ['#65a30d', 'Lime dark'],
        // Greens
        ['#22c55e', 'Green'],
        ['#16a34a', 'Green dark'],
        // Emeralds
        ['#10b981', 'Emerald'],
        ['#059669', 'Emerald dark'],
        // Teals
        ['#14b8a6', 'Teal'],
        ['#0d9488', 'Teal dark'],
        // Cyans
        ['#06b6d4', 'Cyan'],
        ['#0891b2', 'Cyan dark'],
        // Sky
        ['#0ea5e9', 'Sky'],
        ['#0284c7', 'Sky dark'],
        // Blues
        ['#3b82f6', 'Blue'],
        ['#2563eb', 'Blue dark'],
        // Indigos
        ['#6366f1', 'Indigo'],
        ['#4f46e5', 'Indigo dark'],
        // Violets
        ['#8b5cf6', 'Violet'],
        ['#7c3aed', 'Violet dark'],
        // Purples
        ['#a855f7', 'Purple'],
        ['#9333ea', 'Purple dark'],
        // Fuchsia
        ['#d946ef', 'Fuchsia'],
        ['#c026d3', 'Fuchsia dark'],
        // Pinks
        ['#ec4899', 'Pink'],
        ['#db2777', 'Pink dark'],
        // Roses
        ['#f43f5e', 'Rose'],
        ['#e11d48', 'Rose dark'],
        // Neutrals
        ['#64748b', 'Slate'],
        ['#475569', 'Slate dark'],
        ['#6b7280', 'Gray'],
        ['#374151', 'Gray dark'],
    ];
@endphp

<div class="form-group">
    <label>Stage Name *</label>
    <input type="text" name="name" value="{{ old('name', $stage?->name) }}"
        placeholder="e.g. Counselling, Applied, Visa Approved…" required>
</div>

{{-- ── Color picker ── --}}
<div class="form-group">
    <label>Stage Color *</label>
    <input type="hidden" name="color" id="{{ isset($stage) ? 'edit' : 'create' }}-color-input"
        value="{{ $selected }}">

    {{-- Live preview swatch --}}
    <div class="color-preview-row">
        <div class="color-preview-swatch" id="{{ isset($stage) ? 'edit' : 'create' }}-preview-swatch"
            style="background: {{ $selected }}"></div>
        <span class="color-preview-label" id="{{ isset($stage) ? 'edit' : 'create' }}-preview-label">
            {{ $selected }}
        </span>
        {{-- Native color input for free picking --}}
        <label class="color-native-btn" title="Pick any color">
            <input type="color" id="{{ isset($stage) ? 'edit' : 'create' }}-native-picker" value="{{ $selected }}"
                oninput="syncColor(this, '{{ isset($stage) ? 'edit' : 'create' }}')">
            🎨 Custom
        </label>
    </div>

    {{-- Preset palette grid --}}
    <div class="color-preset-grid" id="{{ isset($stage) ? 'edit' : 'create' }}-preset-grid">
        @foreach ($presets as [$hex, $label])
            <button type="button" class="color-preset {{ $hex === $selected ? 'selected' : '' }}"
                data-color="{{ $hex }}" data-scope="{{ isset($stage) ? 'edit' : 'create' }}"
                title="{{ $label }}" style="background: {{ $hex }}" onclick="selectPreset(this)">
            </button>
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
            /* ── Color picker styles ── */
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
                transition: background .15s;
            }

            .color-preview-label {
                font-size: .8rem;
                font-family: monospace;
                color: var(--crm-muted);
                flex: 1;
            }

            .color-native-btn {
                display: inline-flex;
                align-items: center;
                gap: .35rem;
                font-size: .78rem;
                font-weight: 500;
                padding: .3rem .65rem;
                border: 1px solid var(--crm-border);
                border-radius: 6px;
                cursor: pointer;
                background: var(--crm-bg);
                color: var(--crm-text);
                white-space: nowrap;
                transition: all .15s;
                margin: 0;
            }

            .color-native-btn:hover {
                border-color: var(--crm-primary);
                color: var(--crm-primary);
            }

            .color-native-btn input[type="color"] {
                /* hide the native input visually but keep it functional */
                position: absolute;
                opacity: 0;
                width: 0;
                height: 0;
                pointer-events: none;
            }

            .color-preset-grid {
                display: grid;
                grid-template-columns: repeat(10, 1fr);
                gap: .35rem;
                padding: .65rem;
                background: var(--crm-bg);
                border: 1px solid var(--crm-border);
                border-radius: 8px;
            }

            @media (max-width: 480px) {
                .color-preset-grid {
                    grid-template-columns: repeat(7, 1fr);
                }
            }

            .color-preset {
                width: 100%;
                aspect-ratio: 1;
                border-radius: 6px;
                border: 2px solid transparent;
                cursor: pointer;
                transition: transform .1s, border-color .1s, box-shadow .1s;
                padding: 0;
            }

            .color-preset:hover {
                transform: scale(1.18);
                box-shadow: 0 2px 8px rgba(0, 0, 0, .2);
            }

            .color-preset.selected {
                border-color: #fff;
                box-shadow: 0 0 0 2.5px var(--crm-primary), 0 2px 8px rgba(0, 0, 0, .2);
                transform: scale(1.12);
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            /**
             * Called when a preset swatch button is clicked.
             * scope: 'create' | 'edit'  (keeps the two forms independent)
             */
            function selectPreset(btn) {
                const scope = btn.dataset.scope;
                const color = btn.dataset.color;

                // Deselect siblings in same grid
                btn.closest('.color-preset-grid')
                    .querySelectorAll('.color-preset')
                    .forEach(b => b.classList.remove('selected'));

                btn.classList.add('selected');
                applyColor(scope, color);
            }

            /**
             * Called by the native <input type="color"> oninput.
             */
            function syncColor(picker, scope) {
                const color = picker.value;

                // Deselect all presets in scope — custom color, no preset match
                document.getElementById(scope + '-preset-grid')
                    .querySelectorAll('.color-preset')
                    .forEach(b => {
                        b.classList.toggle('selected', b.dataset.color === color);
                    });

                applyColor(scope, color);
            }

            /**
             * Shared: update the hidden input, preview swatch, label, and native picker.
             */
            function applyColor(scope, color) {
                document.getElementById(scope + '-color-input').value = color;
                document.getElementById(scope + '-preview-swatch').style.background = color;
                document.getElementById(scope + '-preview-label').textContent = color;
                document.getElementById(scope + '-native-picker').value = color;
            }
        </script>
    @endpush
@endonce
