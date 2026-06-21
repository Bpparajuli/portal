<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    protected $cacheKey = 'settings_all';
    protected $cacheTtl = 3600; // 1 hour

    /**
     * Get all settings grouped by their group
     */
    public function getAllGrouped(): array
    {
        return Cache::remember($this->cacheKey, $this->cacheTtl, function () {
            $settings = Setting::all();
            $grouped = [];

            foreach ($settings as $setting) {
                $group = $setting->group;
                if (!isset($grouped[$group])) {
                    $grouped[$group] = [];
                }
                $grouped[$group][$setting->key] = $setting->value;
            }

            return $grouped;
        });
    }

    /**
     * Get settings by group
     */
    public function getGroup(string $group): array
    {
        $all = $this->getAllGrouped();
        return $all[$group] ?? [];
    }

    /**
     * Get a specific setting value
     */
    public function get(string $key, $default = null)
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        $value = $setting->is_encrypted ? decrypt($setting->value) : $setting->value;

        return match ($setting->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'number' => (float) $value,
            'json', 'array' => json_decode($value, true),
            'image' => $this->getImageUrl($value),
            default => $value,
        };
    }

    /**
     * Set a setting value
     */
    public function set(string $key, $value, string $group = 'general', string $type = null, string $description = null): Setting
    {
        // Auto-detect type if not provided
        if (!$type) {
            $type = match (true) {
                is_bool($value) => 'boolean',
                is_numeric($value) => 'number',
                is_array($value) || is_object($value) => 'json',
                default => 'string',
            };
        }

        // Convert value based on type
        $storedValue = match ($type) {
            'boolean' => $value ? 'true' : 'false',
            'json', 'array' => json_encode($value),
            default => (string) $value,
        };

        $setting = Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storedValue,
                'group' => $group,
                'type' => $type,
                'description' => $description
            ]
        );

        Cache::forget($this->cacheKey);

        return $setting;
    }

    /**
     * Get image URL from path
     */
    protected function getImageUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return Storage::url($path);
    }

    /**
     * Generate dynamic CSS for appearance
     */
    public function generateDynamicCSS(): string
    {
        $appearance = $this->getGroup('appearance');

        $primaryColor = $appearance['primary_color'] ?? '#3b82f6';
        $secondaryColor = $appearance['secondary_color'] ?? '#6b7280';
        $fontFamily = $appearance['font_family'] ?? "'Inter', sans-serif";
        $borderRadius = $appearance['border_radius'] ?? '0.5rem';
        $customCss = $appearance['custom_css'] ?? '';

        return "
            :root {
                --primary-color: {$primaryColor};
                --primary-color-rgb: {$this->hexToRgb($primaryColor)};
                --secondary-color: {$secondaryColor};
                --font-family: {$fontFamily};
                --border-radius: {$borderRadius};
            }
            
            body {
                font-family: var(--font-family);
            }
            
            .btn-primary {
                background-color: var(--primary-color);
                border-color: var(--primary-color);
            }
            
            .btn-primary:hover {
                background-color: {$this->darkenColor($primaryColor, 10)};
                border-color: {$this->darkenColor($primaryColor, 10)};
            }
            
            .text-primary {
                color: var(--primary-color) !important;
            }
            
            .border-primary {
                border-color: var(--primary-color) !important;
            }
            
            {$customCss}
        ";
    }

    /**
     * Helper: Convert hex to RGB
     */
    protected function hexToRgb(string $hex): string
    {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = hexdec($hex[0] . $hex[1]);
        $g = hexdec($hex[2] . $hex[3]);
        $b = hexdec($hex[4] . $hex[5]);
        return "{$r}, {$g}, {$b}";
    }

    /**
     * Helper: Darken color
     */
    protected function darkenColor(string $hex, int $percent): string
    {
        $hex = str_replace('#', '', $hex);
        $r = hexdec($hex[0] . $hex[1]);
        $g = hexdec($hex[2] . $hex[3]);
        $b = hexdec($hex[4] . $hex[5]);

        $r = max(0, $r - ($r * $percent / 100));
        $g = max(0, $g - ($g * $percent / 100));
        $b = max(0, $b - ($b * $percent / 100));

        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }
}
