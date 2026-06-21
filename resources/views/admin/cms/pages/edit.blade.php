@extends('layouts.admin')
@section('admin-content')
<div class="container-fluid p-4">
    <x-page-header title="Edit Page">
        <x-slot:actions>
            <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            <button class="btn btn-outline-info btn-sm" onclick="togglePreview()"><i class="fas fa-eye me-1"></i>Preview</button>
        </x-slot:actions>
    </x-page-header>

    <form action="{{ route('admin.pages.update', $page) }}" method="POST" id="pageForm">
        @csrf @method('PUT')
        <input type="hidden" name="content_blocks" id="contentBlocks" value="{{ old('content', $page->content) }}">

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-edit me-2 text-primary"></i>Page Content</h6>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" id="blockType" style="width:auto;">
                                <option value="heading">Heading</option>
                                <option value="paragraph">Paragraph</option>
                                <option value="image">Image</option>
                                <option value="button">Button</option>
                                <option value="divider">Divider</option>
                                <option value="columns">Columns (2)</option>
                                <option value="html">Custom HTML</option>
                            </select>
                            <button type="button" class="btn btn-primary btn-sm" onclick="addBlock()"><i class="fas fa-plus me-1"></i>Add Block</button>
                        </div>
                    </div>
                    <div class="card-body p-4" id="blocksContainer">
                        <div class="text-center text-muted py-5" id="emptyBlocks">
                            <i class="fas fa-plus-circle fa-3x mb-3 d-block opacity-25"></i>
                            <p>Add blocks to build your page content</p>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-code me-2 text-muted"></i>Raw HTML Content</h6>
                    </div>
                    <div class="card-body p-4">
                        <textarea name="content" class="form-control font-monospace" rows="8" placeholder="Or write HTML directly..." id="rawContent">{{ old('content', $page->content) }}</textarea>
                        <div class="form-text">Blocks above auto-fill here. Edit raw HTML directly or use blocks.</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-cog me-2 text-secondary"></i>Page Settings</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $page->title) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Slug</label>
                            <input type="text" name="slug" class="form-control" value="{{ old('slug', $page->slug) }}" placeholder="auto-generated">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Status</label>
                            <select name="status" class="form-select">
                                <option value="draft" {{ $page->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ $page->status === 'published' ? 'selected' : '' }}>Published</option>
                                <option value="archived" {{ $page->status === 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Template</label>
                            <select name="template" class="form-select">
                                <option value="default" {{ $page->template === 'default' ? 'selected' : '' }}>Default</option>
                                <option value="full-width" {{ $page->template === 'full-width' ? 'selected' : '' }}>Full Width</option>
                                <option value="landing" {{ $page->template === 'landing' ? 'selected' : '' }}>Landing Page</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_published" class="form-check-input" id="isPublished" value="1" {{ $page->is_published ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="isPublished">Published</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_menu_item" class="form-check-input" id="isMenuItem" value="1" {{ $page->is_menu_item ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="isMenuItem">Show in Menu</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Menu Order</label>
                            <input type="number" name="menu_order" class="form-control" value="{{ $page->menu_order ?? 0 }}" min="0">
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-search me-2 text-info"></i>SEO Settings</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Meta Title</label>
                            <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $page->meta_title) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Meta Description</label>
                            <textarea name="meta_description" class="form-control" rows="3">{{ old('meta_description', $page->meta_description) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Page</button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewContent"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let blockId = 0;

function getBlocks() {
    return document.querySelectorAll('.block-item');
}

function addBlock(type) {
    type = type || document.getElementById('blockType').value;
    const container = document.getElementById('blocksContainer');
    document.getElementById('emptyBlocks')?.remove();

    const id = ++blockId;
    const div = document.createElement('div');
    div.className = 'block-item mb-3 border rounded p-3 bg-white position-relative';
    div.dataset.blockId = id;
    div.dataset.blockType = type;

    let contentHtml = '';
    if (type === 'heading') {
        contentHtml = `<div class="d-flex gap-2 mb-2"><select class="form-select form-select-sm" style="width:auto;" onchange="updateBlock(this)"><option value="h1">H1</option><option value="h2" selected>H2</option><option value="h3">H3</option><option value="h4">H4</option></select><input type="text" class="form-control form-control-sm" placeholder="Heading text..." oninput="updateBlock(this)"></div>`;
    } else if (type === 'paragraph') {
        contentHtml = `<textarea class="form-control form-control-sm" rows="3" placeholder="Paragraph text..." oninput="updateBlock(this)"></textarea>`;
    } else if (type === 'image') {
        contentHtml = `<div class="d-flex gap-2"><input type="text" class="form-control form-control-sm" placeholder="Image URL..." oninput="updateBlock(this)"><input type="text" class="form-control form-control-sm" placeholder="Alt text..." style="width:200px;" oninput="updateBlock(this)"><input type="number" class="form-control form-control-sm" placeholder="Width" style="width:80px;" oninput="updateBlock(this)"></div>`;
    } else if (type === 'button') {
        contentHtml = `<div class="d-flex gap-2"><input type="text" class="form-control form-control-sm" placeholder="Button text..." oninput="updateBlock(this)"><input type="text" class="form-control form-control-sm" placeholder="URL..." oninput="updateBlock(this)"><select class="form-select form-select-sm" style="width:auto;" onchange="updateBlock(this)"><option value="btn-primary">Primary</option><option value="btn-outline-primary">Outline</option><option value="btn-success">Success</option></select></div>`;
    } else if (type === 'divider') {
        contentHtml = `<div class="text-muted small text-center py-2">--- Divider ---</div>`;
    } else if (type === 'columns') {
        contentHtml = `<div class="row g-3"><div class="col-md-6"><textarea class="form-control form-control-sm" rows="3" placeholder="Left column..." oninput="updateBlock(this)"></textarea></div><div class="col-md-6"><textarea class="form-control form-control-sm" rows="3" placeholder="Right column..." oninput="updateBlock(this)"></textarea></div></div>`;
    } else {
        contentHtml = `<textarea class="form-control form-control-sm font-monospace" rows="4" placeholder="Custom HTML..." oninput="updateBlock(this)"></textarea>`;
    }

    div.innerHTML = `
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge bg-light text-muted text-capitalize">${type}</span>
            <div class="d-flex gap-1">
                <button type="button" class="btn btn-sm btn-link text-muted p-0 px-1" onclick="moveBlock(this, -1)" title="Move up"><i class="fas fa-chevron-up"></i></button>
                <button type="button" class="btn btn-sm btn-link text-muted p-0 px-1" onclick="moveBlock(this, 1)" title="Move down"><i class="fas fa-chevron-down"></i></button>
                <button type="button" class="btn btn-sm btn-link text-danger p-0 px-1" onclick="removeBlock(this)" title="Remove"><i class="fas fa-times"></i></button>
            </div>
        </div>
        <div class="block-content">${contentHtml}</div>`;

    container.appendChild(div);
    updateRawContent();
}

function removeBlock(btn) {
    btn.closest('.block-item').remove();
    updateRawContent();
    if (!getBlocks().length) document.getElementById('emptyBlocks')?.classList.remove('d-none');
}

function moveBlock(btn, dir) {
    const block = btn.closest('.block-item');
    const parent = block.parentNode;
    const sibling = dir === -1 ? block.previousElementSibling : block.nextElementSibling;
    if (sibling && sibling.classList.contains('block-item')) {
        if (dir === -1) parent.insertBefore(block, sibling);
        else parent.insertBefore(sibling, block);
        updateRawContent();
    }
}

function updateBlock(el) {
    updateRawContent();
}

function renderBlockToHtml(block) {
    const type = block.dataset.blockType;
    const inputs = block.querySelectorAll('.block-content input, .block-content textarea, .block-content select');
    const vals = Array.from(inputs).map(i => i.value);

    if (type === 'heading') {
        const tag = vals[0] || 'h2';
        const text = vals[1] || 'Heading';
        return `<${tag}>${text}</${tag}>`;
    }
    if (type === 'paragraph') return `<p>${vals[0] || ''}</p>`;
    if (type === 'image') {
        const src = vals[0] || '';
        const alt = vals[1] || '';
        const w = vals[2] ? ` width="${vals[2]}"` : '';
        return src ? `<img src="${src}" alt="${alt}"${w} class="img-fluid">` : '';
    }
    if (type === 'button') {
        const text = vals[0] || 'Button';
        const url = vals[1] || '#';
        const cls = vals[2] || 'btn-primary';
        return `<a href="${url}" class="btn ${cls}">${text}</a>`;
    }
    if (type === 'divider') return '<hr>';
    if (type === 'columns') {
        return `<div class="row"><div class="col-md-6">${vals[0] || ''}</div><div class="col-md-6">${vals[1] || ''}</div></div>`;
    }
    return vals[0] || '';
}

function updateRawContent() {
    const blocks = getBlocks();
    let html = '';
    blocks.forEach(b => { html += renderBlockToHtml(b) + '\n'; });
    document.getElementById('rawContent').value = html.trim();
}

function togglePreview() {
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    document.getElementById('previewContent').innerHTML = document.getElementById('rawContent').value;
    modal.show();
}

function loadExistingContent() {
    const raw = document.getElementById('rawContent').value.trim();
    if (!raw) return;
    document.getElementById('rawContent').value = raw;
}

loadExistingContent();
</script>
@endpush
@endsection