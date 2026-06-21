<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class CssController extends Controller
{
    public function index()
    {
        $prefix = 'theme_';
        $themeKeys = ['primary', 'secondary', 'success', 'warning', 'info', 'danger', 'bg', 'text', 'heading_color', 'card_bg', 'table_header_bg', 'table_header_color', 'btn_gradient_from', 'btn_gradient_to'];

        $theme = [];
        $themeDefaults = [
            'theme_primary' => '#1a0262', 'theme_secondary' => '#820b5c', 'theme_success' => '#10b981',
            'theme_warning' => '#f59e0b', 'theme_info' => '#3b82f6', 'theme_danger' => '#ef4444',
            'theme_bg' => '#f8fafc', 'theme_text' => '#1e293b', 'theme_heading_color' => '#0f172a',
            'theme_card_bg' => '#ffffff', 'theme_table_header_bg' => '#f0edfa', 'theme_table_header_color' => '#1e293b',
            'theme_btn_gradient_from' => '#1a0262', 'theme_btn_gradient_to' => '#2c0e8a',
        ];
        foreach ($themeKeys as $k) {
            $fullKey = $prefix . $k;
            $theme[$fullKey] = Setting::getValue($fullKey, $themeDefaults[$fullKey]);
        }

        $dPrefix = 'design_';
        $designKeys = ['border_radius', 'btn_radius', 'input_radius', 'card_radius', 'header_font', 'body_font', 'header_weight', 'body_size', 'line_height', 'card_shadow', 'transition', 'container_width'];

        $design = [];
        $designDefaults = [
            'design_border_radius' => '12px', 'design_btn_radius' => '8px', 'design_input_radius' => '8px',
            'design_card_radius' => '16px', 'design_header_font' => "'Inter', sans-serif",
            'design_body_font' => "'Inter', sans-serif", 'design_header_weight' => '700',
            'design_body_size' => '0.875rem', 'design_line_height' => '1.6',
            'design_card_shadow' => '0 1px 3px rgba(0,0,0,0.08)', 'design_transition' => '0.2s ease',
            'design_container_width' => '1320px',
        ];
        foreach ($designKeys as $k) {
            $fullKey = $dPrefix . $k;
            $design[$fullKey] = Setting::getValue($fullKey, $designDefaults[$fullKey]);
        }

        $customCss = Setting::getValue('custom_css', '');

        return view('admin.cms.css', compact('theme', 'design', 'customCss'));
    }

    public function update(Request $request)
    {
        $themeFields = ['theme_primary', 'theme_secondary', 'theme_success', 'theme_warning', 'theme_info', 'theme_danger', 'theme_bg', 'theme_text', 'theme_heading_color', 'theme_card_bg', 'theme_table_header_bg', 'theme_table_header_color', 'theme_btn_gradient_from', 'theme_btn_gradient_to'];

        $designFields = ['design_border_radius', 'design_btn_radius', 'design_input_radius', 'design_card_radius', 'design_header_font', 'design_body_font', 'design_header_weight', 'design_body_size', 'design_line_height', 'design_card_shadow', 'design_transition', 'design_container_width'];

        $fields = array_merge($themeFields, $designFields, ['custom_css']);

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $group = str_starts_with($field, 'theme_') ? 'theme' : (str_starts_with($field, 'design_') ? 'design' : 'appearance');
                Setting::setValue($field, $request->$field, $group);
            }
        }

        return redirect()->back()->with('success', 'Design settings saved.');
    }

    public function preview()
    {
        $themeKeys = ['primary', 'secondary', 'success', 'warning', 'info', 'danger', 'bg', 'text', 'heading_color', 'card_bg', 'table_header_bg', 'table_header_color', 'btn_gradient_from', 'btn_gradient_to'];
        $themeDefaults = [
            'primary' => '#1a0262', 'secondary' => '#820b5c', 'success' => '#10b981',
            'warning' => '#f59e0b', 'info' => '#3b82f6', 'danger' => '#ef4444',
            'bg' => '#f8fafc', 'text' => '#1e293b', 'heading_color' => '#0f172a',
            'card_bg' => '#ffffff', 'table_header_bg' => '#f0edfa', 'table_header_color' => '#1e293b',
            'btn_gradient_from' => '#1a0262', 'btn_gradient_to' => '#2c0e8a',
        ];

        $t = [];
        foreach ($themeKeys as $k) {
            $t[$k] = Setting::getValue('theme_' . $k, $themeDefaults[$k]);
        }

        $designDefaults = [
            'border_radius' => '12px', 'btn_radius' => '8px', 'input_radius' => '8px',
            'card_radius' => '16px', 'header_font' => "'Inter', sans-serif",
            'body_font' => "'Inter', sans-serif", 'header_weight' => '700',
            'body_size' => '0.875rem', 'line_height' => '1.6',
            'card_shadow' => '0 1px 3px rgba(0,0,0,0.08)', 'transition' => '0.2s ease',
            'container_width' => '1320px',
        ];

        $d = [];
        foreach ($designDefaults as $k => $v) {
            $d[$k] = Setting::getValue('design_' . $k, $v);
        }

        $custom = Setting::getValue('custom_css', '');

        $css = <<<CSS
:root {
    --primary: {$t['primary']};
    --secondary: {$t['secondary']};
    --success: {$t['success']};
    --warning: {$t['warning']};
    --info: {$t['info']};
    --danger: {$t['danger']};
    --bg: {$t['bg']};
    --text: {$t['text']};
    --heading-color: {$t['heading_color']};
    --card-bg: {$t['card_bg']};
    --th-bg: {$t['table_header_bg']};
    --th-color: {$t['table_header_color']};
    --btn-grad-from: {$t['btn_gradient_from']};
    --btn-grad-to: {$t['btn_gradient_to']};

    --ds-radius: {$d['border_radius']};
    --ds-btn-radius: {$d['btn_radius']};
    --ds-input-radius: {$d['input_radius']};
    --ds-card-radius: {$d['card_radius']};
    --ds-header-font: {$d['header_font']};
    --ds-body-font: {$d['body_font']};
    --ds-header-weight: {$d['header_weight']};
    --ds-body-size: {$d['body_size']};
    --ds-line-height: {$d['line_height']};
    --ds-card-shadow: {$d['card_shadow']};
    --ds-transition: {$d['transition']};
    --ds-container-width: {$d['container_width']};
}

body { background: var(--bg); color: var(--text); font-family: var(--ds-body-font); font-size: var(--ds-body-size); line-height: var(--ds-line-height); }
h1, h2, h3, h4, h5, h6 { color: var(--heading-color); font-family: var(--ds-header-font); font-weight: var(--ds-header-weight); }

.btn { border-radius: var(--ds-btn-radius) !important; }
.btn-primary { background: linear-gradient(135deg, var(--btn-grad-from), var(--btn-grad-to)); border: none; color: #fff; }
.btn-primary:hover { background: linear-gradient(135deg, var(--btn-grad-to), var(--btn-grad-from)); }
.btn-secondary { background: var(--secondary); border: none; color: #fff; }
.btn-success { background: var(--success); border: none; color: #fff; }
.btn-warning { background: var(--warning); border: none; color: #fff; }
.btn-info { background: var(--info); border: none; color: #fff; }
.btn-danger { background: var(--danger); border: none; color: #fff; }
.btn-outline-primary { color: var(--primary); border-color: var(--primary); }
.btn-outline-primary:hover { background: linear-gradient(135deg, var(--btn-grad-from), var(--btn-grad-to)); border-color: transparent; color: #fff; }
.btn-link { color: var(--primary); }
.btn-link:hover { color: var(--secondary); }

.card { background: var(--card-bg); border-radius: var(--ds-card-radius) !important; box-shadow: var(--ds-card-shadow); transition: box-shadow var(--ds-transition); }
.card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

a, .link-primary { color: var(--primary); transition: color var(--ds-transition); }
a:hover { color: var(--secondary); }

.form-control, .form-select { border-radius: var(--ds-input-radius) !important; }
.form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 0.2rem rgba(from var(--primary) r g b / 0.25); }

.table { border-radius: var(--ds-card-radius); overflow: hidden; }
.table thead th { background: var(--th-bg) !important; color: var(--th-color) !important; border-bottom: 2px solid var(--primary); padding: 0.75rem 0.5rem; }
.table td { padding: 0.75rem 0.5rem; }
.table-hover tbody tr:hover { background: color-mix(in srgb, var(--primary) 4%, var(--bg)); }
.table-striped tbody tr:nth-of-type(odd) { background: color-mix(in srgb, var(--bg) 98%, var(--primary)); }

.badge-primary { background: var(--primary); color: #fff; }
.badge-secondary { background: var(--secondary); color: #fff; }
.badge-success { background: var(--success); color: #fff; }
.badge-warning { background: var(--warning); color: #fff; }
.badge-info { background: var(--info); color: #fff; }
.badge-danger { background: var(--danger); color: #fff; }

.nav-pills .nav-link.active, .nav-tabs .nav-link.active { background: var(--primary); border-color: var(--primary); color: #fff; }
.page-item.active .page-link { background: var(--primary); border-color: var(--primary); }
.progress-bar { background: linear-gradient(90deg, var(--btn-grad-from), var(--btn-grad-to)); }
.list-group-item.active { background: var(--primary); border-color: var(--primary); }
.dropdown-item:active { background: var(--primary); }
.form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }

.alert-primary { background: color-mix(in srgb, var(--primary) 10%, white); border-color: var(--primary); color: var(--primary); }
.alert-success { background: color-mix(in srgb, var(--success) 10%, white); border-color: var(--success); color: var(--success); }
.alert-warning { background: color-mix(in srgb, var(--warning) 10%, white); border-color: var(--warning); color: var(--warning); }
.alert-info { background: color-mix(in srgb, var(--info) 10%, white); border-color: var(--info); color: var(--info); }
.alert-danger { background: color-mix(in srgb, var(--danger) 10%, white); border-color: var(--danger); color: var(--danger); }

.container { max-width: var(--ds-container-width); }
.text-primary { color: var(--primary) !important; }
.text-secondary { color: var(--secondary) !important; }
.text-success { color: var(--success) !important; }
.text-warning { color: var(--warning) !important; }
.text-info { color: var(--info) !important; }
.text-danger { color: var(--danger) !important; }

.bg-primary { background: var(--primary) !important; color: #fff !important; }
.bg-primary .text-primary { color: #fff !important; }
.bg-primary.text-primary { color: #fff !important; }
.bg-secondary { background: var(--secondary) !important; color: #fff !important; }
.bg-secondary .text-secondary { color: #fff !important; }
.bg-secondary.text-secondary { color: #fff !important; }
.bg-success { background: var(--success) !important; color: #fff !important; }
.bg-success .text-success { color: #fff !important; }
.bg-success.text-success { color: #fff !important; }
.bg-warning { background: var(--warning) !important; color: #fff !important; }
.bg-warning .text-warning { color: #fff !important; }
.bg-warning.text-warning { color: #fff !important; }
.bg-info { background: var(--info) !important; color: #fff !important; }
.bg-info .text-info { color: #fff !important; }
.bg-info.text-info { color: #fff !important; }
.bg-danger { background: var(--danger) !important; color: #fff !important; }
.bg-danger .text-danger { color: #fff !important; }
.bg-danger.text-danger { color: #fff !important; }

.border-primary { border-color: var(--primary) !important; }
.border-secondary { border-color: var(--secondary) !important; }
.rounded-custom { border-radius: var(--ds-radius) !important; }
{$custom}
CSS;

        return response($css, 200, ['Content-Type' => 'text/css']);
    }
}
