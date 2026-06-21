<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::latest()->paginate(15);
        return view('admin.cms.pages.index', compact('pages'));
    }

    public function create()
    {
        $templates = ['default' => 'Default', 'full-width' => 'Full Width', 'landing' => 'Landing Page'];
        return view('admin.cms.pages.create', compact('templates'));
    }
    public function consolidated()
    {
        $pages = Page::latest()->paginate(15);

        return view(
            'admin.content',
            compact('pages')
        );
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
            'featured_image' => 'nullable|image|max:2048',
            'is_published' => 'boolean',
            'is_menu_item' => 'boolean',
            'menu_order' => 'nullable|integer|min:0',
            'status' => 'nullable|in:draft,published,archived',
        ]);

        // Handle image upload
        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('pages', 'public');
            $validated['featured_image'] = $path;
        }

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['created_by'] = Auth::id();
        $validated['status'] = $validated['status'] ?? 'draft';

        Page::create($validated);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page created successfully!');
    }

    public function edit(Page $page)
    {
        $templates = ['default' => 'Default', 'full-width' => 'Full Width', 'landing' => 'Landing Page'];
        return view('admin.cms.pages.edit', compact('page', 'templates'));
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
            'featured_image' => 'nullable|image|max:2048',
            'is_published' => 'boolean',
            'is_menu_item' => 'boolean',
            'menu_order' => 'nullable|integer|min:0',
            'status' => 'nullable|in:draft,published,archived',
        ]);

        if ($request->hasFile('featured_image')) {
            // Delete old image
            if ($page->featured_image) {
                \Storage::disk('public')->delete($page->featured_image);
            }
            $path = $request->file('featured_image')->store('pages', 'public');
            $validated['featured_image'] = $path;
        }

        $validated['updated_by'] = Auth::id();
        $page->update($validated);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page updated successfully!');
    }

    public function destroy(Page $page)
    {
        // Delete featured image
        if ($page->featured_image) {
            \Storage::disk('public')->delete($page->featured_image);
        }

        $page->delete();

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page deleted successfully!');
    }

    public function toggleStatus(Page $page)
    {
        $page->is_published = !$page->is_published;
        $page->status = $page->is_published ? 'published' : 'draft';
        $page->save();

        return redirect()->back()->with('success', 'Page status updated!');
    }
}
