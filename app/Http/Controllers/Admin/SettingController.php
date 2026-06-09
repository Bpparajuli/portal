<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $query = Setting::orderBy('group')->orderBy('key');

        if ($request->filled('group')) {
            $query->where('group', $request->group);
        }

        $allSettings = $query->get();
        $settings = $allSettings->groupBy('group');
        $groups = Setting::select('group')->distinct()->pluck('group');

        $uploadedImages = $this->getUploadedImages();

        return view('admin.settings', compact('settings', 'allSettings', 'groups', 'uploadedImages'));
    }

    public function update(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'key' => 'nullable|string|max:255|unique:settings,key,' . $setting->id,
            'value' => 'nullable|string',
            'group' => 'nullable|string|max:50',
            'type' => 'nullable|in:' . implode(',', Setting::validTypes()),
        ]);

        if ($setting->type === 'image' && $request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/settings', $filename);
            $validated['value'] = 'settings/' . $filename;
        }

        $setting->update($validated);

        return redirect()->route('admin.settings.index', ['group' => $request->query('group')])
            ->with('success', 'Setting updated successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:settings,key',
            'value' => 'nullable|string',
            'group' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'type' => 'nullable|in:' . implode(',', Setting::validTypes()),
        ]);

        Setting::create($validated);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Setting created successfully.');
    }

    public function destroy(Setting $setting)
    {
        if ($setting->type === 'image' && !empty($setting->value) && str_starts_with($setting->value, 'settings/')) {
            Storage::delete('public/' . $setting->value);
        }

        $setting->delete();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Setting deleted successfully.');
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $file = $request->file('image');
        $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('public/settings', $filename);

        return response()->json([
            'success' => true,
            'filename' => $filename,
            'url' => Storage::url('settings/' . $filename),
            'path' => 'settings/' . $filename,
        ]);
    }

    public function listImages()
    {
        $images = $this->getUploadedImages();

        return response()->json(['images' => $images]);
    }

    protected function getUploadedImages(): array
    {
        $files = Storage::files('public/settings');
        $images = [];

        foreach ($files as $file) {
            $filename = basename($file);
            $images[] = [
                'filename' => $filename,
                'url' => Storage::url('settings/' . $filename),
                'path' => 'settings/' . $filename,
                'size' => Storage::size($file),
                'last_modified' => Storage::lastModified($file),
            ];
        }

        rsort($images);

        return $images;
    }
}
