<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    protected $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'general');

        $modules = [
            'general' => ['name' => 'General', 'description' => 'System name, contact info, branding', 'icon' => 'cogs'],
            'features' => ['name' => 'Features', 'description' => 'Module toggles and availability', 'icon' => 'toggle-on'],
            'email' => ['name' => 'Email', 'description' => 'SMTP, mail driver, sender config', 'icon' => 'envelope'],
            'api' => ['name' => 'API', 'description' => 'API keys, integrations, webhooks', 'icon' => 'key'],
            'roles' => ['name' => 'Roles & Access', 'description' => 'User authority, CRM access, permissions', 'icon' => 'users-cog'],
        ];

        $settings = [];
        foreach (array_keys($modules) as $module) {
            if ($module === 'roles') continue;
            if ($module === 'email') {
                $emailSettings = Setting::where('group', 'email')->get();
                if ($emailSettings->isEmpty()) {
                    $this->seedEmailSettings();
                    $emailSettings = Setting::where('group', 'email')->get();
                }
                $settings['email'] = $emailSettings;
            } else {
                $settings[$module] = Setting::where('group', $module)->get();
            }
        }

        $allSettings = Setting::all();
        $allGroups = Setting::select('group')->distinct()->pluck('group');

        $users = User::with('children')->orderBy('name')->get();
        $roles = ['admin', 'agent', 'staff'];

        $uploadedImages = $this->getUploadedImages();

        return view('admin.cms.setting', compact(
            'modules', 'settings', 'allSettings', 'allGroups',
            'activeTab', 'users', 'roles', 'uploadedImages'
        ));
    }

    public function update(Request $request)
    {
        $group = $request->get('group', 'general');
        $data = $request->except(['_token', '_method', 'group']);

        foreach ($data as $key => $value) {
            $this->settingService->set($key, $value, $group);
        }

        return redirect()->back()->with('success', 'Settings updated.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:settings,key',
            'value' => 'nullable',
            'group' => 'required|string',
            'type' => 'required|in:string,text,boolean,number,json,image,color',
            'description' => 'nullable|string|max:500',
        ]);

        $this->settingService->set(
            $validated['key'],
            $validated['value'] ?? '',
            $validated['group'],
            $validated['type'],
            $validated['description'] ?? null
        );

        return redirect()->back()->with('success', 'Setting created.');
    }

    public function updateSingle(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'value' => 'nullable',
            'group' => 'nullable|string',
            'type' => 'nullable|in:string,text,boolean,number,json,image,color',
            'description' => 'nullable|string|max:500',
        ]);

        if ($setting->type === 'image' && $request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = 'setting-' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('settings', $filename);
            $validated['value'] = 'settings/' . $filename;
        }

        $setting->update($validated);
        return redirect()->back()->with('success', 'Setting updated.');
    }

    public function destroy(Setting $setting)
    {
        if ($setting->type === 'image' && $setting->value && str_starts_with($setting->value, 'settings/')) {
            Storage::delete($setting->value);
        }
        $setting->delete();
        return redirect()->back()->with('success', 'Setting deleted.');
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg|max:5120',
        ]);

        $file = $request->file('image');
        $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            . '-' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('settings', $filename);

        return response()->json([
            'success' => true,
            'path' => 'settings/' . $filename,
            'url' => Storage::url('settings/' . $filename),
            'filename' => $filename,
        ]);
    }

    public function updateUserRole(Request $request, User $user)
    {
        $validated = $request->validate(['role' => 'required|in:admin,agent,staff']);
        $user->update(['role' => $validated['role']]);
        return redirect()->back()->with('success', 'User role updated.');
    }

    public function toggleUserStatus(Request $request, User $user)
    {
        $user->update(['active' => !$user->active]);
        return redirect()->back()->with('success', 'User status toggled.');
    }

    public function toggleCrmAccess(Request $request, User $user)
    {
        $user->update(['paid_crm' => !$user->paid_crm]);
        return redirect()->back()->with('success', 'CRM access toggled.');
    }

    protected function seedEmailSettings(): void
    {
        $defaults = [
            ['key' => 'mail_driver', 'value' => 'smtp', 'type' => 'string', 'description' => 'Mail driver (smtp, sendmail, log)'],
            ['key' => 'mail_host', 'value' => 'smtp.gmail.com', 'type' => 'string', 'description' => 'SMTP host address'],
            ['key' => 'mail_port', 'value' => '587', 'type' => 'number', 'description' => 'SMTP port (587 for TLS, 465 for SSL)'],
            ['key' => 'mail_username', 'value' => '', 'type' => 'string', 'description' => 'SMTP username / email address'],
            ['key' => 'mail_password', 'value' => '', 'type' => 'string', 'description' => 'SMTP password / app password'],
            ['key' => 'mail_encryption', 'value' => 'tls', 'type' => 'string', 'description' => 'Encryption (tls, ssl, null)'],
            ['key' => 'mail_from_address', 'value' => 'noreply@example.com', 'type' => 'string', 'description' => 'Default from email address'],
            ['key' => 'mail_from_name', 'value' => 'Portal', 'type' => 'string', 'description' => 'Default from name'],
            ['key' => 'mail_reply_to_address', 'value' => 'support@example.com', 'type' => 'string', 'description' => 'Reply-to email address'],
            ['key' => 'mail_reply_to_name', 'value' => 'Support', 'type' => 'string', 'description' => 'Reply-to name'],
        ];
        foreach ($defaults as $s) {
            $this->settingService->set($s['key'], $s['value'], 'email', $s['type'], $s['description'] ?? null);
        }
    }

    protected function getUploadedImages(): array
    {
        if (!Storage::exists('settings')) return [];

        $files = Storage::files('settings');
        $images = [];
        foreach ($files as $file) {
            $filename = basename($file);
            $images[] = [
                'filename' => $filename,
                'path' => $file,
                'url' => Storage::url($file),
                'size' => Storage::size($file),
                'last_modified' => Storage::lastModified($file),
            ];
        }
        usort($images, fn($a, $b) => $b['last_modified'] - $a['last_modified']);
        return $images;
    }
}
