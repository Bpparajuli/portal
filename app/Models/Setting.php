<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = [
        'key', 'value', 'group', 'type', 'description', 'is_encrypted',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    protected $appends = ['image_url'];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('settings_all');
        });

        static::deleted(function () {
            Cache::forget('settings_all');
        });
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        try {
            $setting = static::where('key', $key)->first();
        } catch (\Exception $e) {
            return $default;
        }

        if (!$setting) {
            return $default;
        }

        $value = $setting->is_encrypted ? decrypt($setting->value) : $setting->value;

        return match ($setting->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'number' => (float) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    public static function setValue(string $key, mixed $value, ?string $group = 'general'): static
    {
        $type = match (true) {
            is_bool($value) => 'boolean',
            is_numeric($value) => 'number',
            is_array($value) || is_object($value) => 'json',
            default => 'string',
        };

        $value = match ($type) {
            'boolean' => $value ? 'true' : 'false',
            'json' => json_encode($value),
            default => (string) $value,
        };

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group]
        );
    }

    public static function getGroup(string $group): array
    {
        return static::where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->type !== 'image' || empty($this->value)) {
            return null;
        }

        return static::resolveImageUrl($this->value);
    }

    public function isImageType(): bool
    {
        return $this->type === 'image';
    }

    public static function validTypes(): array
    {
        return ['string', 'text', 'boolean', 'number', 'json', 'image', 'color'];
    }

    public static function resolveImageUrl(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (str_starts_with($value, 'settings/')) {
            return Storage::url($value);
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return asset($value);
    }
}
