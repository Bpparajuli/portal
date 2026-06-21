<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Page;
use App\Models\Popup;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'sections');

        $tabs = [
            'sections' => ['name' => 'Global Sections', 'icon' => 'globe'],
            'dynamic' => ['name' => 'Dynamic UI', 'icon' => 'magic'],
            'popups' => ['name' => 'Popups', 'icon' => 'window-restore'],
            'blocks' => ['name' => 'Content Blocks', 'icon' => 'newspaper'],
            'media' => ['name' => 'Media Manager', 'icon' => 'images'],
        ];

        // Global Sections data (header, footer, favicon)
        $siteName = Setting::getValue('site.name', 'My App');
        $siteLogo = Setting::getValue('site.logo', '');
        $siteNotice = Setting::getValue('site.notice', '');
        $favicon = Setting::getValue('site.favicon', '');
        // Dynamic UI data (all settings used by guest-dashboard + agent-dashboard)
        $heroTitle = Setting::getValue('content.hero_title', '');
        $heroSubtitle = Setting::getValue('content.hero_subtitle', '');
        $heroBadge = Setting::getValue('content.hero_badge', '');
        $heroBtn1Text = Setting::getValue('content.hero_btn1_text', '');
        $heroBtn1Link = Setting::getValue('content.hero_btn1_link', '');
        $heroBtn2Text = Setting::getValue('content.hero_btn2_text', '');
        $heroBtn2Link = Setting::getValue('content.hero_btn2_link', '');
        $heroStat1Label = Setting::getValue('content.hero_stat1_label', '');
        $heroStat2Label = Setting::getValue('content.hero_stat2_label', '');
        $heroStat3Label = Setting::getValue('content.hero_stat3_label', '');
        $heroStat1Value = Setting::getValue('content.hero_stat1_value', '');
        $heroStat2Value = Setting::getValue('content.hero_stat2_value', '');
        $heroStat3Value = Setting::getValue('content.hero_stat3_value', '');
        $heroFormTitle = Setting::getValue('content.hero_form_title', '');
        $heroFormDescription = Setting::getValue('content.hero_form_description', '');
        $sectionFilterTitle = Setting::getValue('content.section_filter_title', '');
        $sectionFilterDesc = Setting::getValue('content.section_filter_desc', '');
        $sectionFeaturesTag = Setting::getValue('content.section_features_tag', '');
        $sectionFeaturesTitle = Setting::getValue('content.section_features_title', '');
        $sectionBannersTag = Setting::getValue('content.section_banners_tag', '');
        $sectionBannersTitle = Setting::getValue('content.section_banners_title', '');
        $sectionEventsTag = Setting::getValue('content.section_events_tag', '');
        $sectionEventsTitle = Setting::getValue('content.section_events_title', '');
        $countriesTitle = Setting::getValue('content.countries_title', '');
        $sectionTestimonialsTag = Setting::getValue('content.section_testimonials_tag', '');
        $sectionTestimonialsTitle = Setting::getValue('content.section_testimonials_title', '');
        $sectionLogosTag = Setting::getValue('content.section_logos_tag', '');
        $sectionLogosTitle = Setting::getValue('content.section_logos_title', '');
        $features = Setting::getValue('content.features', []);
        $banners = Setting::getValue('content.banners', []);
        $events = Setting::getValue('content.events', []);
        $countries = Setting::getValue('content.countries', []);
        $ctaTitle = Setting::getValue('content.cta_title', '');
        $ctaDescription = Setting::getValue('content.cta_description', '');
        $ctaButtonText = Setting::getValue('content.cta_button_text', '');
        $ctaButtonLink = Setting::getValue('content.cta_button_link', '');
        $ctaImage = Setting::getValue('content.cta_image', '');


        // Content Blocks (blog/news/articles from contents table)
        $contentTypes = ['post', 'news', 'article', 'faq'];
        $blocks = Content::whereIn('type', $contentTypes)->latest()->paginate(15);

        // Pages (CMS pages)
        $pages = Page::orderBy('title')->get();

        // Popups
        $popups = Popup::orderBy('sort_order')->get();

        $uploadedImages = $this->getUploadedImages();
        $uniLogos = $this->getUniLogos();

        return view('admin.cms.content', compact(
            'tabs', 'activeTab',
            'siteName', 'siteLogo', 'siteNotice', 'favicon',
            'heroTitle', 'heroSubtitle', 'heroBadge', 'heroBtn1Text', 'heroBtn1Link',
            'heroBtn2Text', 'heroBtn2Link', 'heroStat1Label', 'heroStat2Label', 'heroStat3Label',
            'heroStat1Value', 'heroStat2Value', 'heroStat3Value', 'heroFormTitle', 'heroFormDescription',
            'sectionFilterTitle', 'sectionFilterDesc',
            'sectionFeaturesTag', 'sectionFeaturesTitle',
            'sectionBannersTag', 'sectionBannersTitle',
            'sectionEventsTag', 'sectionEventsTitle',
            'countriesTitle',
            'sectionTestimonialsTag', 'sectionTestimonialsTitle',
            'sectionLogosTag', 'sectionLogosTitle',
            'features', 'banners', 'events', 'countries',
            'ctaTitle', 'ctaDescription', 'ctaButtonText', 'ctaButtonLink', 'ctaImage',

            'blocks', 'contentTypes', 'pages',
            'popups',
            'uploadedImages', 'uniLogos'
        ));
    }

    // ─── GLOBAL SECTIONS ───

    public function updateSections(Request $request)
    {
        $fields = [
            'site.name', 'site.logo', 'site.notice', 'site.favicon',
        ];

        foreach ($fields as $key) {
            if ($request->has($key)) {
                $group = explode('.', $key)[0];
                Setting::setValue($key, $request->$key, $group);
            }
        }

        if ($request->hasFile('site.logo')) {
            $file = $request->file('site.logo');
            $filename = 'logo-' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('media', $filename);
            Setting::setValue('site.logo', 'media/' . $filename, 'site');
        }

        if ($request->hasFile('site.favicon')) {
            $file = $request->file('site.favicon');
            $filename = 'favicon-' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('media', $filename);
            Setting::setValue('site.favicon', 'media/' . $filename, 'site');
        }

        return redirect()->back()->with('success', 'Global sections updated.');
    }

    // ─── DYNAMIC UI ───

    public function updateDynamic(Request $request)
    {
        $section = $request->get('section');
        $action = $request->get('action', 'update');

        switch ($section) {
            case 'hero':
                Setting::setValue('content.hero_title', $request->hero_title, 'content');
                Setting::setValue('content.hero_subtitle', $request->hero_subtitle, 'content');
                Setting::setValue('content.hero_badge', $request->hero_badge, 'content');
                Setting::setValue('content.hero_btn1_text', $request->hero_btn1_text, 'content');
                Setting::setValue('content.hero_btn1_link', $request->hero_btn1_link, 'content');
                Setting::setValue('content.hero_btn2_text', $request->hero_btn2_text, 'content');
                Setting::setValue('content.hero_btn2_link', $request->hero_btn2_link, 'content');
                Setting::setValue('content.hero_stat1_label', $request->hero_stat1_label, 'content');
                Setting::setValue('content.hero_stat2_label', $request->hero_stat2_label, 'content');
                Setting::setValue('content.hero_stat3_label', $request->hero_stat3_label, 'content');
                Setting::setValue('content.hero_stat1_value', $request->hero_stat1_value, 'content');
                Setting::setValue('content.hero_stat2_value', $request->hero_stat2_value, 'content');
                Setting::setValue('content.hero_stat3_value', $request->hero_stat3_value, 'content');
                Setting::setValue('content.hero_form_title', $request->hero_form_title, 'content');
                Setting::setValue('content.hero_form_description', $request->hero_form_description, 'content');
                break;

            case 'section_headings':
                $mapped = [
                    'filter_title' => 'content.section_filter_title',
                    'filter_description' => 'content.section_filter_desc',
                    'features_tag' => 'content.section_features_tag',
                    'features_title' => 'content.section_features_title',
                    'events_tag' => 'content.section_events_tag',
                    'events_title' => 'content.section_events_title',
                    'countries_title' => 'content.countries_title',
                ];
                foreach ($mapped as $input => $setting) {
                    if ($request->has($input)) {
                        Setting::setValue($setting, $request->$input, 'content');
                    }
                }
                break;

            case 'cta':
                Setting::setValue('content.cta_title', $request->cta_title, 'content');
                Setting::setValue('content.cta_description', $request->cta_description, 'content');
                Setting::setValue('content.cta_button_text', $request->cta_button_text, 'content');
                Setting::setValue('content.cta_button_link', $request->cta_button_link, 'content');
                if ($request->hasFile('cta_image')) {
                    $file = $request->file('cta_image');
                    $filename = 'cta-' . time() . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('media', $filename);
                    Setting::setValue('content.cta_image', 'media/' . $filename, 'content');
                } elseif ($request->filled('cta_image_selected')) {
                    Setting::setValue('content.cta_image', $request->cta_image_selected, 'content');
                }
                break;

            case 'features':
                $features = Setting::getValue('content.features', []);
                $this->handleRepeatable($features, $request, 'features');
                Setting::setValue('content.features', $features, 'content');
                break;

            case 'banners':
                $banners = Setting::getValue('content.banners', []);
                $index = $request->get('index');
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    $filename = 'banner-' . time() . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('media', $filename);
                    $request->merge(['image' => 'media/' . $filename]);
                } elseif ($action === 'update' && $index !== null && isset($banners[$index])) {
                    $request->merge(['image' => $banners[$index]['image'] ?? ($banners[$index]['flag'] ?? '')]);
                } else {
                    $request->merge(['image' => '']);
                }
                $this->handleRepeatable($banners, $request, 'banners');
                Setting::setValue('content.banners', $banners, 'content');
                break;

            case 'events':
                $events = Setting::getValue('content.events', []);
                $this->handleEventUpdate($events, $request, $action);
                Setting::setValue('content.events', $events, 'content');
                break;

            case 'countries':
                $countries = Setting::getValue('content.countries', []);
                $index = $request->get('index');
                if ($action === 'sort') {
                    $direction = $request->get('direction');
                    $swap = $direction === 'up' ? $index - 1 : $index + 1;
                    if (isset($countries[$index], $countries[$swap])) {
                        $tmp = $countries[$index];
                        $countries[$index] = $countries[$swap];
                        $countries[$swap] = $tmp;
                        $countries = array_values($countries);
                    }
                    Setting::setValue('content.countries', $countries, 'content');
                    break;
                }
                if ($request->hasFile('flag')) {
                    $file = $request->file('flag');
                    $filename = 'flag-' . time() . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('media', $filename);
                    $request->merge(['flag' => 'media/' . $filename]);
                } elseif ($action === 'update' && $index !== null && isset($countries[$index])) {
                    $request->merge(['flag' => $countries[$index]['flag'] ?? ($countries[$index]['image'] ?? '')]);
                } else {
                    $request->merge(['flag' => '']);
                }
                $this->handleRepeatable($countries, $request, 'countries');
                Setting::setValue('content.countries', $countries, 'content');
                break;


        }

        return redirect()->back()->with('success', 'Content updated.');
    }

    // ─── POPUPS ───

    public function storePopup(Request $request)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'button_text' => 'nullable|string|max:255',
            'button_link' => 'nullable|string|max:500',
            'button_target' => 'nullable|in:_self,_blank',
            'show_close' => 'nullable|boolean',
            'display_on' => 'nullable|array',
            'display_on.*' => 'string',
            'display_duration' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['show_close'] = $request->boolean('show_close');
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'popup-' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('media', $filename);
            $data['image'] = 'media/' . $filename;
        } elseif ($request->filled('image')) {
            $data['image'] = $request->image;
        }

        Popup::create($data);

        return redirect()->route('admin.content.index', ['tab' => 'popups'])
            ->with('success', 'Popup created.');
    }

    public function updatePopup(Request $request, Popup $popup)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'button_text' => 'nullable|string|max:255',
            'button_link' => 'nullable|string|max:500',
            'button_target' => 'nullable|in:_self,_blank',
            'show_close' => 'nullable|boolean',
            'display_on' => 'nullable|array',
            'display_on.*' => 'string',
            'display_duration' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['show_close'] = $request->boolean('show_close');
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            if ($popup->image && Storage::disk('public')->exists($popup->image)) {
                Storage::disk('public')->delete($popup->image);
            }
            $file = $request->file('image');
            $filename = 'popup-' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('media', $filename);
            $data['image'] = 'media/' . $filename;
        } elseif ($request->filled('image')) {
            $data['image'] = $request->image;
        }

        $popup->update($data);

        return redirect()->route('admin.content.index', ['tab' => 'popups'])
            ->with('success', 'Popup updated.');
    }

    public function destroyPopup(Popup $popup)
    {
        if ($popup->image && Storage::disk('public')->exists($popup->image)) {
            Storage::disk('public')->delete($popup->image);
        }
        $popup->delete();

        return redirect()->route('admin.content.index', ['tab' => 'popups'])
            ->with('success', 'Popup deleted.');
    }

    // ─── CONTENT BLOCKS (CMS) ───

    public function storeBlock(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:post,news,article,faq,testimonial',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'is_published' => 'boolean',
            'status' => 'nullable|in:draft,published,archived',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['created_by'] = Auth::id();
        $validated['author_id'] = Auth::id();
        $validated['status'] = $validated['status'] ?? 'draft';

        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $filename = 'block-' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('media', $filename);
            $validated['featured_image'] = 'media/' . $filename;
        } elseif ($request->filled('featured_image')) {
            $validated['featured_image'] = $request->featured_image;
        }

        Content::create($validated);

        return redirect()->back()->with('success', 'Content block created.');
    }

    public function updateBlock(Request $request, Content $block)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:post,news,article,faq,testimonial',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'is_published' => 'boolean',
            'status' => 'nullable|in:draft,published,archived',
        ]);

        if ($request->hasFile('featured_image')) {
            if ($block->featured_image) {
                Storage::delete($block->featured_image);
            }
            $file = $request->file('featured_image');
            $filename = 'block-' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('media', $filename);
            $validated['featured_image'] = 'media/' . $filename;
        } elseif ($request->filled('featured_image')) {
            $validated['featured_image'] = $request->featured_image;
        }

        $validated['updated_by'] = Auth::id();
        $block->update($validated);

        return redirect()->back()->with('success', 'Content block updated.');
    }

    public function destroyBlock(Content $block)
    {
        if ($block->featured_image) {
            Storage::delete($block->featured_image);
        }
        $block->delete();
        return redirect()->back()->with('success', 'Content block deleted.');
    }

    // ─── TESTIMONIALS ───

    public function storeTestimonial(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'content' => 'required|string',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'testimonial-' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('media', $filename);
            $validated['image'] = 'media/' . $filename;
        } elseif ($request->filled('image')) {
            $validated['image'] = $request->image;
        }

        Testimonial::create($validated);
        return redirect()->back()->with('success', 'Testimonial added.');
    }

    public function updateTestimonial(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'content' => 'required|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($testimonial->image) {
                Storage::delete($testimonial->image);
            }
            $file = $request->file('image');
            $filename = 'testimonial-' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('media', $filename);
            $validated['image'] = 'media/' . $filename;
        } elseif ($request->filled('image')) {
            $validated['image'] = $request->image;
        }

        $testimonial->update($validated);
        return redirect()->back()->with('success', 'Testimonial updated.');
    }

    public function destroyTestimonial(Testimonial $testimonial)
    {
        if ($testimonial->image) {
            Storage::delete($testimonial->image);
        }
        $testimonial->delete();
        return redirect()->back()->with('success', 'Testimonial deleted.');
    }

    // ─── MEDIA MANAGER ───

    public function uploadMedia(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg|max:10240',
        ]);

        $dir = $request->get('dir', 'media');
        $file = $request->file('image');
        $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            . '-' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($dir, $filename);

        return response()->json([
            'success' => true,
            'path' => $dir . '/' . $filename,
            'url' => Storage::url($dir . '/' . $filename),
            'filename' => $filename,
        ]);
    }

    public function deleteMedia(Request $request)
    {
        $path = $request->get('path');
        if ($path && Storage::exists($path)) {
            Storage::delete($path);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'File not found.'], 404);
    }

    public function deleteMediaBulk(Request $request)
    {
        $paths = $request->get('paths', []);
        if (!is_array($paths) || empty($paths)) {
            return response()->json(['success' => false, 'message' => 'No paths provided.'], 400);
        }
        $deleted = 0;
        foreach ($paths as $path) {
            if ($path && Storage::exists($path)) {
                Storage::delete($path);
                $deleted++;
            }
        }
        return response()->json(['success' => true, 'deleted' => $deleted]);
    }

    // ─── HELPERS ───

    protected function handleRepeatable(array &$items, Request $request, string $type)
    {
        $action = $request->get('action', 'add');

        if (in_array($action, ['add', 'update'])) {
            $entry = [];
            // Use input bag only (not merged input+files) because files->remove may not
            // actually remove files from the bag, causing UploadedFile objects to override
            // already-merged string values (e.g. flag, image) in array_replace_recursive.
            $excluded = ['_token', 'section', 'action', 'index', '_method'];
            foreach ($request->request->all() as $key => $value) {
                if (in_array($key, $excluded, true)) continue;
                $entry[$key] = is_scalar($value) ? $value : '';
            }
            $index = $request->get('index');
            if ($index !== null && isset($items[$index])) {
                $items[$index] = $entry;
            } else {
                $items[] = $entry;
            }
        } elseif ($action === 'delete') {
            $index = $request->get('index');
            if (isset($items[$index])) {
                unset($items[$index]);
                $items = array_values($items);
            }
        }
    }

    protected function handleEventUpdate(array &$events, Request $request, string $action)
    {
        if (in_array($action, ['add', 'update'])) {
            $entry = [
                'event_type' => $request->event_type ?? 'Event',
                'title' => $request->title ?? '',
                'description' => $request->description ?? '',
                'date' => $request->date ?? '',
                'link' => $request->link ?? '',
                'image' => $request->image ?? '',
            ];
            if ($request->hasFile('image_file')) {
                $file = $request->file('image_file');
                $filename = 'event-' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('media', $filename);
                $entry['image'] = 'media/' . $filename;
            } elseif ($request->filled('image_file')) {
                $entry['image'] = $request->image_file;
            }
            $index = $request->get('index');
            if ($index !== null && isset($events[$index])) {
                $events[$index] = $entry;
            } else {
                $events[] = $entry;
            }
        } elseif ($action === 'delete') {
            $index = $request->get('index');
            if (isset($events[$index])) {
                unset($events[$index]);
                $events = array_values($events);
            }
        }
    }

    protected function getUploadedImages(): array
    {
        $imageDirs = ['media', 'settings', 'images', 'testimonials', 'admin', 'agents', 'staff'];
        $images = [];
        foreach ($imageDirs as $dir) {
            if (!Storage::exists($dir)) continue;
            foreach (Storage::files($dir) as $file) {
                $images[] = [
                    'filename' => basename($file),
                    'path' => $file,
                    'url' => Storage::url($file),
                    'size' => Storage::size($file),
                    'last_modified' => Storage::lastModified($file),
                    'dir' => $dir,
                ];
            }
        }
        usort($images, fn($a, $b) => $b['last_modified'] - $a['last_modified']);
        return $images;
    }

    protected function getUniLogos(): array
    {
        $dir = 'uni_logo';
        $images = [];
        if (!Storage::exists($dir)) return $images;
        foreach (Storage::files($dir) as $file) {
            $images[] = [
                'filename' => basename($file),
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
