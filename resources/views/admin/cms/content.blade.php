@extends('layouts.admin')
@section('admin-content')
    <x-page-header title="Content Management" subtitle="Global sections, dynamic UI, content blocks, and media" />
    <ul class="nav nav-tabs border-0 mb-3 gap-1" role="tablist">
        @foreach ($tabs as $tabKey => $tab)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == $tabKey ? 'active' : '' }} py-2 px-3"
                    onclick="window.location.href='{{ route('admin.content.index', ['tab' => $tabKey]) }}'" type="button"
                    style="font-size:var(--text-sm);">
                    <i class="fas fa-{{ $tab['icon'] }} me-1"></i>{{ $tab['name'] }}
                </button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content">
        {{-- ═══════════════ TAB: GLOBAL SECTIONS ═══════════════ --}}
        @if ($activeTab === 'sections')
            <form method="POST" action="{{ route('admin.content.sections.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-2 px-3 border-bottom">
                                <span class="fw-semibold small"><i class="fas fa-header me-1 text-primary"></i>Header</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="mb-2">
                                    <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Site
                                        Name</label>
                                    <input type="text" name="site.name" class="form-control form-control-sm"
                                        value="{{ $siteName }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Site
                                        Logo</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <img id="logoPreview" src="{{ $siteLogo ? Storage::url($siteLogo) : '' }}" alt=""
                                            style="height:32px;border-radius:4px;{{ $siteLogo ? '' : 'display:none;' }}">
                                        <input type="file" name="site.logo" class="form-control form-control-sm"
                                            style="font-size:11px;" accept="image/*">
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0"
                                            style="font-size:10px;" data-gallery-target="site.logo"
                                            data-gallery-preview="logoPreview" data-gallery-hidden="logoHidden"><i
                                                class="fas fa-images me-1"></i>Gallery</button>
                                        <input type="hidden" name="site.logo_selected" id="logoHidden" data-gallery-field="site.logo">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Notice
                                        Bar</label>
                                    <input type="text" name="site.notice" class="form-control form-control-sm"
                                        value="{{ $siteNotice }}">
                                </div>
                                <div>
                                    <label class="form-label fw-semibold mb-1"
                                        style="font-size:var(--text-sm);">Favicon</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <img id="faviconPreview" src="{{ $favicon ? Storage::url($favicon) : '' }}" alt=""
                                            style="height:24px;border-radius:2px;{{ $favicon ? '' : 'display:none;' }}">
                                        <input type="file" name="site.favicon" class="form-control form-control-sm"
                                            style="font-size:11px;" accept="image/*">
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0"
                                            style="font-size:10px;" data-gallery-target="site.favicon"
                                            data-gallery-preview="faviconPreview" data-gallery-hidden="faviconHidden"><i
                                                class="fas fa-images me-1"></i>Gallery</button>
                                        <input type="hidden" name="site.favicon_selected" id="faviconHidden" data-gallery-field="site.favicon">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary btn-sm px-4"><i class="fas fa-save me-1"></i>Save
                                Sections</button>
                        </div>
                    </div>
                </div>
            </form>
        @endif

        {{-- ═══════════════ TAB: DYNAMIC UI ═══════════════ --}}
        @if ($activeTab === 'dynamic')
            <div class="row g-3">

                {{-- ═══ ROW 1: HERO ═══ --}}
                <div class="col-lg-12">
                    <form method="POST" action="{{ route('admin.content.dynamic.update') }}">
                        @csrf
                        <input type="hidden" name="section" value="hero">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white py-2 px-3 border-bottom">
                                <span class="fw-semibold small"><i class="fas fa-star me-1 text-warning"></i>Hero
                                    Section</span>
                            </div>
                            <div class="card-body ">
                                <div class="d-flex gap-2 p-3">
                                    <div class="col-md-8">
                                        <h6 class="fw-bold text-muted mb-2"
                                            style="font-size:var(--text-sm);letter-spacing:0.03em;text-transform:uppercase;">
                                            Content
                                        </h6>
                                        <div class="row g-2 mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold mb-1"
                                                    style="font-size:var(--text-sm);">Badge
                                                    Text</label>
                                                <input type="text" name="hero_badge" class="form-control form-control-sm"
                                                    value="{{ $heroBadge }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold mb-1"
                                                    style="font-size:var(--text-sm);">Title</label>
                                                <input type="text" name="hero_title" class="form-control form-control-sm"
                                                    value="{{ $heroTitle }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold mb-1"
                                                    style="font-size:var(--text-sm);">Description / Subtitle</label>
                                                <textarea name="hero_subtitle" class="form-control form-control-sm" rows="2" style="font-size:12px;">{{ $heroSubtitle }}</textarea>
                                            </div>

                                        </div>

                                        <h6 class="fw-bold text-muted mb-2 mt-3"
                                            style="font-size:var(--text-sm);letter-spacing:0.03em;text-transform:uppercase;">
                                            Stats
                                        </h6>
                                        <div class="row g-2 mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold mb-1"
                                                    style="font-size:var(--text-sm);">Value: </label>
                                                <input type="text" name="hero_stat1_value"
                                                    class="form-control form-control-sm" value="{{ $heroStat1Value }}"
                                                    placeholder="Auto from DB">
                                                <label class="form-label fw-semibold mb-1"
                                                    style="font-size:var(--text-sm);">Label: </label>
                                                <input type="text" name="hero_stat1_label"
                                                    class="form-control form-control-sm" value="{{ $heroStat1Label }}">
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold mb-1"
                                                    style="font-size:var(--text-sm);">Value: </label>
                                                <input type="text" name="hero_stat2_value"
                                                    class="form-control form-control-sm" value="{{ $heroStat2Value }}"
                                                    placeholder="Auto from DB">
                                                <label class="form-label fw-semibold mb-1"
                                                    style="font-size:var(--text-sm);">Label: </label>
                                                <input type="text" name="hero_stat2_label"
                                                    class="form-control form-control-sm" value="{{ $heroStat2Label }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold mb-1"
                                                    style="font-size:var(--text-sm);">Value:</label>
                                                <input type="text" name="hero_stat3_value"
                                                    class="form-control form-control-sm" value="{{ $heroStat3Value }}"
                                                    placeholder="Auto from DB">
                                                <label class="form-label fw-semibold mb-1"
                                                    style="font-size:var(--text-sm);">Label:</label>
                                                <input type="text" name="hero_stat3_label"
                                                    class="form-control form-control-sm" value="{{ $heroStat3Label }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="fw-bold text-muted mb-2 mt-3"
                                            style="font-size:var(--text-sm);letter-spacing:0.03em;text-transform:uppercase;">
                                            Login
                                            Form</h6>
                                        <div>
                                            <label class="form-label fw-semibold mb-1"
                                                style="font-size:var(--text-sm);">Form
                                                Title</label>
                                            <input type="text" name="hero_form_title"
                                                class="form-control form-control-sm" value="{{ $heroFormTitle }}">
                                        </div>
                                        <div>
                                            <label class="form-label fw-semibold mb-1"
                                                style="font-size:var(--text-sm);">Form
                                                Description</label>
                                            <input type="text" name="hero_form_description"
                                                class="form-control form-control-sm" value="{{ $heroFormDescription }}">
                                        </div>
                                        <h6 class="fw-bold text-muted mb-2 mt-3"
                                            style="font-size:var(--text-sm);letter-spacing:0.03em;text-transform:uppercase;">
                                            Buttons
                                        </h6>
                                        <div>
                                            <label class="form-label fw-semibold mb-1"
                                                style="font-size:var(--text-sm);">Button 1 Text</label>
                                            <input type="text" name="hero_btn1_text"
                                                class="form-control form-control-sm" value="{{ $heroBtn1Text }}">
                                        </div>
                                        <div>
                                            <label class="form-label fw-semibold mb-1"
                                                style="font-size:var(--text-sm);">Button 1 Link</label>
                                            <input type="text" name="hero_btn1_link"
                                                class="form-control form-control-sm" value="{{ $heroBtn1Link }}">
                                        </div>
                                        <div>
                                            <label class="form-label fw-semibold mb-1"
                                                style="font-size:var(--text-sm);">Button 2 Text</label>
                                            <input type="text" name="hero_btn2_text"
                                                class="form-control form-control-sm" value="{{ $heroBtn2Text }}">
                                        </div>
                                        <div>
                                            <label class="form-label fw-semibold mb-1"
                                                style="font-size:var(--text-sm);">Button 2 Link</label>
                                            <input type="text" name="hero_btn2_link"
                                                class="form-control form-control-sm" value="{{ $heroBtn2Link }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-sm btn-warning" style="font-size:11px;">
                                        <i text-dark class="fas fa-save me-1"></i>Save Hero</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- ═══ ROW 2: FILTER ═══ --}}
                <div class="col-lg-12">
                    <form method="POST" action="{{ route('admin.content.dynamic.update') }}">
                        @csrf
                        <input type="hidden" name="section" value="section_headings">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white py-2 px-3 border-bottom">
                                <span class="fw-semibold small"><i class="fas fa-search me-1 text-info"></i>Filter
                                    Section</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold mb-1"
                                            style="font-size:var(--text-sm);">Title</label>
                                        <input type="text" name="filter_title" class="form-control form-control-sm"
                                            value="{{ $sectionFilterTitle }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold mb-1"
                                            style="font-size:var(--text-sm);">Description</label>
                                        <input type="text" name="filter_description"
                                            class="form-control form-control-sm" value="{{ $sectionFilterDesc }}">
                                    </div>
                                </div>
                                <div class="text-end mt-2">
                                    <button type="submit" class="btn btn-sm btn-info" style="font-size:11px;"><i
                                            class="fas fa-save me-1"></i>Save Filter</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- ═══ ROW 3: FEATURES / WHY CHOOSE US ═══ --}}
                <div class="col-lg-12">
                    <form method="POST" action="{{ route('admin.content.dynamic.update') }}">
                        @csrf
                        <input type="hidden" name="section" value="section_headings">
                        <div class="card border-0 shadow-sm mb-3">
                            <div
                                class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                                <span class="fw-semibold small"><i class="fas fa-th-large me-1 text-primary"></i>Why
                                    Choose Us — Features</span>
                                <button type="button" class="btn btn-sm btn-primary py-0" style="font-size:11px;"
                                    data-bs-toggle="modal" data-bs-target="#featuresModal">
                                    <i class="fas fa-plus me-1"></i>Add Feature
                                </button>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold mb-1"
                                            style="font-size:var(--text-sm);">Section Tag</label>
                                        <input type="text" name="features_tag" class="form-control form-control-sm"
                                            value="{{ $sectionFeaturesTag }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold mb-1"
                                            style="font-size:var(--text-sm);">Section Title</label>
                                        <input type="text" name="features_title" class="form-control form-control-sm"
                                            value="{{ $sectionFeaturesTitle }}">
                                    </div>
                                </div>
                                <div class="text-end mb-2">
                                    <button type="submit" class="btn btn-sm btn-primary" style="font-size:11px;"><i
                                            class="fas fa-save me-1"></i>Save Section
                                        Headings</button>
                                </div>
                                <hr>
                                @if (count($features))
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0" style="font-size:var(--text-sm);">
                                            <thead class="table-light small text-muted">
                                                <tr>
                                                    <th>Icon</th>
                                                    <th>Title</th>
                                                    <th>Text</th>
                                                    <th>Link</th>
                                                    <th class="text-end" style="width:80px;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($features as $i => $f)
                                                    <tr>
                                                        <td><i class="fas {{ $f['icon'] ?? 'fa-star' }}"></i></td>
                                                        <td class="fw-semibold">{{ $f['title'] ?? '' }}</td>
                                                        <td><span class="text-truncate d-inline-block"
                                                                style="max-width:200px;">{{ $f['text'] ?? '' }}</span>
                                                        </td>
                                                        <td>
                                                            @if (!empty($f['link']))
                                                                <a href="{{ $f['link'] }}" target="_blank"
                                                                class="small">View</a>@else<span
                                                                    class="text-muted small">—</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            <button type="button" class="btn btn-sm btn-ghost py-0"
                                                                title="Edit" data-bs-toggle="modal"
                                                                data-bs-target="#featuresModal"
                                                                data-index="{{ $i }}"
                                                                data-icon="{{ $f['icon'] ?? '' }}"
                                                                data-title="{{ $f['title'] ?? '' }}"
                                                                data-text="{{ $f['text'] ?? '' }}"
                                                                data-link="{{ $f['link'] ?? '' }}"><i
                                                                    class="fas fa-edit text-primary"
                                                                    style="font-size:11px;"></i></button>
                                                            <form method="POST"
                                                                action="{{ route('admin.content.dynamic.update') }}"
                                                                class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="section"
                                                                    value="features"><input type="hidden" name="action"
                                                                    value="delete"><input type="hidden" name="index"
                                                                    value="{{ $i }}">
                                                                <button type="submit" class="btn btn-sm btn-ghost py-0"
                                                                    onclick="return confirm('Delete?')"><i
                                                                        class="fas fa-trash text-danger"
                                                                        style="font-size:11px;"></i></button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-3">
                                        <p class="text-muted small mb-0">No features. Add one above.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                {{-- ═══ ROW 4: EVENTS ═══ --}}
                <div class="col-lg-12">
                    <form method="POST" action="{{ route('admin.content.dynamic.update') }}">
                        @csrf
                        <input type="hidden" name="section" value="section_headings">
                        <div class="card border-0 shadow-sm mb-3">
                            <div
                                class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                                <span class="fw-semibold small"><i
                                        class="fas fa-calendar-alt me-1 text-warning"></i>Upcoming Events</span>
                                <button type="button" class="btn btn-sm btn-warning py-0" style="font-size:11px;"
                                    data-bs-toggle="modal" data-bs-target="#eventsModal">
                                    <i class="fas fa-plus me-1"></i>Add Event
                                </button>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold mb-1"
                                            style="font-size:var(--text-sm);">Section Tag</label>
                                        <input type="text" name="events_tag" class="form-control form-control-sm"
                                            value="{{ $sectionEventsTag }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold mb-1"
                                            style="font-size:var(--text-sm);">Section Title</label>
                                        <input type="text" name="events_title" class="form-control form-control-sm"
                                            value="{{ $sectionEventsTitle }}">
                                    </div>
                                </div>
                                <div class="text-end mb-2">
                                    <button type="submit" class="btn btn-sm btn-warning" style="font-size:11px;"><i
                                            class="fas fa-save me-1"></i>Save Section
                                        Headings</button>
                                </div>
                                <hr>
                                @if (count($events))
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0" style="font-size:var(--text-sm);">
                                            <thead class="table-light small text-muted">
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Title</th>
                                                    <th>Date</th>
                                                    <th style="width:100px;">Link</th>
                                                    <th class="text-end" style="width:80px;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($events as $i => $e)
                                                    <tr>
                                                        <td><span class="badge bg-light text-muted"
                                                                style="font-size:10px;">{{ $e['event_type'] ?? 'Event' }}</span>
                                                        </td>
                                                        <td class="fw-semibold">{{ $e['title'] ?? '' }}</td>
                                                        <td class="small text-muted">{{ !empty($e['date']) ? \Carbon\Carbon::parse($e['date'])->format('M d, Y') : '' }}</td>
                                                        <td>
                                                            @if (!empty($e['link']))
                                                                <a href="{{ $e['link'] }}" target="_blank"
                                                                    class="small"><i
                                                                    class="fas fa-external-link-alt"></i></a>@else<span
                                                                    class="text-muted small">—</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            <button type="button" class="btn btn-sm btn-ghost py-0"
                                                                title="Edit" data-bs-toggle="modal"
                                                                data-bs-target="#eventsModal"
                                                                data-index="{{ $i }}"
                                                                data-event_type="{{ $e['event_type'] ?? '' }}"
                                                                data-title="{{ $e['title'] ?? '' }}"
                                                                data-description="{{ $e['description'] ?? '' }}"
                                                                data-date="{{ $e['date'] ?? '' }}"
                                                                data-link="{{ $e['link'] ?? '' }}"><i
                                                                    class="fas fa-edit text-primary"
                                                                    style="font-size:11px;"></i></button>
                                                            <form method="POST"
                                                                action="{{ route('admin.content.dynamic.update') }}"
                                                                class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="section"
                                                                    value="events"><input type="hidden" name="action"
                                                                    value="delete"><input type="hidden" name="index"
                                                                    value="{{ $i }}">
                                                                <button type="submit" class="btn btn-sm btn-ghost py-0"
                                                                    onclick="return confirm('Delete?')"><i
                                                                        class="fas fa-trash text-danger"
                                                                        style="font-size:11px;"></i></button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-3">
                                        <p class="text-muted small mb-0">No events. Add one above.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                {{-- ═══ ROW 5: BANNERS ═══ --}}
                <div class="col-lg-12">
                    <div class="card border-0 shadow-sm mb-3">
                        <div
                            class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                            <span class="fw-semibold small"><i class="fas fa-images me-1 text-success"></i>Banners</span>
                            <button type="button" class="btn btn-sm btn-success py-0" style="font-size:11px;"
                                data-bs-toggle="modal" data-bs-target="#bannersModal"><i class="fas fa-plus me-1"></i>Add
                                Banner</button>
                        </div>
                        <div class="card-body p-0">
                            @if (count($banners))
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" style="font-size:var(--text-sm);">
                                        <thead class="table-light small text-muted">
                                            <tr>
                                                <th>Image</th>
                                                <th>Title</th>
                                                <th>Link</th>
                                                <th class="text-end" style="width:80px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($banners as $i => $b)
                                                <tr>
                                                    <td>
                                                        @if (!empty($b['image']))
                                                            <img src="{{ Storage::url($b['image']) }}" alt=""
                                                            style="height:28px;border-radius:3px;">@else<span
                                                                class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="fw-semibold">{{ $b['title'] ?? '' }}</td>
                                                    <td>
                                                        @if (!empty($b['link']))
                                                            <a href="{{ $b['link'] }}" target="_blank"
                                                            class="small">View</a>@else<span
                                                                class="text-muted small">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        <button type="button" class="btn btn-sm btn-ghost py-0"
                                                            title="Edit" data-bs-toggle="modal"
                                                            data-bs-target="#bannersModal"
                                                            data-index="{{ $i }}"
                                                            data-image="{{ $b['image'] ?? '' }}"
                                                            data-title="{{ $b['title'] ?? '' }}"
                                                            data-link="{{ $b['link'] ?? '' }}"><i
                                                                class="fas fa-edit text-primary"
                                                                style="font-size:11px;"></i></button>
                                                        <form method="POST"
                                                            action="{{ route('admin.content.dynamic.update') }}"
                                                            class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="section" value="banners"><input
                                                                type="hidden" name="action" value="delete"><input
                                                                type="hidden" name="index"
                                                                value="{{ $i }}">
                                                            <button type="submit" class="btn btn-sm btn-ghost py-0"
                                                                onclick="return confirm('Delete?')"><i
                                                                    class="fas fa-trash text-danger"
                                                                    style="font-size:11px;"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <p class="text-muted small mb-0">No banners.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ═══ ROW 6: COUNTRIES ═══ --}}
                <div class="col-lg-12">
                    <form method="POST" action="{{ route('admin.content.dynamic.update') }}">
                        @csrf
                        <input type="hidden" name="section" value="section_headings">
                        <div class="card border-0 shadow-sm mb-3">
                            <div
                                class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                                <span class="fw-semibold small"><i class="fas fa-globe me-1 text-purple"
                                        style="color:#1a0262;"></i>Countries</span>
                                <button type="button" class="btn btn-sm"
                                    style="font-size:11px;background:#1a0262;color:#fff;border:none;"
                                    data-bs-toggle="modal" data-bs-target="#countriesModal"><i
                                        class="fas fa-plus me-1"></i>Add Country</button>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold mb-1"
                                            style="font-size:var(--text-sm);">Section Title</label>
                                        <input type="text" name="countries_title" class="form-control form-control-sm"
                                            value="{{ $countriesTitle }}">
                                    </div>
                                </div>
                                <div class="text-end mb-2">
                                    <button type="submit" class="btn btn-sm"
                                        style="font-size:11px;background:#1a0262;color:#fff;border:none;"><i
                                            class="fas fa-save me-1"></i>Save Countries</button>
                                </div>
                                <hr>
                                @if (count($countries))
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0" style="font-size:var(--text-sm);">
                                            <thead class="table-light small text-muted">
                                                <tr>
                                                    <th style="width:40px;">Sort</th>
                                                    <th>Flag</th>
                                                    <th>Name</th>
                                                    <th>Description</th>
                                                    <th class="text-end" style="width:120px;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($countries as $i => $c)
                                                    <tr>
                                                        <td class="text-muted" style="font-size:11px;">{{ $i + 1 }}</td>
                                                        <td>
                                                            @php $flagPath = $c['flag'] ?? ($c['image'] ?? ''); @endphp
                                                            @if (!empty($flagPath))
                                                                <img src="{{ Storage::url($flagPath) }}" alt=""
                                                                style="height:20px;border-radius:2px;">@else<span
                                                                    class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                        <td class="fw-semibold">{{ $c['name'] ?? '' }}</td>
                                                        <td><span class="text-truncate d-inline-block"
                                                                 style="max-width:300px;">{{ $c['description'] ?? '' }}</span>
                                                        </td>
                                                        <td class="text-end" style="white-space:nowrap;">
                                                            @if($i > 0)
                                                            <form method="POST"
                                                                action="{{ route('admin.content.dynamic.update') }}"
                                                                class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="section" value="countries">
                                                                <input type="hidden" name="action" value="sort">
                                                                <input type="hidden" name="direction" value="up">
                                                                <input type="hidden" name="index" value="{{ $i }}">
                                                                <button type="submit" class="btn btn-sm btn-ghost py-0"
                                                                    title="Move up"><i class="fas fa-chevron-up text-muted" style="font-size:10px;"></i></button>
                                                            </form>
                                                            @endif
                                                            @if($i < count($countries) - 1)
                                                            <form method="POST"
                                                                action="{{ route('admin.content.dynamic.update') }}"
                                                                class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="section" value="countries">
                                                                <input type="hidden" name="action" value="sort">
                                                                <input type="hidden" name="direction" value="down">
                                                                <input type="hidden" name="index" value="{{ $i }}">
                                                                <button type="submit" class="btn btn-sm btn-ghost py-0"
                                                                    title="Move down"><i class="fas fa-chevron-down text-muted" style="font-size:10px;"></i></button>
                                                            </form>
                                                            @endif
                                                            <button type="button" class="btn btn-sm btn-ghost py-0"
                                                                title="Edit" data-bs-toggle="modal"
                                                                data-bs-target="#countriesModal"
                                                                data-index="{{ $i }}"
                                                                data-flag="{{ $c['flag'] ?? ($c['image'] ?? '') }}"
                                                                data-name="{{ $c['name'] ?? '' }}"
                                                                data-description="{{ $c['description'] ?? '' }}"><i
                                                                    class="fas fa-edit text-primary"
                                                                    style="font-size:11px;"></i></button>
                                                            <form method="POST"
                                                                action="{{ route('admin.content.dynamic.update') }}"
                                                                class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="section"
                                                                    value="countries"><input type="hidden"
                                                                    name="action" value="delete"><input type="hidden"
                                                                    name="index" value="{{ $i }}">
                                                                <button type="submit" class="btn btn-sm btn-ghost py-0"
                                                                    onclick="return confirm('Delete?')"><i
                                                                        class="fas fa-trash text-danger"
                                                                        style="font-size:11px;"></i></button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-3">
                                        <p class="text-muted small mb-0">No countries.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                {{-- ═══ ROW 7: CTA ═══ --}}
                <div class="col-lg-12">
                    <form method="POST" action="{{ route('admin.content.dynamic.update') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="section" value="cta">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white py-2 px-3 border-bottom">
                                <span class="fw-semibold small"><i class="fas fa-bullhorn me-1 text-danger"></i>CTA
                                    Section</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold mb-1"
                                            style="font-size:var(--text-sm);">Title</label>
                                        <input type="text" name="cta_title" class="form-control form-control-sm"
                                            value="{{ $ctaTitle }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold mb-1"
                                            style="font-size:var(--text-sm);">Description</label>
                                        <textarea name="cta_description" class="form-control form-control-sm" rows="2" style="font-size:12px;">{{ $ctaDescription }}</textarea>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold mb-1"
                                            style="font-size:var(--text-sm);">Button Text</label>
                                        <input type="text" name="cta_button_text" class="form-control form-control-sm"
                                            value="{{ $ctaButtonText }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold mb-1"
                                            style="font-size:var(--text-sm);">Button Link</label>
                                        <input type="text" name="cta_button_link" class="form-control form-control-sm"
                                            value="{{ $ctaButtonLink }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold mb-1"
                                            style="font-size:var(--text-sm);">Image</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <img id="ctaImagePreview" src="{{ $ctaImage ? Storage::url($ctaImage) : '' }}" alt=""
                                                style="height:28px;border-radius:3px;{{ $ctaImage ? '' : 'display:none;' }}">
                                            <input type="file" name="cta_image" class="form-control form-control-sm"
                                                style="font-size:11px;" accept="image/*">
                                            <button type="button" class="btn btn-sm btn-outline-secondary py-0"
                                                style="font-size:10px;" data-gallery-target="cta_image"
                                                data-gallery-preview="ctaImagePreview" data-gallery-hidden="ctaImageHidden"><i
                                                    class="fas fa-images me-1"></i>Gallery</button>
                                            <input type="hidden" name="cta_image_selected" id="ctaImageHidden" data-gallery-field="cta_image">
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end mt-2">
                                    <button type="submit" class="btn btn-sm btn-danger" style="font-size:11px;"><i
                                            class="fas fa-save me-1"></i>Save CTA</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

            </div>

            {{-- ════════ REPEATABLE MODALS ════════ --}}
            <x-modal id="featuresModal" title="Feature" size="md">
                <x-slot:body>
                    <form method="POST" action="{{ route('admin.content.dynamic.update') }}" id="featuresForm">
                        @csrf
                        <input type="hidden" name="section" value="features">
                        <input type="hidden" name="action" id="featuresAction" value="add">
                        <input type="hidden" name="index" id="featuresIndex">
                        <div class="mb-2">
                            <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Icon (Font
                                Awesome)</label>
                            <input type="text" name="icon" id="featuresIcon" class="form-control form-control-sm"
                                placeholder="fa-user-graduate">
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Title</label>
                            <input type="text" name="title" id="featuresTitle"
                                class="form-control form-control-sm">
                        </div>
                        <div>
                            <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Text</label>
                            <textarea name="text" id="featuresText" class="form-control form-control-sm" rows="2"
                                style="font-size:12px;"></textarea>
                        </div>
                        <div class="mt-2">
                            <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Link</label>
                            <input type="text" name="link" id="featuresLink" class="form-control form-control-sm"
                                placeholder="https://...">
                        </div>
                    </form>
                </x-slot:body>
                <x-slot:footer>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="featuresForm" class="btn btn-primary btn-sm"><i
                            class="fas fa-save me-1"></i>Save</button>
                </x-slot:footer>
            </x-modal>

            <x-modal id="eventsModal" title="Event" size="md">
                <x-slot:body>
                    <form method="POST" action="{{ route('admin.content.dynamic.update') }}" id="eventsForm">
                        @csrf
                        <input type="hidden" name="section" value="events">
                        <input type="hidden" name="action" id="eventsAction" value="add">
                        <input type="hidden" name="index" id="eventsIndex">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Type</label>
                                <input type="text" name="event_type" id="eventsType"
                                    class="form-control form-control-sm" placeholder="Webinar">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Title</label>
                                <input type="text" name="title" id="eventsTitle"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Date</label>
                                <input type="date" name="date" id="eventsDate"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Link</label>
                                <input type="text" name="link" id="eventsLink"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold mb-1"
                                    style="font-size:var(--text-sm);">Description</label>
                                <textarea name="description" id="eventsDescription" class="form-control form-control-sm" rows="2"
                                    style="font-size:12px;"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Image</label>
                                <div class="d-flex gap-2 align-items-start">
                                    <div>
                                        <input type="file" name="image_file" id="eventsImage"
                                            class="form-control form-control-sm" accept="image/*"
                                            onchange="document.getElementById('eventsImagePreview').src=URL.createObjectURL(this.files[0])">
                                        <img id="eventsImagePreview" src=""
                                            style="height:32px;margin-top:4px;border-radius:4px;display:none;">
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 mt-0"
                                        style="font-size:10px;" data-gallery-target="image_file"
                                        data-gallery-preview="eventsImagePreview" data-gallery-hidden="eventsImageHidden"><i
                                            class="fas fa-images me-1"></i>Gallery</button>
                                    <input type="hidden" name="image_selected" id="eventsImageHidden" data-gallery-field="image_file">
                                </div>
                            </div>
                        </div>
                    </form>
                </x-slot:body>
                <x-slot:footer>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="eventsForm" class="btn btn-primary btn-sm"><i
                            class="fas fa-save me-1"></i>Save</button>
                </x-slot:footer>
            </x-modal>

            <x-modal id="bannersModal" title="Banner" size="md">
                <x-slot:body>
                    <form method="POST" action="{{ route('admin.content.dynamic.update') }}" id="bannersForm"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="section" value="banners">
                        <input type="hidden" name="action" id="bannersAction" value="add">
                        <input type="hidden" name="index" id="bannersIndex">
                        <div class="mb-2">
                            <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Image</label>
                            <div class="d-flex gap-2 align-items-start">
                                <div>
                                    <input type="file" name="image" id="bannersImage"
                                        class="form-control form-control-sm mb-1" accept="image/*"
                                        onchange="document.getElementById('bannersImagePreview').src=URL.createObjectURL(this.files[0])">
                                    <img id="bannersImagePreview" src=""
                                        style="height:32px;border-radius:4px;display:none;">
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 mt-0"
                                    style="font-size:10px;" data-gallery-target="image"
                                    data-gallery-preview="bannersImagePreview" data-gallery-hidden="bannersImageHidden"><i
                                        class="fas fa-images me-1"></i>Gallery</button>
                                <input type="hidden" name="image_selected" id="bannersImageHidden" data-gallery-field="image">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Title</label>
                            <input type="text" name="title" id="bannersTitle" class="form-control form-control-sm">
                        </div>
                        <div>
                            <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Link</label>
                            <input type="text" name="link" id="bannersLink" class="form-control form-control-sm">
                        </div>
                    </form>
                </x-slot:body>
                <x-slot:footer>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="bannersForm" class="btn btn-primary btn-sm"><i
                            class="fas fa-save me-1"></i>Save</button>
                </x-slot:footer>
            </x-modal>

            <x-modal id="countriesModal" title="Country" size="md">
                <x-slot:body>
                    <form method="POST" action="{{ route('admin.content.dynamic.update') }}" id="countriesForm"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="section" value="countries">
                        <input type="hidden" name="action" id="countriesAction" value="add">
                        <input type="hidden" name="index" id="countriesIndex">
                        <div class="mb-2">
                            <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Flag
                                Image</label>
                            <div class="d-flex gap-2 align-items-start">
                                <div>
                                    <input type="file" name="flag" id="countriesFlag"
                                        class="form-control form-control-sm mb-1" accept="image/*"
                                        onchange="document.getElementById('countriesFlagPreview').src=URL.createObjectURL(this.files[0])">
                                    <img id="countriesFlagPreview" src=""
                                        style="height:32px;border-radius:4px;display:none;">
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 mt-0"
                                    style="font-size:10px;" data-gallery-target="flag"
                                    data-gallery-preview="countriesFlagPreview" data-gallery-hidden="countriesFlagHidden"><i
                                        class="fas fa-images me-1"></i>Gallery</button>
                                <input type="hidden" name="flag_selected" id="countriesFlagHidden" data-gallery-field="flag">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Name</label>
                            <input type="text" name="name" id="countriesName"
                                class="form-control form-control-sm">
                        </div>
                        <div>
                            <label class="form-label fw-semibold mb-1"
                                style="font-size:var(--text-sm);">Description</label>
                            <textarea name="description" id="countriesDescription" class="form-control form-control-sm" rows="2"
                                style="font-size:12px;"></textarea>
                        </div>
                    </form>
                </x-slot:body>
                <x-slot:footer>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="countriesForm" class="btn btn-primary btn-sm"><i
                            class="fas fa-save me-1"></i>Save</button>
                </x-slot:footer>
            </x-modal>



            <script>
                document.querySelectorAll('[data-bs-target="#featuresModal"]').forEach(btn => {
                    btn.addEventListener('click', function() {
                        document.getElementById('featuresAction').value = this.dataset.index !== undefined ?
                            'update' : 'add';
                        document.getElementById('featuresIndex').value = this.dataset.index || '';
                        document.getElementById('featuresIcon').value = this.dataset.icon || '';
                        document.getElementById('featuresTitle').value = this.dataset.title || '';
                        document.getElementById('featuresText').value = this.dataset.text || '';
                        document.getElementById('featuresLink').value = this.dataset.link || '';
                    });
                });
                document.querySelectorAll('[data-bs-target="#eventsModal"]').forEach(btn => {
                    btn.addEventListener('click', function() {
                        document.getElementById('eventsAction').value = this.dataset.index !== undefined ?
                            'update' : 'add';
                        document.getElementById('eventsIndex').value = this.dataset.index || '';
                        document.getElementById('eventsType').value = this.dataset.event_type || '';
                        document.getElementById('eventsTitle').value = this.dataset.title || '';
                        document.getElementById('eventsDescription').value = this.dataset.description || '';
                        document.getElementById('eventsDate').value = this.dataset.date || '';
                        document.getElementById('eventsLink').value = this.dataset.link || '';
                    });
                });
                document.querySelectorAll('[data-bs-target="#bannersModal"]').forEach(btn => {
                    btn.addEventListener('click', function() {
                        var isEdit = this.dataset.index !== undefined;
                        document.getElementById('bannersAction').value = isEdit ? 'update' : 'add';
                        document.getElementById('bannersIndex').value = this.dataset.index || '';
                        document.getElementById('bannersTitle').value = this.dataset.title || '';
                        document.getElementById('bannersLink').value = this.dataset.link || '';
                        var preview = document.getElementById('bannersImagePreview');
                        if (isEdit && this.dataset.image) {
                            preview.src = '/storage/' + this.dataset.image;
                            preview.style.display = 'inline';
                        } else {
                            preview.style.display = 'none';
                        }
                    });
                });
                document.querySelectorAll('[data-bs-target="#countriesModal"]').forEach(btn => {
                    btn.addEventListener('click', function() {
                        var isEdit = this.dataset.index !== undefined;
                        document.getElementById('countriesAction').value = isEdit ? 'update' : 'add';
                        document.getElementById('countriesIndex').value = this.dataset.index || '';
                        document.getElementById('countriesName').value = this.dataset.name || '';
                        document.getElementById('countriesDescription').value = this.dataset.description || '';
                        var preview = document.getElementById('countriesFlagPreview');
                        if (isEdit && this.dataset.flag) {
                            preview.src = '/storage/' + this.dataset.flag;
                            preview.style.display = 'inline';
                        } else {
                            preview.style.display = 'none';
                        }
                    });
                });
            </script>
        @endif

        {{-- ═══════════════ TAB: POPUPS ═══════════════ --}}
        @if ($activeTab === 'popups')
            <div class="card border-0 shadow-sm mb-3">
                <div
                    class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-semibold small"><i class="fas fa-window-restore me-1 text-info"></i>Popup Cards</span>
                    <button type="button" class="btn btn-sm btn-info py-0" style="font-size:11px;"
                        data-bs-toggle="modal" data-bs-target="#popupModal" onclick="resetPopupForm()">
                        <i class="fas fa-plus me-1"></i>New Popup
                    </button>
                </div>
                <div class="card-body p-0">
                    @if (count($popups))
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size:var(--text-sm);">
                                <thead class="table-light small text-muted">
                                    <tr>
                                        <th style="width:30px;">#</th>
                                        <th>Title</th>
                                        <th>Image</th>
                                        <th>Display On</th>
                                        <th>Duration</th>
                                        <th style="width:60px;">Active</th>
                                        <th class="text-end" style="width:80px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($popups as $p)
                                        <tr>
                                            <td class="text-muted">{{ $p->sort_order }}</td>
                                            <td class="fw-semibold">{{ $p->title ?? '(no title)' }}</td>
                                            <td>
                                                @if ($p->image)
                                                    <img src="{{ Storage::url($p->image) }}" alt=""
                                                        style="height:24px;width:24px;object-fit:cover;border-radius:3px;">
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($p->display_on)
                                                    @foreach ($p->display_on as $d)
                                                        <span class="badge bg-light text-muted"
                                                            style="font-size:9px;">{{ $d }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">All</span>
                                                @endif
                                            </td>
                                            <td>{{ $p->display_duration > 0 ? $p->display_duration . 's' : 'Manual' }}</td>
                                            <td>
                                                <span class="badge {{ $p->is_active ? 'bg-success' : 'bg-secondary' }}"
                                                    style="font-size:10px;">
                                                    {{ $p->is_active ? 'Yes' : 'No' }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ url()->current() }}?preview_popup={{ $p->id }}"
                                                    target="_blank" class="btn btn-sm btn-ghost py-0" title="Preview">
                                                    <i class="fas fa-eye text-success" style="font-size:11px;"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-ghost py-0" title="Edit"
                                                    data-bs-toggle="modal" data-bs-target="#popupModal"
                                                    data-id="{{ $p->id }}" data-title="{{ $p->title }}"
                                                    data-description="{{ $p->description }}"
                                                    data-image="{{ $p->image ? Storage::url($p->image) : '' }}"
                                                    data-button_text="{{ $p->button_text }}"
                                                    data-button_link="{{ $p->button_link }}"
                                                    data-button_target="{{ $p->button_target }}"
                                                    data-show_close="{{ $p->show_close ? '1' : '0' }}"
                                                    data-display_on="{{ json_encode($p->display_on ?? []) }}"
                                                    data-display_duration="{{ $p->display_duration }}"
                                                    data-is_active="{{ $p->is_active ? '1' : '0' }}"
                                                    data-starts_at="{{ $p->starts_at ? $p->starts_at->format('Y-m-d\TH:i') : '' }}"
                                                    data-ends_at="{{ $p->ends_at ? $p->ends_at->format('Y-m-d\TH:i') : '' }}"
                                                    data-sort_order="{{ $p->sort_order }}">
                                                    <i class="fas fa-edit text-primary" style="font-size:11px;"></i>
                                                </button>
                                                <x-confirm-delete
                                                    url="{{ route('admin.content.popup.destroy', $p) }}"
                                                    label=""
                                                    title="Delete popup?"
                                                    message="Delete this popup?"
                                                    class="btn btn-sm btn-ghost py-0"
                                                />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted small mb-0">No popups yet. Click "New Popup" to create one.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Popup Create/Edit Modal --}}
            <x-modal id="popupModal" title="Popup" size="lg">
                <x-slot:body>
                    <form method="POST" action="{{ route('admin.content.popup.store') }}" id="popupForm"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" id="popupMethod" value="POST">
                        <input type="hidden" name="popup_id" id="popupId" value="">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Title</label>
                                <input type="text" name="title" id="popupTitle"
                                    class="form-control form-control-sm" placeholder="Admin label (not shown)">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Sort
                                    Order</label>
                                <input type="number" name="sort_order" id="popupSortOrder"
                                    class="form-control form-control-sm" value="0" min="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Auto-Close
                                    (sec)</label>
                                <input type="number" name="display_duration" id="popupDuration"
                                    class="form-control form-control-sm" value="0" min="0"
                                    placeholder="0=manual">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Image</label>
                                <div class="d-flex gap-2 align-items-start">
                                    <div>
                                        <input type="file" name="image" id="popupImage"
                                            class="form-control form-control-sm" accept="image/*"
                                            onchange="document.getElementById('popupImagePreview').src=URL.createObjectURL(this.files[0])">
                                        <img id="popupImagePreview" src=""
                                            style="height:40px;margin-top:4px;border-radius:4px;display:none;">
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 mt-0"
                                        style="font-size:10px;" data-gallery-target="image"
                                        data-gallery-preview="popupImagePreview" data-gallery-hidden="popupImageHidden"><i
                                            class="fas fa-images me-1"></i>Gallery</button>
                                    <input type="hidden" name="image_selected" id="popupImageHidden" data-gallery-field="image">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Start</label>
                                <input type="datetime-local" name="starts_at" id="popupStartsAt"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">End</label>
                                <input type="datetime-local" name="ends_at" id="popupEndsAt"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold mb-1"
                                    style="font-size:var(--text-sm);">Description</label>
                                <textarea name="description" id="popupDescription" class="form-control form-control-sm" rows="2"
                                    style="font-size:12px;" placeholder="Popup message body"></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Button
                                    Text</label>
                                <input type="text" name="button_text" id="popupBtnText"
                                    class="form-control form-control-sm" placeholder="Learn More">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Button
                                    Link</label>
                                <input type="text" name="button_link" id="popupBtnLink"
                                    class="form-control form-control-sm" placeholder="https://...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1"
                                    style="font-size:var(--text-sm);">Target</label>
                                <select name="button_target" id="popupBtnTarget" class="form-select form-select-sm"
                                    style="font-size:12px;">
                                    <option value="_self">Same tab</option>
                                    <option value="_blank">New tab</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Display
                                    On</label>
                                <div class="d-flex flex-wrap gap-3">
                                    @php
                                        $pageOptions = [
                                            'guest' => 'Guest / Welcome Page',
                                            'agent' => 'Agent Dashboard',
                                            'university' => 'University Index',
                                            'course' => 'Course Index',
                                            'student' => 'Student Pages',
                                            'all' => 'All Pages',
                                        ];
                                    @endphp
                                    @foreach ($pageOptions as $val => $label)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="display_on[]"
                                                value="{{ $val }}" id="page_{{ $val }}">
                                            <label class="form-check-label small"
                                                for="page_{{ $val }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-12 d-flex gap-4 pt-2 border-top mt-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="show_close" value="1"
                                        id="popupShowClose" checked>
                                    <label class="form-check-label small" for="popupShowClose">Show Close Button</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                        id="popupIsActive" checked>
                                    <label class="form-check-label small" for="popupIsActive">Active</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </x-slot:body>
                <x-slot:footer>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="popupForm" class="btn btn-info btn-sm"><i
                            class="fas fa-save me-1"></i>Save Popup</button>
                </x-slot:footer>
            </x-modal>

            <script>
                var popupUpdateUrl = '{{ route('admin.content.popup.update', ['popup' => '__ID__']) }}';

                function resetPopupForm() {
                    document.getElementById('popupForm').action = '{{ route('admin.content.popup.store') }}';
                    document.getElementById('popupMethod').value = 'POST';
                    document.getElementById('popupId').value = '';
                    document.getElementById('popupTitle').value = '';
                    document.getElementById('popupDescription').value = '';
                    document.getElementById('popupBtnText').value = '';
                    document.getElementById('popupBtnLink').value = '';
                    document.getElementById('popupBtnTarget').value = '_self';
                    document.getElementById('popupDuration').value = '0';
                    document.getElementById('popupSortOrder').value = '0';
                    document.getElementById('popupStartsAt').value = '';
                    document.getElementById('popupEndsAt').value = '';
                    document.getElementById('popupShowClose').checked = true;
                    document.getElementById('popupIsActive').checked = true;
                    document.getElementById('popupImage').value = '';
                    document.getElementById('popupImagePreview').style.display = 'none';
                    document.querySelectorAll('[name="display_on[]"]').forEach(cb => cb.checked = false);
                }

                document.querySelectorAll('[data-bs-target="#popupModal"]').forEach(btn => {
                    btn.addEventListener('click', function() {
                        if (!this.dataset.id) return; // skip the "New Popup" button
                        var id = this.dataset.id;
                        document.getElementById('popupForm').action = popupUpdateUrl.replace('__ID__', id);
                        document.getElementById('popupMethod').value = 'POST';
                        document.getElementById('popupId').value = id;
                        document.getElementById('popupTitle').value = this.dataset.title || '';
                        document.getElementById('popupDescription').value = this.dataset.description || '';
                        document.getElementById('popupBtnText').value = this.dataset.button_text || '';
                        document.getElementById('popupBtnLink').value = this.dataset.button_link || '';
                        document.getElementById('popupBtnTarget').value = this.dataset.button_target || '_self';
                        document.getElementById('popupDuration').value = this.dataset.display_duration || '0';
                        document.getElementById('popupSortOrder').value = this.dataset.sort_order || '0';
                        document.getElementById('popupStartsAt').value = this.dataset.starts_at || '';
                        document.getElementById('popupEndsAt').value = this.dataset.ends_at || '';
                        document.getElementById('popupShowClose').checked = this.dataset.show_close === '1';
                        document.getElementById('popupIsActive').checked = this.dataset.is_active === '1';
                        document.getElementById('popupImage').value = '';
                        var preview = document.getElementById('popupImagePreview');
                        if (this.dataset.image) {
                            preview.src = this.dataset.image;
                            preview.style.display = 'inline';
                        } else {
                            preview.style.display = 'none';
                        }
                        var pages = [];
                        try {
                            pages = JSON.parse(this.dataset.display_on || '[]');
                        } catch (e) {}
                        document.querySelectorAll('[name="display_on[]"]').forEach(function(cb) {
                            cb.checked = pages.indexOf(cb.value) > -1;
                        });
                    });
                });
            </script>
        @endif

        {{-- ═══════════════ TAB: CONTENT BLOCKS ═══════════════ --}}
        @if ($activeTab === 'blocks')
            <div class="card border-0 shadow-sm mb-3">
                <div
                    class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-semibold small"><i class="fas fa-newspaper me-1 text-primary"></i>Content Blocks
                        (Blog / News / Articles)</span>
                    <button type="button" class="btn btn-sm btn-primary py-0" style="font-size:11px;"
                        data-bs-toggle="modal" data-bs-target="#addBlockModal"><i class="fas fa-plus me-1"></i>New
                        Block</button>
                </div>
                <div class="card-body p-0">
                    @if ($blocks->count())
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size:var(--text-sm);">
                                <thead class="table-light small text-muted">
                                    <tr>
                                        <th style="width:30%;">Title</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th style="width:80px;">Category</th>
                                        <th class="text-end" style="width:100px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($blocks as $block)
                                        <tr>
                                            <td class="fw-semibold text-truncate" style="max-width:0;">
                                                {{ $block->title }}</td>
                                            <td><span class="badge bg-light text-muted"
                                                    style="font-size:10px;">{{ $block->type }}</span></td>
                                            <td><span
                                                    class="badge {{ $block->status === 'published' ? 'bg-success' : ($block->status === 'draft' ? 'bg-warning text-dark' : 'bg-secondary') }}"
                                                    style="font-size:10px;">{{ $block->status ?? 'draft' }}</span></td>
                                            <td><span class="small text-muted">{{ $block->category ?? '—' }}</span></td>
                                            <td class="text-end">
                                                @if ($block->type !== 'testimonial')
                                                    <button type="button" class="btn btn-sm btn-ghost py-0"
                                                        title="Edit" data-bs-toggle="modal"
                                                        data-bs-target="#editBlockModal{{ $block->id }}"><i
                                                            class="fas fa-edit text-primary"
                                                            style="font-size:11px;"></i></button>
                                                    <x-confirm-delete
                                                        url="{{ route('admin.content.block.destroy', $block) }}"
                                                        label=""
                                                        title="Delete block?"
                                                        message="Delete this block?"
                                                        class="btn btn-sm btn-ghost py-0"
                                                    />
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-3 py-2 border-top"> {{ $blocks->links() }} </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted small mb-0">No content blocks yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Add Block Modal --}}
            <x-modal id="addBlockModal" title="New Content Block" size="lg">
                <x-slot:body>
                    <form method="POST" action="{{ route('admin.content.block.store') }}" id="addBlockForm"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold mb-1"
                                    style="font-size:var(--text-sm);">Title</label>
                                <input type="text" name="title" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-1"
                                    style="font-size:var(--text-sm);">Type</label>
                                <select name="type" class="form-select form-select-sm" style="font-size:12px;">
                                    <option value="post">Blog Post</option>
                                    <option value="news">News</option>
                                    <option value="article">Article</option>
                                    <option value="faq">FAQ</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold mb-1"
                                    style="font-size:var(--text-sm);">Category</label>
                                <input type="text" name="category" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-1"
                                    style="font-size:var(--text-sm);">Status</label>
                                <select name="status" class="form-select form-select-sm" style="font-size:12px;">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-1" style="font-size:var(--text-sm);">Featured
                                    Image</label>
                                <div class="d-flex gap-2 align-items-start">
                                    <input type="file" name="featured_image" class="form-control form-control-sm"
                                        accept="image/*" style="font-size:11px;">
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 mt-0"
                                        style="font-size:10px;" data-gallery-target="featured_image"
                                        data-gallery-preview="featuredImagePreview" data-gallery-hidden="featuredImageHidden"><i
                                            class="fas fa-images me-1"></i>Gallery</button>
                                    <input type="hidden" name="featured_image_selected" id="featuredImageHidden" data-gallery-field="featured_image">
                                </div>
                                <img id="featuredImagePreview" src="" style="height:40px;margin-top:4px;border-radius:4px;display:none;">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold mb-1"
                                    style="font-size:var(--text-sm);">Excerpt</label>
                                <input type="text" name="excerpt" class="form-control form-control-sm">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold mb-1"
                                    style="font-size:var(--text-sm);">Content</label>
                                <textarea name="content" class="form-control editor-textarea" rows="4" style="font-size:12px;"></textarea>
                            </div>
                        </div>
                    </form>
                </x-slot:body>
                <x-slot:footer>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="addBlockForm" class="btn btn-primary btn-sm"><i
                            class="fas fa-save me-1"></i>Create</button>
                </x-slot:footer>
            </x-modal>
        @endif

        {{-- ═══════════════ TAB: MEDIA MANAGER ═══════════════ --}}
        @if ($activeTab === 'media')
            <div class="row g-3">

                {{-- ─── MAIN MEDIA GALLERY ─── --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div
                            class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                            <span class="fw-semibold small"><i class="fas fa-images me-1 text-primary"></i>Media Gallery</span>
                            <div class="d-flex gap-2 align-items-center">
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 d-none" id="deleteSelectedBtn"
                                    style="font-size:11px;"><i class="fas fa-trash me-1"></i>Delete <span id="selectedCount">0</span></button>
                                <input type="file" id="mediaUploadInput" class="form-control form-control-sm"
                                    style="width:180px;font-size:11px;" accept="image/*">
                                <button type="button" class="btn btn-primary btn-sm py-0" id="mediaUploadBtn" disabled
                                    style="font-size:11px;"><i class="fas fa-upload me-1"></i>Upload</button>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div id="mediaProgress" class="d-none mb-2">
                                <div class="progress" style="height:3px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:100%">
                                    </div>
                                </div>
                            </div>
                            <div id="mediaError" class="text-danger small d-none mb-2"></div>
                            @php $allMedia = $uploadedImages; @endphp
                            @if (count($allMedia))
                                <div class="row g-2" id="mediaGrid">
                                    @foreach ($allMedia as $img)
                                        <div class="col-3 col-md-2 col-lg-2">
                                            <div class="card border-0 shadow-sm media-item position-relative"
                                                data-url="{{ $img['url'] }}" data-path="{{ $img['path'] }}"
                                                title="Click to preview">
                                                <div class="media-checkbox-wrapper position-absolute"
                                                    style="top:2px;left:2px;z-index:5;">
                                                    <input type="checkbox" class="media-select form-check-input"
                                                        style="width:14px;height:14px;" value="{{ $img['path'] }}">
                                                </div>
                                                <button type="button"
                                                    class="btn btn-sm btn-danger position-absolute media-delete-btn"
                                                    style="top:2px;right:2px;z-index:5;padding:0 4px;font-size:9px;line-height:1.2;border-radius:3px;display:none;"
                                                    data-path="{{ $img['path'] }}"
                                                    title="Delete this image"><i class="fas fa-times"></i></button>
                                                <div style="position:relative;">
                                                    <img src="{{ $img['url'] }}" alt="{{ $img['filename'] }}"
                                                        loading="lazy"
                                                        style="width:100%;height:70px;object-fit:cover;border-radius:4px 4px 0 0;"
                                                        onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                                    <div
                                                        style="display:none;height:70px;align-items:center;justify-content:center;background:#f1f5f9;border-radius:4px 4px 0 0;">
                                                        <span class="text-muted" style="font-size:10px;">No preview</span>
                                                    </div>
                                                </div>
                                                <div class="p-1 text-center">
                                                    <span class="small text-muted text-truncate d-block"
                                                        style="font-size:9px;">{{ Str::limit($img['filename'], 14) }}</span>
                                                    <span class="badge bg-light text-muted"
                                                        style="font-size:7px;">{{ $img['dir'] ?? '' }}</span>
                                                    <div class="mt-1 d-flex gap-1 justify-content-center">
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-secondary media-copy-path"
                                                            style="font-size:8px;padding:0 4px;line-height:1.5;"
                                                            data-path="{{ $img['path'] }}"
                                                            title="Copy path"><i class="fas fa-link"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                {{-- Lightbox --}}
                                <div class="modal fade" id="mediaLightbox" tabindex="-1">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content border-0 shadow" style="background:transparent;">
                                            <div class="modal-body text-center p-0 position-relative">
                                                <button type="button"
                                                    class="btn btn-sm btn-dark position-absolute top-0 end-0 m-2"
                                                    data-bs-dismiss="modal"
                                                    style="z-index:10;opacity:0.8;border-radius:50%;width:32px;height:32px;"><i
                                                        class="fas fa-times"></i></button>
                                                <img id="lightboxImg" src="" alt=""
                                                    style="max-width:100%;max-height:80vh;border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,0.3);">
                                            </div>
                                            <div class="text-center mt-2 d-flex justify-content-center gap-2 align-items-center">
                                                <code id="lightboxPath" class="small text-white bg-dark px-2 py-1 rounded"
                                                    style="font-size:11px;cursor:pointer;" title="Click to copy"></code>
                                                <button type="button" class="btn btn-sm btn-outline-light py-0" id="lightboxCopyBtn"
                                                    style="font-size:10px;"><i class="fas fa-copy me-1"></i>Copy</button>
                                                <button type="button" class="btn btn-sm btn-danger py-0" id="lightboxDeleteBtn"
                                                    style="font-size:10px;"><i class="fas fa-trash me-1"></i>Delete</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-5"><i class="fas fa-image fa-3x text-muted mb-3 opacity-50"></i>
                                    <p class="text-muted small">No media uploaded yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ─── UNIVERSITY LOGOS ─── --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div
                            class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                            <span class="fw-semibold small"><i class="fas fa-university me-1 text-warning"></i>University Logos</span>
                            <input type="file" id="uniLogoUploadInput" class="form-control form-control-sm"
                                style="width:160px;font-size:11px;" accept="image/*">
                        </div>
                        <div class="card-body p-3">
                            <div id="uniLogoError" class="text-danger small d-none mb-2"></div>
                            @if (count($uniLogos))
                                <div class="row g-2" id="uniLogoGrid">
                                    @foreach ($uniLogos as $logo)
                                        <div class="col-6">
                                            <div class="card border-0 shadow-sm position-relative">
                                                <button type="button"
                                                    class="btn btn-sm btn-danger position-absolute uni-logo-delete-btn"
                                                    style="top:2px;right:2px;z-index:5;padding:0 4px;font-size:9px;line-height:1.2;border-radius:3px;"
                                                    data-path="{{ $logo['path'] }}"
                                                    title="Delete this logo"><i class="fas fa-times"></i></button>
                                                <div class="p-2 text-center">
                                                    <img src="{{ $logo['url'] }}" alt="{{ $logo['filename'] }}"
                                                        loading="lazy"
                                                        style="max-width:100%;height:50px;object-fit:contain;border-radius:4px;">
                                                    <span class="small text-muted text-truncate d-block mt-1"
                                                        style="font-size:8px;">{{ $logo['filename'] }}</span>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-secondary media-copy-path mt-1"
                                                        style="font-size:8px;padding:0 4px;line-height:1.5;"
                                                        data-path="{{ $logo['path'] }}"
                                                        title="Copy path"><i class="fas fa-link"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-university fa-2x text-muted mb-2 opacity-50"></i>
                                    <p class="text-muted small">No university logos uploaded.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ═══════════════ IMAGE PICKER MODAL (shared across all tabs) ═══════════════ --}}
        <div class="modal fade" id="imagePickerModal" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-white py-2 px-3 border-bottom">
                        <span class="fw-semibold small"><i class="fas fa-images me-1 text-primary"></i>Select Image</span>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-3" style="max-height:70vh;overflow-y:auto;">
                        <div class="row g-2" id="pickerGrid">
                            @php $pickerMedia = $uploadedImages; @endphp
                            @forelse($pickerMedia as $img)
                                <div class="col-3 col-md-2 col-lg-2 picker-item" style="cursor:pointer;"
                                    data-url="{{ $img['url'] }}" data-path="{{ $img['path'] }}">
                                    <div class="card border-0 shadow-sm picker-thumb"
                                        style="outline:2px solid transparent;transition:outline .15s;">
                                        <img src="{{ $img['url'] }}" alt="" loading="lazy"
                                            style="width:100%;height:60px;object-fit:cover;border-radius:4px 4px 0 0;">
                                        <div class="p-1 text-center">
                                            <span class="small text-muted text-truncate d-block" style="font-size:8px;">{{ $img['filename'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-4">
                                    <p class="text-muted small">No media available. Upload images in the Media Manager tab first.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-3">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-sm btn-primary" id="pickerSelectBtn" disabled><i class="fas fa-check me-1"></i>Select</button>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const csrf = '{{ csrf_token() }}';
                    let pickerCallback = null;

                    // ─── Image Picker Logic ───
                    const pickerModal = document.getElementById('imagePickerModal');
                    const pickerGrid = document.getElementById('pickerGrid');
                    const pickerSelectBtn = document.getElementById('pickerSelectBtn');
                    let selectedPickerItem = null;

                    pickerGrid.addEventListener('click', function(e) {
                        const item = e.target.closest('.picker-item');
                        if (!item) return;
                        if (selectedPickerItem) {
                            selectedPickerItem.querySelector('.picker-thumb').style.outline = '2px solid transparent';
                        }
                        selectedPickerItem = item;
                        item.querySelector('.picker-thumb').style.outline = '2px solid #1a0262';
                        pickerSelectBtn.disabled = false;
                    });

                    pickerSelectBtn.addEventListener('click', function() {
                        if (selectedPickerItem && pickerCallback) {
                            pickerCallback({
                                url: selectedPickerItem.dataset.url,
                                path: selectedPickerItem.dataset.path
                            });
                        }
                        const modal = bootstrap.Modal.getInstance(pickerModal);
                        if (modal) modal.hide();
                        if (selectedPickerItem) {
                            selectedPickerItem.querySelector('.picker-thumb').style.outline = '2px solid transparent';
                            selectedPickerItem = null;
                        }
                        pickerSelectBtn.disabled = true;
                    });

                    document.getElementById('imagePickerModal').addEventListener('hidden.bs.modal', function() {
                        if (selectedPickerItem) {
                            selectedPickerItem.querySelector('.picker-thumb').style.outline = '2px solid transparent';
                            selectedPickerItem = null;
                        }
                        pickerSelectBtn.disabled = true;
                    });

                    // ─── Helper to open picker ───
                    window.openImagePicker = function(callback) {
                        pickerCallback = callback;
                        const modal = new bootstrap.Modal(document.getElementById('imagePickerModal'));
                        modal.show();
                    };

                    // ─── Attach Browse Gallery buttons ───
                    document.querySelectorAll('[data-gallery-target]').forEach(btn => {
                        const targetName = btn.dataset.galleryTarget;
                        const previewId = btn.dataset.galleryPreview;
                        const hiddenId = btn.dataset.galleryHidden;
                        btn.addEventListener('click', function() {
                            openImagePicker(function(selection) {
                                const preview = document.getElementById(previewId);
                                const hidden = document.getElementById(hiddenId);
                                if (preview) {
                                    preview.src = selection.url;
                                    preview.style.display = 'inline';
                                }
                                if (hidden) hidden.value = selection.path;
                            });
                        });
                    });

                    // ─── On form submit, copy gallery-selected values as original field names ───
                    document.querySelectorAll('form').forEach(form => {
                        form.addEventListener('submit', function() {
                            this.querySelectorAll('input[type="hidden"][data-gallery-field]').forEach(hidden => {
                                const fieldName = hidden.dataset.galleryField;
                                if (hidden.value) {
                                    const existing = this.querySelector(`input[type="hidden"][name="${fieldName}"], input[type="text"][name="${fieldName}"]`);
                                    if (!existing || !existing.value) {
                                        const clone = document.createElement('input');
                                        clone.type = 'hidden';
                                        clone.name = fieldName;
                                        clone.value = hidden.value;
                                        this.appendChild(clone);
                                    }
                                }
                            });
                        });
                    });

                        // ─── Media Upload ───
                        const uploadInput = document.getElementById('mediaUploadInput');
                        const uploadBtn = document.getElementById('mediaUploadBtn');
                        const progress = document.getElementById('mediaProgress');
                        const errorEl = document.getElementById('mediaError');
                        const grid = document.getElementById('mediaGrid');

                        uploadInput.addEventListener('change', function() {
                            uploadBtn.disabled = !this.files.length;
                        });

                        uploadBtn.addEventListener('click', function() {
                            const file = uploadInput.files[0];
                            if (!file) return;
                            progress.classList.remove('d-none');
                            errorEl.classList.add('d-none');
                            uploadBtn.disabled = true;
                            const fd = new FormData();
                            fd.append('image', file);
                            fetch('{{ route('admin.content.media.upload') }}', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': csrf },
                                body: fd
                            }).then(r => r.json()).then(data => {
                                if (data.success) {
                                    const col = document.createElement('div');
                                    col.className = 'col-3 col-md-2 col-lg-2';
                                    col.innerHTML = `<div class="card border-0 shadow-sm media-item position-relative" data-url="${data.url}" data-path="${data.path}" title="Click to preview">
                                        <div class="media-checkbox-wrapper position-absolute" style="top:2px;left:2px;z-index:5;">
                                            <input type="checkbox" class="media-select form-check-input" style="width:14px;height:14px;" value="${data.path}">
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger position-absolute media-delete-btn" style="top:2px;right:2px;z-index:5;padding:0 4px;font-size:9px;line-height:1.2;border-radius:3px;display:none;" data-path="${data.path}" title="Delete this image"><i class="fas fa-times"></i></button>
                                        <div style="position:relative;">
                                            <img src="${data.url}" alt="${data.filename}" loading="lazy" style="width:100%;height:70px;object-fit:cover;border-radius:4px 4px 0 0;">
                                        </div>
                                        <div class="p-1 text-center">
                                            <span class="small text-muted text-truncate d-block" style="font-size:9px;">${data.filename.substring(0,14)}</span>
                                            <span class="badge bg-light text-muted" style="font-size:7px;">media</span>
                                        </div>
                                    </div>`;
                                    grid.insertBefore(col, grid.firstChild);
                                    uploadInput.value = '';
                                }
                            }).catch(() => {
                                errorEl.textContent = 'Upload failed.';
                                errorEl.classList.remove('d-none');
                            }).finally(() => {
                                progress.classList.add('d-none');
                                uploadBtn.disabled = false;
                            });
                        });

                        // ─── Lightbox ───
                        const lightbox = new bootstrap.Modal(document.getElementById('mediaLightbox'));
                        const lightboxImg = document.getElementById('lightboxImg');
                        const lightboxPath = document.getElementById('lightboxPath');
                        const lightboxDeleteBtn = document.getElementById('lightboxDeleteBtn');
                        let currentLightboxPath = '';

                        grid.addEventListener('click', function(e) {
                            const item = e.target.closest('.media-item');
                            if (!item) return;
                            if (e.target.closest('.media-checkbox-wrapper') || e.target.closest('.media-delete-btn')) return;
                            lightboxImg.src = item.dataset.url;
                            lightboxPath.textContent = item.dataset.path;
                            currentLightboxPath = item.dataset.path;
                            lightbox.show();
                        });

                        lightboxPath.addEventListener('click', function() {
                            navigator.clipboard.writeText(this.textContent).catch(() => {});
                            const orig = this.innerHTML;
                            this.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
                            setTimeout(() => { this.innerHTML = orig; }, 1500);
                        });

                        const lightboxCopyBtn = document.getElementById('lightboxCopyBtn');
                        if (lightboxCopyBtn) {
                            lightboxCopyBtn.addEventListener('click', function() {
                                const path = lightboxPath.textContent;
                                if (!path) return;
                                navigator.clipboard.writeText(path).catch(() => {});
                                const orig = this.innerHTML;
                                this.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
                                setTimeout(() => { this.innerHTML = orig; }, 1500);
                            });
                        }

                        grid.addEventListener('click', function(e) {
                            const btn = e.target.closest('.media-copy-path');
                            if (!btn) return;
                            e.stopPropagation();
                            const path = btn.dataset.path;
                            navigator.clipboard.writeText(path).catch(() => {});
                            const orig = btn.innerHTML;
                            btn.innerHTML = '<i class="fas fa-check"></i>';
                            setTimeout(() => { btn.innerHTML = orig; }, 1500);
                        });

                        lightboxDeleteBtn.addEventListener('click', function() {
                            if (!currentLightboxPath) return;
                            if (!confirm('Delete this image?')) return;
                            fetch('{{ route('admin.content.media.delete') }}', {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
                                body: JSON.stringify({ path: currentLightboxPath })
                            }).then(r => r.json()).then(data => {
                                if (data.success) {
                                    const items = grid.querySelectorAll('.media-item');
                                    items.forEach(item => {
                                        if (item.dataset.path === currentLightboxPath) {
                                            item.closest('.col-3, .col-2, .col-lg-2').remove();
                                        }
                                    });
                                    lightbox.hide();
                                }
                            });
                        });

                        // ─── Hover delete button on media items ───
                        grid.addEventListener('mouseenter', function(e) {
                            const item = e.target.closest('.media-item');
                            if (item) item.querySelector('.media-delete-btn').style.display = '';
                        }, true);
                        grid.addEventListener('mouseleave', function(e) {
                            const item = e.target.closest('.media-item');
                            if (item) item.querySelector('.media-delete-btn').style.display = 'none';
                        }, true);

                        // Single delete via hover button
                        grid.addEventListener('click', function(e) {
                            const btn = e.target.closest('.media-delete-btn');
                            if (!btn) return;
                            e.stopPropagation();
                            if (!confirm('Delete ' + btn.dataset.path + '?')) return;
                            fetch('{{ route('admin.content.media.delete') }}', {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
                                body: JSON.stringify({ path: btn.dataset.path })
                            }).then(r => r.json()).then(data => {
                                if (data.success) btn.closest('.col-3, .col-2, .col-lg-2').remove();
                            });
                        });

                        // ─── Bulk Delete ───
                        const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
                        const selectedCount = document.getElementById('selectedCount');

                        grid.addEventListener('change', function(e) {
                            if (e.target.classList.contains('media-select')) {
                                const checked = grid.querySelectorAll('.media-select:checked').length;
                                if (checked > 0) {
                                    deleteSelectedBtn.classList.remove('d-none');
                                    selectedCount.textContent = checked;
                                } else {
                                    deleteSelectedBtn.classList.add('d-none');
                                }
                            }
                        });

                        deleteSelectedBtn.addEventListener('click', function() {
                            const checked = grid.querySelectorAll('.media-select:checked');
                            if (!checked.length) return;
                            if (!confirm('Delete ' + checked.length + ' selected image(s)?')) return;
                            const paths = Array.from(checked).map(cb => cb.value);
                            fetch('{{ route('admin.content.media.delete-bulk') }}', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
                                body: JSON.stringify({ paths })
                            }).then(r => r.json()).then(data => {
                                if (data.success) {
                                    checked.forEach(cb => cb.closest('.col-3, .col-2, .col-lg-2').remove());
                                    deleteSelectedBtn.classList.add('d-none');
                                }
                            });
                        });

                        // ─── uni_logo upload ───
                        const uniLogoInput = document.getElementById('uniLogoUploadInput');
                        const uniLogoGrid = document.getElementById('uniLogoGrid');
                        const uniLogoError = document.getElementById('uniLogoError');

                        uniLogoInput.addEventListener('change', function() {
                            if (!this.files.length) return;
                            uniLogoError.classList.add('d-none');
                            const fd = new FormData();
                            fd.append('image', this.files[0]);
                            fd.append('dir', 'uni_logo');
                            fetch('{{ route('admin.content.media.upload') }}', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': csrf },
                                body: fd
                            }).then(r => r.json()).then(data => {
                                if (data.success) {
                                    location.reload();
                                } else {
                                    uniLogoError.textContent = 'Upload failed.';
                                    uniLogoError.classList.remove('d-none');
                                }
                            }).catch(() => {
                                uniLogoError.textContent = 'Upload failed.';
                                uniLogoError.classList.remove('d-none');
                            });
                        });

                        // uni_logo delete
                        document.querySelectorAll('.uni-logo-delete-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                if (!confirm('Delete this logo?')) return;
                                fetch('{{ route('admin.content.media.delete') }}', {
                                    method: 'DELETE',
                                    headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ path: this.dataset.path })
                                }).then(r => r.json()).then(data => {
                                    if (data.success) this.closest('.col-6').remove();
                                });
                            });
                        });
                    });
                </script>
            @endpush
    </div>
@endsection
