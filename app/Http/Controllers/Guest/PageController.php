<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Page;

class PageController extends Controller
{
    public function show($slug)
    {
        $page = Page::where('slug', $slug)
            ->where('is_published', true)
            ->where('status', 'published')
            ->firstOrFail();

        return view('guest.pages.show', compact('page'));
    }

    public function faq()
    {
        $faqs = Content::byType('faq')->published()->latest('sort_order')->get();
        return view('guest.faq', compact('faqs'));
    }
}
