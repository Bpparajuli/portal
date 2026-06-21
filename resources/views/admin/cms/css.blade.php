@extends('layouts.admin')
@section('admin-content')
    <x-page-header title="Design & CSS" subtitle="Theme colors, typography, layout, and custom CSS overrides" />

    <form method="POST" action="{{ route('admin.css.update') }}">
        @csrf

        <div class="row g-3">

            {{-- LEFT COLUMN --}}
            <div class="col-lg-7">

                {{-- ═══ THEME COLORS ═══ --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2 px-3 border-bottom">
                        <span class="fw-semibold small"><i class="fas fa-palette me-1 text-primary"></i>Theme Colors</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2">
                            @foreach ([
                                ['k' => 'theme_primary', 'l' => 'Primary', 'def' => '#1a0262'],
                                ['k' => 'theme_secondary', 'l' => 'Secondary', 'def' => '#820b5c'],
                                ['k' => 'theme_success', 'l' => 'Success', 'def' => '#10b981'],
                                ['k' => 'theme_warning', 'l' => 'Warning', 'def' => '#f59e0b'],
                                ['k' => 'theme_info', 'l' => 'Info', 'def' => '#3b82f6'],
                                ['k' => 'theme_danger', 'l' => 'Danger', 'def' => '#ef4444'],
                                ['k' => 'theme_bg', 'l' => 'Body Bg', 'def' => '#f8fafc'],
                                ['k' => 'theme_text', 'l' => 'Body Text', 'def' => '#1e293b'],
                                ['k' => 'theme_heading_color', 'l' => 'Heading Color', 'def' => '#0f172a'],
                                ['k' => 'theme_card_bg', 'l' => 'Card Bg', 'def' => '#ffffff'],
                            ] as $item)
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">{{ $item['l'] }}</label>
                                    <div class="d-flex gap-1">
                                        <input type="color" name="{{ $item['k'] }}" class="form-control form-control-color p-0 border-0" style="width:34px;height:34px;" value="{{ $theme[$item['k']] ?? $item['def'] }}">
                                        <input type="text" class="form-control form-control-sm" style="font-size:11px;" value="{{ $theme[$item['k']] ?? $item['def'] }}" oninput="this.previousElementSibling.value=this.value">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ═══ GRADIENT & TABLE ═══ --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2 px-3 border-bottom">
                        <span class="fw-semibold small"><i class="fas fa-fill-drip me-1 text-warning"></i>Gradient & Table</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Btn Gradient From</label>
                                <div class="d-flex gap-1">
                                    <input type="color" name="theme_btn_gradient_from" class="form-control form-control-color p-0 border-0" style="width:34px;height:34px;" value="{{ $theme['theme_btn_gradient_from'] ?? '#1a0262' }}">
                                    <input type="text" class="form-control form-control-sm" style="font-size:11px;" value="{{ $theme['theme_btn_gradient_from'] ?? '#1a0262' }}" oninput="this.previousElementSibling.value=this.value">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Btn Gradient To</label>
                                <div class="d-flex gap-1">
                                    <input type="color" name="theme_btn_gradient_to" class="form-control form-control-color p-0 border-0" style="width:34px;height:34px;" value="{{ $theme['theme_btn_gradient_to'] ?? '#2c0e8a' }}">
                                    <input type="text" class="form-control form-control-sm" style="font-size:11px;" value="{{ $theme['theme_btn_gradient_to'] ?? '#2c0e8a' }}" oninput="this.previousElementSibling.value=this.value">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Table Header Bg</label>
                                <div class="d-flex gap-1">
                                    <input type="color" name="theme_table_header_bg" class="form-control form-control-color p-0 border-0" style="width:34px;height:34px;" value="{{ $theme['theme_table_header_bg'] ?? '#f0edfa' }}">
                                    <input type="text" class="form-control form-control-sm" style="font-size:11px;" value="{{ $theme['theme_table_header_bg'] ?? '#f0edfa' }}" oninput="this.previousElementSibling.value=this.value">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Table Header Color</label>
                                <div class="d-flex gap-1">
                                    <input type="color" name="theme_table_header_color" class="form-control form-control-color p-0 border-0" style="width:34px;height:34px;" value="{{ $theme['theme_table_header_color'] ?? '#1e293b' }}">
                                    <input type="text" class="form-control form-control-sm" style="font-size:11px;" value="{{ $theme['theme_table_header_color'] ?? '#1e293b' }}" oninput="this.previousElementSibling.value=this.value">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ═══ TYPOGRAPHY ═══ --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2 px-3 border-bottom">
                        <span class="fw-semibold small"><i class="fas fa-font me-1 text-info"></i>Typography</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Header Font</label>
                                <input type="text" name="design_header_font" class="form-control form-control-sm" style="font-size:12px;" value="{{ $design['design_header_font'] ?? "'Inter', sans-serif" }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Body Font</label>
                                <input type="text" name="design_body_font" class="form-control form-control-sm" style="font-size:12px;" value="{{ $design['design_body_font'] ?? "'Inter', sans-serif" }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Header Weight</label>
                                <input type="text" name="design_header_weight" class="form-control form-control-sm" style="font-size:12px;" value="{{ $design['design_header_weight'] ?? '700' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Body Font Size</label>
                                <input type="text" name="design_body_size" class="form-control form-control-sm" style="font-size:12px;" value="{{ $design['design_body_size'] ?? '0.875rem' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Line Height</label>
                                <input type="text" name="design_line_height" class="form-control form-control-sm" style="font-size:12px;" value="{{ $design['design_line_height'] ?? '1.6' }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ═══ BORDER RADIUS ═══ --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2 px-3 border-bottom">
                        <span class="fw-semibold small"><i class="fas fa-border-all me-1 text-success"></i>Border Radius</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Generic</label>
                                <input type="text" name="design_border_radius" class="form-control form-control-sm" style="font-size:12px;" value="{{ $design['design_border_radius'] ?? '12px' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Buttons</label>
                                <input type="text" name="design_btn_radius" class="form-control form-control-sm" style="font-size:12px;" value="{{ $design['design_btn_radius'] ?? '8px' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Inputs</label>
                                <input type="text" name="design_input_radius" class="form-control form-control-sm" style="font-size:12px;" value="{{ $design['design_input_radius'] ?? '8px' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Cards</label>
                                <input type="text" name="design_card_radius" class="form-control form-control-sm" style="font-size:12px;" value="{{ $design['design_card_radius'] ?? '16px' }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ═══ LAYOUT ═══ --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2 px-3 border-bottom">
                        <span class="fw-semibold small"><i class="fas fa-arrows-alt-h me-1 text-secondary"></i>Layout</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Container Max Width</label>
                                <input type="text" name="design_container_width" class="form-control form-control-sm" style="font-size:12px;" value="{{ $design['design_container_width'] ?? '1320px' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Card Shadow</label>
                                <input type="text" name="design_card_shadow" class="form-control form-control-sm" style="font-size:12px;" value="{{ $design['design_card_shadow'] ?? '0 1px 3px rgba(0,0,0,0.08)' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Transition</label>
                                <input type="text" name="design_transition" class="form-control form-control-sm" style="font-size:12px;" value="{{ $design['design_transition'] ?? '0.2s ease' }}">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN --}}
            <div class="col-lg-5">

                {{-- ═══ CUSTOM CSS ═══ --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                        <span class="fw-semibold small"><i class="fas fa-code me-1 text-success"></i>Custom CSS Overrides</span>
                    </div>
                    <div class="card-body p-3">
                        <textarea name="custom_css" class="form-control font-monospace" rows="10" style="font-size:11px;line-height:1.5;tab-size:2;" spellcheck="false">{{ $customCss }}</textarea>
                        <small class="text-muted d-block mt-1">Write raw CSS. These rules apply globally after theme variables.</small>
                    </div>
                </div>

                {{-- ═══ LIVE PREVIEW ═══ --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-2 px-3 border-bottom">
                        <span class="fw-semibold small"><i class="fas fa-eye me-1 text-warning"></i>Live Preview</span>
                    </div>
                    <div class="card-body p-3 text-center">
                        <div id="cssPreview" class="p-3 rounded mb-2" style="border:2px dashed var(--primary, #3b82f6);">
                            <span class="fw-bold" style="color:var(--primary, #1a0262);">Primary</span>
                            <span class="fw-bold ms-2" style="color:var(--secondary, #820b5c);">Secondary</span>
                            <span class="fw-bold ms-2" style="color:var(--success, #10b981);">Success</span>
                            <span class="fw-bold ms-2" style="color:var(--warning, #f59e0b);">Warning</span>
                            <span class="fw-bold ms-2" style="color:var(--danger, #ef4444);">Danger</span>
                            <div class="mt-2 d-flex gap-2 justify-content-center flex-wrap">
                                <span class="px-3 py-1 text-white" style="background:linear-gradient(135deg, var(--btn-grad-from, #1a0262), var(--btn-grad-to, #2c0e8a));border-radius:var(--ds-btn-radius, 8px);font-size:12px;">Button</span>
                                <span class="px-3 py-1 text-white" style="background:var(--secondary, #820b5c);border-radius:var(--ds-btn-radius, 8px);font-size:12px;">Secondary</span>
                                <span class="px-3 py-1" style="border:1px solid var(--primary, #1a0262);color:var(--primary, #1a0262);border-radius:var(--ds-btn-radius, 8px);font-size:12px;">Outline</span>
                            </div>
                            <div class="mt-2 d-flex gap-2 justify-content-center">
                                <span class="badge-primary px-2 py-1" style="font-size:10px;">Primary</span>
                                <span class="badge-secondary px-2 py-1" style="font-size:10px;">Secondary</span>
                                <span class="badge-success px-2 py-1" style="font-size:10px;">Success</span>
                                <span class="badge-warning px-2 py-1" style="font-size:10px;">Warning</span>
                            </div>
                            <div class="mt-2" style="font-family:var(--ds-body-font, 'Inter');font-size:var(--ds-body-size, 0.875rem);">
                                The quick brown fox jumps over the lazy dog.
                            </div>
                            <div class="mt-2" style="font-family:var(--ds-header-font, 'Inter');font-weight:var(--ds-header-weight, 700);font-size:1rem;">
                                Heading Preview
                            </div>
                            <div class="mt-2 d-flex gap-2 justify-content-center" style="font-size:11px;">
                                <a href="#" style="color:var(--primary, #1a0262);">Link</a>
                                <span style="background:var(--th-bg, #f0edfa);color:var(--th-color, #1e293b);padding:4px 8px;border-radius:var(--ds-card-radius, 16px);">Table Header</span>
                            </div>
                        </div>
                        <div class="d-flex gap-2 justify-content-center">
                            <button type="submit" class="btn btn-primary btn-sm px-4"><i class="fas fa-save me-1"></i>Save Design</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
    document.querySelectorAll('[name]').forEach(el => {
        el.addEventListener('input', function() {
            const name = this.name;
            const val = this.value;
            const preview = document.getElementById('cssPreview');
            if (!preview) return;
            const cssVarMap = {
                'theme_primary': '--primary', 'theme_secondary': '--secondary',
                'theme_success': '--success', 'theme_warning': '--warning',
                'theme_info': '--info', 'theme_danger': '--danger',
                'theme_bg': '--bg', 'theme_text': '--text',
                'theme_heading_color': '--heading-color', 'theme_card_bg': '--card-bg',
                'theme_table_header_bg': '--th-bg', 'theme_table_header_color': '--th-color',
                'theme_btn_gradient_from': '--btn-grad-from', 'theme_btn_gradient_to': '--btn-grad-to',
                'design_border_radius': '--ds-radius', 'design_btn_radius': '--ds-btn-radius',
                'design_input_radius': '--ds-input-radius', 'design_card_radius': '--ds-card-radius',
                'design_header_font': '--ds-header-font', 'design_body_font': '--ds-body-font',
                'design_header_weight': '--ds-header-weight',
                'design_body_size': '--ds-body-size', 'design_line_height': '--ds-line-height',
                'design_card_shadow': '--ds-card-shadow', 'design_transition': '--ds-transition',
                'design_container_width': '--ds-container-width',
            };
            if (cssVarMap[name]) {
                preview.style.setProperty(cssVarMap[name], val);
            }
        });
    });
    </script>
    @endpush
@endsection
