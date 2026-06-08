<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::latest()->paginate(20);
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create', ['page' => new Page()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'template' => 'nullable|string|max:50',
            'is_published' => 'boolean',
            'is_menu_item' => 'boolean',
            'menu_order' => 'nullable|integer|min:0',
            'status' => 'nullable|in:draft,published,archived',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['created_by'] = Auth::id();

        Page::create($validated);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page created successfully.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug,' . $page->id,
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'template' => 'nullable|string|max:50',
            'is_published' => 'boolean',
            'is_menu_item' => 'boolean',
            'menu_order' => 'nullable|integer|min:0',
            'status' => 'nullable|in:draft,published,archived',
        ]);

        $validated['updated_by'] = Auth::id();
        $page->update($validated);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page updated successfully.');
    }

    public function show(Page $page)
    {
        return view('admin.pages.show', compact('page'));
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page deleted successfully.');
    }

    public function dynamic()
    {
        $welcomeText = Setting::getValue('guest_dashboard_welcome', 'Welcome to Idea Consultancy Agent Portal');
        $programs = Setting::getValue('upcoming_programs', []);
        $activities = Setting::getValue('activities_events', []);

        return view('admin.pages.dynamic', compact('welcomeText', 'programs', 'activities'));
    }

    public function updateDynamic(Request $request)
    {
        if ($request->has('welcome_text')) {
            Setting::setValue('guest_dashboard_welcome', $request->welcome_text, 'content');
        }

        if ($request->has('programs')) {
            Setting::setValue('upcoming_programs', $request->programs, 'content');
        }

        if ($request->has('activities')) {
            Setting::setValue('activities_events', $request->activities, 'content');
        }

        return redirect()->route('admin.pages.dynamic')
            ->with('success', 'Dynamic content updated successfully.');
    }

    public function consolidated()
    {
        $pages = Page::latest()->paginate(20, ['*'], 'pages_page');
        $contentSettings = Setting::where('group', 'content')->orderBy('key')->get();
        $groups = Setting::select('group')->distinct()->pluck('group')->sort()->values();
        $welcomeText = Setting::getValue('guest_dashboard_welcome', '');
        $programs = Setting::getValue('upcoming_programs', []);
        $activities = Setting::getValue('activities_events', []);
        $images = $this->getUploadedImages();

        return view('admin.pages-content.index', compact(
            'pages', 'contentSettings', 'groups', 'welcomeText', 'programs', 'activities', 'images'
        ));
    }

    private function getUploadedImages(): array
    {
        $path = storage_path('app/public/settings');
        if (!is_dir($path)) return [];
        $files = array_filter(scandir($path), fn($f) => in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['jpg','jpeg','png','gif','webp']));
        $images = [];
        foreach ($files as $file) {
            $filePath = $path . DIRECTORY_SEPARATOR . $file;
            $images[] = [
                'filename' => $file,
                'url' => Storage::url('settings/' . $file),
                'path' => 'settings/' . $file,
                'size' => filesize($filePath),
                'last_modified' => filemtime($filePath),
            ];
        }
        usort($images, fn($a, $b) => $b['last_modified'] - $a['last_modified']);
        return $images;
    }
}
