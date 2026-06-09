@extends('layouts.admin')
@section('admin-content')
<div class="container-fluid px-4 py-4">
    <x-page-header title="Pages &amp; Content Management" subtitle="Manage all web pages, content settings, and dynamic sections from one place">
        <x-slot:actions>
            <a href="{{ route('admin.pages.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Page</a>
        </x-slot:actions>
    </x-page-header>

    <ul class="nav nav-tabs mb-4 border-0" id="pcTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold px-4" id="pages-tab" data-bs-toggle="tab" data-bs-target="#pages" type="button" role="tab">
                <i class="fas fa-file-alt me-2"></i>Pages <span class="badge bg-primary bg-opacity-10 text-primary ms-1 rounded-pill">{{ $pages->total() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold px-4" id="content-tab" data-bs-toggle="tab" data-bs-target="#content" type="button" role="tab">
                <i class="fas fa-palette me-2"></i>Content Settings <span class="badge bg-primary bg-opacity-10 text-primary ms-1 rounded-pill">{{ $contentSettings->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold px-4" id="dynamic-tab" data-bs-toggle="tab" data-bs-target="#dynamic" type="button" role="tab">
                <i class="fas fa-bolt me-2"></i>Dynamic Content
            </button>
        </li>
    </ul>

    <div class="tab-content" id="pcTabContent">
        {{-- ==================== TAB 1: PAGES ==================== --}}
        <div class="tab-pane fade show active" id="pages" role="tabpanel">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 dense-student-table">
                            <thead>
                                <tr>
                                    <th style="width:40px;">ID</th>
                                    <th>Title</th>
                                    <th style="min-width:100px;">Slug</th>
                                    <th style="width:90px;">Status</th>
                                    <th style="width:60px;">Menu</th>
                                    <th style="width:100px;">Updated</th>
                                    <th class="text-end" style="width:120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pages as $page)
                                <tr>
                                    <td><span class="fw-bold text-muted small">#{{ $page->id }}</span></td>
                                    <td class="fw-semibold">{{ $page->title }}</td>
                                    <td><code class="small">{{ $page->slug }}</code></td>
                                    <td>
                                        @php $sc = ['published'=>'success','draft'=>'warning','archived'=>'secondary']; @endphp
                                        <span class="badge bg-{{ $sc[$page->status ?? 'draft'] }} bg-opacity-10 text-{{ $sc[$page->status ?? 'draft'] }} rounded-pill small">{{ $page->status ?? 'draft' }}</span>
                                    </td>
                                    <td>
                                        @if($page->is_menu_item)
                                            <span class="badge bg-info bg-opacity-10 text-info rounded-pill small">{{ $page->menu_order }}</span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td><span class="small text-muted">{{ $page->updated_at->format('d M Y') }}</span></td>
                                    <td class="text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <button type="button" class="btn btn-sm btn-ghost" onclick="viewPage({{ $page->id }})" title="Preview"><i class="fas fa-eye text-info"></i></button>
                                            <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-ghost" title="Edit"><i class="fas fa-edit text-primary"></i></a>
                                            <x-confirm-delete
                                                action="admin.pages.destroy"
                                                :id="$page->id"
                                                label=""
                                                title="Delete Page?"
                                                message="This will permanently delete this page."
                                                mode="form"
                                                class="btn btn-sm btn-ghost"
                                            />
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-file-alt fa-3x mb-3 d-block"></i>No pages yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($pages->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-center py-3">{{ $pages->links('pagination::bootstrap-5') }}</div>
                @endif
            </div>
        </div>

        {{-- ==================== TAB 2: CONTENT SETTINGS ==================== --}}
        <div class="tab-pane fade" id="content" role="tabpanel">
            @if(session('content_success'))
                <div class="alert alert-success alert-dismissible fade show">{{ session('content_success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    @forelse($contentSettings as $setting)
                    <div class="d-flex align-items-center gap-3 p-3 border-bottom border-light hover-bg transition" style="cursor:pointer;" onclick="openContentModal({{ $setting->id }})">
                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="fw-semibold small">{{ Str::title(str_replace('_', ' ', $setting->key)) }}</span>
                                <span class="badge bg-light text-muted rounded-pill" style="font-size:9px;">{{ $setting->key }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if($setting->type === 'boolean')
                                    <span class="small {{ filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) ? 'text-success fw-bold' : 'text-muted' }}">{{ filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) ? 'Enabled' : 'Disabled' }}</span>
                                @elseif($setting->type === 'image' && $setting->value)
                                    <img src="{{ $setting->image_url ?? asset($setting->value) }}" style="width:32px;height:32px;object-fit:cover;border-radius:4px;">
                                    <span class="small text-muted text-truncate">{{ $setting->value }}</span>
                                @elseif($setting->type === 'color' && $setting->value)
                                    <span class="rounded-circle d-inline-block border" style="width:20px;height:20px;background:{{ $setting->value }};"></span>
                                    <span class="small text-muted">{{ $setting->value }}</span>
                                @elseif($setting->type === 'json')
                                    <span class="small text-muted"><i class="fas fa-database me-1"></i>Structured data ({{ count(json_decode($setting->value ?: '[]', true)) }} items)</span>
                                @else
                                    <span class="small text-muted text-truncate d-block" style="max-width:500px;">{{ $setting->value ?: '(empty)' }}</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <span class="badge rounded-pill small fw-normal" style="font-size:9px;
                                {{ $setting->type === 'image' ? 'background:#e8f5e9;color:#2e7d32;' : '' }}
                                {{ $setting->type === 'boolean' ? 'background:#e3f2fd;color:#1565c0;' : '' }}
                                {{ $setting->type === 'json' ? 'background:#fce4ec;color:#c62828;' : '' }}
                                {{ $setting->type === 'number' ? 'background:#fff3e0;color:#e65100;' : '' }}
                                {{ in_array($setting->type, ['string','text']) ? 'background:#f3e5f5;color:#6a1b9a;' : '' }}
                                {{ $setting->type === 'color' ? 'background:#e0f7fa;color:#006064;' : '' }}">
                                {{ $setting->type }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted"><i class="fas fa-cogs fa-3x mb-3 d-block"></i>No content settings configured.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ==================== TAB 3: DYNAMIC CONTENT ==================== --}}
        <div class="tab-pane fade" id="dynamic" role="tabpanel">
            @if(session('dynamic_success'))
                <div class="alert alert-success alert-dismissible fade show">{{ session('dynamic_success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3"><i class="fas fa-hand-wave text-primary me-2"></i>Welcome Text</h6>
                            <form method="POST" action="{{ route('admin.pages.dynamic.update') }}">
                                @csrf
                                <input type="hidden" name="section" value="welcome">
                                <div class="mb-3">
                                    <textarea name="value" class="form-control" rows="3" placeholder="Welcome text for the guest dashboard">{{ $welcomeText }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i>Save</button>
                            </form>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0"><i class="fas fa-calendar-alt text-success me-2"></i>Upcoming Programs</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#programModal"><i class="fas fa-plus me-1"></i>Add Program</button>
                        </div>
                        <div class="card-body p-4">
                            @if(count($programs))
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 dense-student-table">
                                    <thead><tr><th>Title</th><th>Date</th><th>Description</th><th class="text-end" style="width:80px;">Actions</th></tr></thead>
                                    <tbody>
                                        @foreach($programs as $i => $p)
                                        <tr>
                                            <td class="fw-semibold small">{{ $p['title'] ?? '' }}</td>
                                            <td class="small text-muted">{{ $p['date'] ?? '' }}</td>
                                            <td><span class="small text-muted text-truncate d-block" style="max-width:300px;">{{ $p['description'] ?? '' }}</span></td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-ghost edit-program-btn py-0" data-index="{{ $i }}" data-title="{{ $p['title'] ?? '' }}" data-description="{{ $p['description'] ?? '' }}" data-date="{{ $p['date'] ?? '' }}" data-link="{{ $p['link'] ?? '' }}"><i class="fas fa-edit text-primary"></i></button>
                                                <button class="btn btn-sm btn-ghost py-0 delete-program-btn" data-index="{{ $i }}"><i class="fas fa-trash-alt text-danger"></i></button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <p class="text-muted small mb-0">No upcoming programs configured.</p>
                            @endif
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0"><i class="fas fa-running text-warning me-2"></i>Activities / Events</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#activityModal"><i class="fas fa-plus me-1"></i>Add Activity</button>
                        </div>
                        <div class="card-body p-4">
                            @if(count($activities))
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 dense-student-table">
                                    <thead><tr><th>Title</th><th>Description</th><th class="text-end" style="width:80px;">Actions</th></tr></thead>
                                    <tbody>
                                        @foreach($activities as $i => $a)
                                        <tr>
                                            <td class="fw-semibold small">{{ $a['title'] ?? '' }}</td>
                                            <td><span class="small text-muted text-truncate d-block" style="max-width:400px;">{{ $a['description'] ?? '' }}</span></td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-ghost py-0 edit-activity-btn" data-index="{{ $i }}" data-title="{{ $a['title'] ?? '' }}" data-description="{{ $a['description'] ?? '' }}"><i class="fas fa-edit text-primary"></i></button>
                                                <button class="btn btn-sm btn-ghost py-0 delete-activity-btn" data-index="{{ $i }}"><i class="fas fa-trash-alt text-danger"></i></button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <p class="text-muted small mb-0">No activities configured.</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3"><i class="fas fa-info-circle text-info me-2"></i>About Dynamic Content</h6>
                            <p class="small text-muted mb-2">This section manages dynamic content displayed on the public-facing sections of the website.</p>
                            <ul class="small text-muted mb-0 ps-3">
                                <li class="mb-1">Changes take effect immediately.</li>
                                <li class="mb-1">Use plain text or basic HTML for formatting.</li>
                                <li>Links should include the full URL with <code>https://</code>.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- View Page Modal --}}
<div class="modal fade" id="pageViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalTitle">Page Preview</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="modalBody"></div>
        </div>
    </div>
</div>

{{-- Edit Content Setting Modals --}}
@foreach($contentSettings as $setting)
<div class="modal fade" id="contentModal{{ $setting->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.settings.update', $setting) }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-header"><h6 class="modal-title fw-bold"><i class="fas fa-edit me-2 text-primary"></i>{{ Str::title(str_replace('_', ' ', $setting->key)) }}</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label small fw-semibold">Key</label><input type="text" class="form-control form-control-sm" value="{{ $setting->key }}" readonly></div>
                    @if($setting->type === 'boolean')
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="value" value="1" {{ filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                            <label class="form-check-label">Enabled</label>
                        </div>
                        <input type="hidden" name="value_hidden" value="0">
                    @elseif($setting->type === 'image')
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Current Image</label>
                            @if($setting->value)
                                <div class="mb-2"><img src="{{ $setting->image_url ?? asset($setting->value) }}" style="max-width:200px;max-height:120px;object-fit:contain;border-radius:8px;border:1px solid var(--border);"></div>
                            @endif
                            <input type="file" name="image_file" class="form-control form-control-sm" accept="image/*">
                            <input type="hidden" name="value" value="{{ $setting->value }}">
                        </div>
                    @elseif($setting->type === 'color')
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Color Value</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" name="value" class="form-control form-control-color" value="{{ $setting->value ?? '#000000' }}" style="width:50px;height:38px;">
                                <input type="text" name="value_text" class="form-control form-control-sm" value="{{ $setting->value ?? '' }}" placeholder="#000000">
                            </div>
                        </div>
                    @elseif($setting->type === 'json')
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">JSON Data</label>
                            <textarea name="value" class="form-control form-control-sm font-monospace" rows="6">{{ $setting->value }}</textarea>
                            <small class="text-muted">Edit the JSON structure directly. Use the Dynamic Content tab for a form-driven interface.</small>
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Value</label>
                            @if($setting->type === 'text' || strlen($setting->value ?? '') > 100)
                                <textarea name="value" class="form-control form-control-sm" rows="4">{{ $setting->value }}</textarea>
                            @else
                                <input type="text" name="value" class="form-control form-control-sm" value="{{ $setting->value }}">
                            @endif
                        </div>
                    @endif
                    @if($setting->description)
                    <small class="text-muted"><i class="fas fa-info-circle me-1"></i>{{ $setting->description }}</small>
                    @endif
                </div>
                <div class="modal-footer">
                    <div class="me-auto">
                        <x-confirm-delete
                            action="admin.settings.destroy"
                            :id="$setting->id"
                            label="Delete"
                            title="Delete Setting?"
                            message="This will permanently delete this setting."
                            mode="form"
                            class="btn btn-sm btn-outline-danger"
                        />
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

{{-- Program Modal --}}
<div class="modal fade" id="programModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.pages.dynamic.update') }}">
                @csrf
                <input type="hidden" name="section" value="programs">
                <input type="hidden" name="index" id="programIndex" value="">
                <div class="modal-header"><h6 class="modal-title fw-bold"><i class="fas fa-calendar-alt me-2 text-success"></i><span id="programModalTitle">Add Program</span></h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label small fw-semibold">Title <span class="text-danger">*</span></label><input type="text" name="title" id="programTitle" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label small fw-semibold">Description</label><textarea name="description" id="programDescription" class="form-control" rows="2"></textarea></div>
                    <div class="mb-3"><label class="form-label small fw-semibold">Date</label><input type="date" name="date" id="programDate" class="form-control"></div>
                    <div class="mb-3"><label class="form-label small fw-semibold">Link</label><input type="url" name="link" id="programLink" class="form-control" placeholder="https://"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success" id="programSubmitBtn"><i class="fas fa-save me-1"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Activity Modal --}}
<div class="modal fade" id="activityModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.pages.dynamic.update') }}">
                @csrf
                <input type="hidden" name="section" value="activities">
                <input type="hidden" name="index" id="activityIndex" value="">
                <div class="modal-header"><h6 class="modal-title fw-bold"><i class="fas fa-running me-2 text-warning"></i><span id="activityModalTitle">Add Activity</span></h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label small fw-semibold">Title <span class="text-danger">*</span></label><input type="text" name="title" id="activityTitle" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label small fw-semibold">Description</label><textarea name="description" id="activityDescription" class="form-control" rows="3"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="activitySubmitBtn"><i class="fas fa-save me-1"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteProgramForm" method="POST" style="display:none">@csrf<input type="hidden" name="section" value="programs"><input type="hidden" name="action" value="delete"><input type="hidden" name="index" id="deleteProgramIndex" value=""></form>
<form id="deleteActivityForm" method="POST" style="display:none">@csrf<input type="hidden" name="section" value="activities"><input type="hidden" name="action" value="delete"><input type="hidden" name="index" id="deleteActivityIndex" value=""></form>

@push('scripts')
<script>
const pages = @json($pages->items());
function viewPage(id) {
    const page = pages.find(p => p.id === id);
    if (!page) return;
    document.getElementById('modalTitle').textContent = page.title;
    document.getElementById('modalBody').innerHTML = `
        <div class="mb-3"><strong>Slug:</strong> <code>${page.slug}</code></div>
        <div class="mb-3"><strong>Status:</strong> <span class="badge bg-${page.status === 'published' ? 'success' : (page.status === 'draft' ? 'warning' : 'secondary')}">${page.status || 'draft'}</span></div>
        <hr>
        <div>${page.content || '<em>No content</em>'}</div>
        ${page.meta_title ? `<hr><div class="small text-muted"><strong>Meta:</strong> ${page.meta_title}${page.meta_description ? ' — ' + page.meta_description : ''}</div>` : ''}
    `;
    new bootstrap.Modal(document.getElementById('pageViewModal')).show();
}

function openContentModal(id) {
    const modal = document.getElementById('contentModal' + id);
    if (modal) new bootstrap.Modal(modal).show();
}

// Program/Activity edit handlers
document.querySelectorAll('.edit-program-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('programIndex').value = this.dataset.index;
        document.getElementById('programTitle').value = this.dataset.title;
        document.getElementById('programDescription').value = this.dataset.description;
        document.getElementById('programDate').value = this.dataset.date;
        document.getElementById('programLink').value = this.dataset.link;
        document.getElementById('programModalTitle').textContent = 'Edit Program';
        document.getElementById('programSubmitBtn').innerHTML = '<i class="fas fa-save me-1"></i>Update';
        new bootstrap.Modal(document.getElementById('programModal')).show();
    });
});
document.querySelectorAll('.edit-activity-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('activityIndex').value = this.dataset.index;
        document.getElementById('activityTitle').value = this.dataset.title;
        document.getElementById('activityDescription').value = this.dataset.description;
        document.getElementById('activityModalTitle').textContent = 'Edit Activity';
        document.getElementById('activitySubmitBtn').innerHTML = '<i class="fas fa-save me-1"></i>Update';
        new bootstrap.Modal(document.getElementById('activityModal')).show();
    });
});
document.querySelectorAll('.delete-program-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!confirm('Delete this program?')) return;
        document.getElementById('deleteProgramIndex').value = this.dataset.index;
        document.getElementById('deleteProgramForm').submit();
    });
});
document.querySelectorAll('.delete-activity-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!confirm('Delete this activity?')) return;
        document.getElementById('deleteActivityIndex').value = this.dataset.index;
        document.getElementById('deleteActivityForm').submit();
    });
});
document.getElementById('programModal')?.addEventListener('hidden.bs.modal', function() {
    document.getElementById('programIndex').value = '';
    document.getElementById('programTitle').value = '';
    document.getElementById('programDescription').value = '';
    document.getElementById('programDate').value = '';
    document.getElementById('programLink').value = '';
    document.getElementById('programModalTitle').textContent = 'Add Program';
    document.getElementById('programSubmitBtn').innerHTML = '<i class="fas fa-save me-1"></i>Save';
});
document.getElementById('activityModal')?.addEventListener('hidden.bs.modal', function() {
    document.getElementById('activityIndex').value = '';
    document.getElementById('activityTitle').value = '';
    document.getElementById('activityDescription').value = '';
    document.getElementById('activityModalTitle').textContent = 'Add Activity';
    document.getElementById('activitySubmitBtn').innerHTML = '<i class="fas fa-save me-1"></i>Save';
});
</script>
@endpush
@endsection
