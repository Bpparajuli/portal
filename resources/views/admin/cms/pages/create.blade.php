@extends('layouts.admin')
@section('admin-content')
<div class="container-fluid p-4">
    <x-page-header title="Create Page">
        <x-slot:actions>
            <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </x-slot:actions>
    </x-page-header>

    <form action="{{ route('admin.pages.store') }}" method="POST" id="pageForm">
        @csrf
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
                        <textarea name="content" class="form-control font-monospace" rows="8" placeholder="Or write HTML directly..." id="rawContent"></textarea>
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
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Slug</label>
                            <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="auto-generated">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Status</label>
                            <select name="status" class="form-select">
                                <option value="draft" selected>Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Template</label>
                            <select name="template" class="form-select">
                                <option value="default">Default</option>
                                <option value="full-width">Full Width</option>
                                <option value="landing">Landing Page</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_published" class="form-check-input" id="isPublished" value="1" checked>
                                <label class="form-check-label fw-semibold small" for="isPublished">Published</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_menu_item" class="form-check-input" id="isMenuItem" value="1">
                                <label class="form-check-label fw-semibold small" for="isMenuItem">Show in Menu</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Menu Order</label>
                            <input type="number" name="menu_order" class="form-control" value="0" min="0">
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
                            <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Meta Description</label>
                            <textarea name="meta_description" class="form-control" rows="3">{{ old('meta_description') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create Page</button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
let blockId = 0;
function getBlocks() { return document.querySelectorAll('.block-item'); }

function addBlock(type) {
    type = type || document.getElementById('blockType').value;
    const container = document.getElementById('blocksContainer');
    document.getElementById('emptyBlocks')?.remove();
    const id = ++blockId;
    const div = document.createElement('div');
    div.className = 'block-item mb-3 border rounded p-3 bg-white position-relative';
    div.dataset.blockId = id; div.dataset.blockType = type;
    let html = '';
    if (type === 'heading') html = `<div class="d-flex gap-2 mb-2"><select class="form-select form-select-sm" style="width:auto;" onchange="updateRawContent()"><option value="h1">H1</option><option value="h2" selected>H2</option><option value="h3">H3</option><option value="h4">H4</option></select><input type="text" class="form-control form-control-sm" placeholder="Heading text..." oninput="updateRawContent()"></div>`;
    else if (type === 'paragraph') html = `<textarea class="form-control form-control-sm" rows="3" placeholder="Paragraph text..." oninput="updateRawContent()"></textarea>`;
    else if (type === 'image') html = `<div class="d-flex gap-2"><input type="text" class="form-control form-control-sm" placeholder="Image URL..." oninput="updateRawContent()"><input type="text" class="form-control form-control-sm" placeholder="Alt text..." style="width:160px;" oninput="updateRawContent()"></div>`;
    else if (type === 'button') html = `<div class="d-flex gap-2"><input type="text" class="form-control form-control-sm" placeholder="Button text..." oninput="updateRawContent()"><input type="text" class="form-control form-control-sm" placeholder="URL..." oninput="updateRawContent()"></div>`;
    else if (type === 'divider') html = `<div class="text-muted small text-center py-2">--- Divider ---</div>`;
    else if (type === 'columns') html = `<div class="row g-3"><div class="col-md-6"><textarea class="form-control form-control-sm" rows="3" placeholder="Left column..." oninput="updateRawContent()"></textarea></div><div class="col-md-6"><textarea class="form-control form-control-sm" rows="3" placeholder="Right column..." oninput="updateRawContent()"></textarea></div></div>`;
    else html = `<textarea class="form-control form-control-sm font-monospace" rows="4" placeholder="Custom HTML..." oninput="updateRawContent()"></textarea>`;
    div.innerHTML = `<div class="d-flex align-items-center justify-content-between mb-2"><span class="badge bg-light text-muted text-capitalize">${type}</span><div class="d-flex gap-1"><button type="button" class="btn btn-sm btn-link text-muted p-0 px-1" onclick="moveBlock(this,-1)" title="Up"><i class="fas fa-chevron-up"></i></button><button type="button" class="btn btn-sm btn-link text-muted p-0 px-1" onclick="moveBlock(this,1)" title="Down"><i class="fas fa-chevron-down"></i></button><button type="button" class="btn btn-sm btn-link text-danger p-0 px-1" onclick="removeBlock(this)" title="Remove"><i class="fas fa-times"></i></button></div></div><div class="block-content">${html}</div>`;
    container.appendChild(div); updateRawContent();
}

function removeBlock(btn) { btn.closest('.block-item').remove(); updateRawContent(); if(!getBlocks().length) document.getElementById('emptyBlocks')?.classList.remove('d-none'); }
function moveBlock(btn, dir) { const b=btn.closest('.block-item'); const p=b.parentNode; const s=dir===-1?b.previousElementSibling:b.nextElementSibling; if(s&&s.classList.contains('block-item')){if(dir===-1)p.insertBefore(b,s);else p.insertBefore(s,b);updateRawContent();} }

function renderBlock(block) {
    const t=block.dataset.blockType; const v=Array.from(block.querySelectorAll('input,textarea,select')).map(i=>i.value);
    if(t==='heading'){const tag=v[0]||'h2';return `<${tag}>${v[1]||'Heading'}</${tag}>`;}
    if(t==='paragraph') return `<p>${v[0]||''}</p>`;
    if(t==='image') return v[0]?`<img src="${v[0]}" alt="${v[1]||''}" class="img-fluid">`:'';
    if(t==='button') return `<a href="${v[1]||'#'}" class="btn btn-primary">${v[0]||'Button'}</a>`;
    if(t==='divider') return '<hr>';
    if(t==='columns') return `<div class="row"><div class="col-md-6">${v[0]||''}</div><div class="col-md-6">${v[1]||''}</div></div>`;
    return v[0]||'';
}

function updateRawContent() {
    let html=''; getBlocks().forEach(b=>{html+=renderBlock(b)+'\n';});
    document.getElementById('rawContent').value=html.trim();
}
</script>
@endpush
@endsection