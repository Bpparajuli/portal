<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingService
{
    /**
     * Get all settings keyed by their key name.
     */
    public function getAll(): \Illuminate\Support\Collection
    {
        return Setting::all()->keyBy('key');
    }

    /**
     * Create or update a setting.
     */
    public function set(string $key, string $value): Setting
    {
        return Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Update a setting by ID.
     */
    public function update(int $id, string $value): Setting
    {
        $setting = Setting::findOrFail($id);
        $setting->update(['value' => $value]);
        return $setting;
    }

    /**
     * Delete a setting.
     */
    public function destroy(int $id): void
    {
        Setting::findOrFail($id)->delete();
    }
}
