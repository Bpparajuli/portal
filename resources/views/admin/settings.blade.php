@extends('layouts.admin')
@section('admin-content')
    <x-page-header title="System Settings">
        <x-slot:actions>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle btn-sm" data-bs-toggle="dropdown">
                        <i class="fas fa-layer-group me-1"></i>{{ request('group') ? ucfirst(request('group')) : 'All Groups' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('admin.settings.index') }}">All Groups</a></li>
                        @foreach($groups ?? [] as $g)
                            <li><a class="dropdown-item" href="{{ route('admin.settings.index', ['group' => $g]) }}">{{ ucfirst($g) }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#imageManagerModal"><i class="fas fa-images me-1"></i>Images</button>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSettingModal"><i class="fas fa-plus me-1"></i>Add Setting</button>
            </div>
        </x-slot:actions>
    </x-page-header>

    @if($settings->count())
        @foreach($settings as $group => $groupSettings)
        <div class="card mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary bg-opacity-10 p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                    <i class="fas fa-cog text-primary"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-capitalize">{{ $group }}</h6>
                    <small class="text-muted">{{ $groupSettings->count() }} setting{{ $groupSettings->count() !== 1 ? 's' : '' }}</small>
                </div>
            </div>
            <div class="card-body p-0">
                @foreach($groupSettings as $setting)
                <div class="d-flex align-items-center gap-3 p-3 border-bottom border-light hover-bg transition" style="cursor:pointer;" onclick="openEditModal({{ $setting->id }})">
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fw-semibold small">{{ Str::title(str_replace('_', ' ', $setting->key)) }}</span>
                            <span class="badge bg-light text-muted rounded-pill" style="font-size:10px;">{{ $setting->key }}</span>
                            @if($setting->description)
                            <i class="fas fa-info-circle text-muted" title="{{ $setting->description }}" style="font-size:12px;cursor:help;"></i>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if($setting->type === 'boolean')
                                <div class="form-check form-switch mb-0" onclick="event.stopPropagation();">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        {{ filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}
                                        onchange="toggleBoolean({{ $setting->id }}, this)">
                                </div>
                                <span class="small {{ filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) ? 'text-success' : 'text-muted' }}">
                                    {{ filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) ? 'Enabled' : 'Disabled' }}
                                </span>
                            @elseif($setting->type === 'image' && $setting->value)
                                <img src="{{ $setting->image_url ?? asset($setting->value) }}" alt="{{ $setting->key }}" style="width:36px;height:36px;object-fit:cover;border-radius:6px;">
                                <span class="small text-muted text-truncate">{{ $setting->value }}</span>
                            @elseif($setting->type === 'color' && $setting->value)
                                <span class="rounded-circle d-inline-block border" style="width:24px;height:24px;background:{{ $setting->value }};"></span>
                                <span class="small text-muted">{{ $setting->value }}</span>
                            @else
                                <span class="small text-muted text-truncate d-block" style="max-width:400px;">{{ $setting->value ?: '(empty)' }}</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <span class="badge bg-opacity-10 rounded-pill px-2 py-1" style="font-size:10px;
                            {{ $setting->type === 'image' ? 'background:#e8f5e9;color:#2e7d32;' : '' }}
                            {{ $setting->type === 'boolean' ? 'background:#e3f2fd;color:#1565c0;' : '' }}
                            {{ $setting->type === 'number' ? 'background:#fce4ec;color:#c62828;' : '' }}
                            {{ !in_array($setting->type, ['image','boolean','number']) ? 'background:#f3e5f5;color:#6a1b9a;' : '' }}">
                            {{ $setting->type ?? 'string' }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    @else
        <x-empty-state icon="fa-cogs" title="No settings configured" description="Add your first system setting below." />
    @endif

    {{-- ───── ADD SETTING MODAL ───── --}}
    <x-modal id="addSettingModal" title="Add Setting" size="md">
        <x-slot:body>
            <form action="{{ route('admin.settings.store') }}" method="POST" id="addSettingForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Key <span class="text-danger">*</span></label>
                    <input type="text" name="key" class="form-control" placeholder="e.g. site_name, mail_host" required>
                    <div class="form-text">Use snake_case. Group prefix is added separately.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Value</label>
                    <textarea name="value" class="form-control" rows="3" placeholder="Setting value"></textarea>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col">
                        <label class="form-label fw-semibold small">Type</label>
                        <select name="type" class="form-select">
                            <option value="string">String</option>
                            <option value="text">Text</option>
                            <option value="number">Number</option>
                            <option value="boolean">Yes/No</option>
                            <option value="color">Color</option>
                            <option value="image">Image</option>
                        </select>
                    </div>
                    <div class="col">
                        <label class="form-label fw-semibold small">Group</label>
                        <input type="text" name="group" class="form-control" placeholder="general" value="general">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="What does this setting control?"></textarea>
                </div>
            </form>
        </x-slot:body>
        <x-slot:footer>
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" form="addSettingForm" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i>Save</button>
        </x-slot:footer>
    </x-modal>

    {{-- ───── EDIT SETTING MODALS ───── --}}
    @foreach($allSettings as $setting)
    <x-modal id="editSettingModal{{ $setting->id }}" title="Edit: {{ Str::title(str_replace('_', ' ', $setting->key)) }}" size="md">
        <x-slot:body>
            @php $isImage = $setting->type === 'image'; $isBool = $setting->type === 'boolean'; $isColor = $setting->type === 'color'; @endphp
            <form action="{{ route('admin.settings.update', $setting) }}" method="POST"
                  id="editForm{{ $setting->id }}" enctype="{{ $isImage ? 'multipart/form-data' : 'application/x-www-form-urlencoded' }}">
                @csrf @method('PUT')
                @if(!$isImage && !$isBool)
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Value</label>
                    @if($isColor)
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" name="value" class="form-control form-control-color" value="{{ $setting->value ?? '#000000' }}" style="width:60px;height:38px;padding:3px;">
                            <input type="text" class="form-control" value="{{ $setting->value }}" id="colorText{{ $setting->id }}" oninput="this.previousElementSibling.value=this.value">
                        </div>
                    @else
                        <textarea name="value" class="form-control" rows="3">{{ $setting->value }}</textarea>
                    @endif
                </div>
                @elseif($isImage)
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Current Image</label>
                    @if($setting->value)
                    <div class="mb-2">
                        <img src="{{ $setting->image_url ?? asset($setting->value) }}" alt="Current" style="max-width:200px;max-height:120px;object-fit:cover;border-radius:8px;border:1px solid #ddd;">
                        <p class="small text-muted mt-1 mb-0">{{ $setting->value }}</p>
                    </div>
                    @endif
                    <label class="form-label fw-semibold small mt-2">Upload New Image</label>
                    <input type="file" name="image_file" class="form-control" accept="image/*">
                    <div class="form-text">Max 5MB. Leave empty to keep current.</div>
                    <input type="hidden" name="value" value="{{ $setting->value }}">
                </div>
                @endif
                <input type="hidden" name="group" value="{{ $setting->group }}">
                <input type="hidden" name="type" value="{{ $setting->type }}">
                <input type="hidden" name="key" value="{{ $setting->key }}">
                @if($setting->description)
                <div class="alert alert-info bg-opacity-10 py-2 mb-0 small">
                    <i class="fas fa-info-circle me-1"></i> {{ $setting->description }}
                </div>
                @endif
            </form>
        </x-slot:body>
        <x-slot:footer>
            <div class="d-flex justify-content-between w-100">
                <x-confirm-delete
                    action="admin.settings.destroy"
                    :id="$setting->id"
                    label="Delete"
                    title="Delete Setting?"
                    message="This will permanently delete this setting."
                    mode="form"
                    class="btn btn-outline-danger btn-sm"
                />
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="editForm{{ $setting->id }}" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i>Update</button>
                </div>
            </div>
        </x-slot:footer>
    </x-modal>
    @endforeach

    {{-- ───── IMAGE MANAGER MODAL ───── --}}
    <x-modal id="imageManagerModal" title="Image Manager" size="lg">
        <x-slot:body>
            <div class="mb-4">
                <label class="form-label fw-semibold small">Upload New Image</label>
                <div class="d-flex gap-2">
                    <input type="file" id="imageManagerUpload" class="form-control form-control-sm" accept="image/*">
                    <button type="button" class="btn btn-primary btn-sm" id="imageManagerUploadBtn" disabled><i class="fas fa-upload me-1"></i>Upload</button>
                </div>
                <div id="imageManagerProgress" class="mt-2 d-none"><div class="progress" style="height:4px;"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width:100%"></div></div></div>
                <div id="imageManagerError" class="text-danger small mt-1 d-none"></div>
            </div>
            <hr>
            <label class="form-label fw-semibold small">Uploaded Images</label>
            <div id="imageManagerGrid" class="row g-2">
                @forelse($uploadedImages as $img)
                <div class="col-4 col-md-3 col-lg-2">
                    <div class="card border-0 shadow-sm image-manager-item" style="cursor:pointer;" data-url="{{ $img['url'] }}" data-path="{{ $img['path'] }}" title="{{ $img['filename'] }}">
                        <img src="{{ $img['url'] }}" alt="{{ $img['filename'] }}" style="width:100%;height:80px;object-fit:cover;border-radius:8px 8px 0 0;">
                        <div class="p-1 text-center"><span class="small text-muted text-truncate d-block" style="font-size:0.6rem;">{{ $img['filename'] }}</span></div>
                    </div>
                </div>
                @empty
                <div class="col-12"><p class="text-muted text-center py-3 small">No images uploaded yet.</p></div>
                @endforelse
            </div>
        </x-slot:body>
        <x-slot:footer>
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        </x-slot:footer>
    </x-modal>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Image Manager ──
    const uploadInput = document.getElementById('imageManagerUpload');
    const uploadBtn = document.getElementById('imageManagerUploadBtn');
    const progress = document.getElementById('imageManagerProgress');
    const errorEl = document.getElementById('imageManagerError');
    const grid = document.getElementById('imageManagerGrid');
    uploadInput.addEventListener('change', function() { uploadBtn.disabled = !this.files.length; });
    uploadBtn.addEventListener('click', function() {
        const file = uploadInput.files[0]; if(!file) return;
        progress.classList.remove('d-none'); errorEl.classList.add('d-none'); uploadBtn.disabled = true;
        const fd = new FormData(); fd.append('image', file);
        fetch('{{ route("admin.settings.upload-image") }}', { method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:fd })
        .then(r=>r.json()).then(data=>{
            if(data.success){
                const col=document.createElement('div'); col.className='col-4 col-md-3 col-lg-2';
                col.innerHTML=`<div class="card border-0 shadow-sm image-manager-item" style="cursor:pointer;" data-url="${data.url}" data-path="${data.path}" title="${data.filename}"><img src="${data.url}" alt="${data.filename}" style="width:100%;height:80px;object-fit:cover;border-radius:8px 8px 0 0;"><div class="p-1 text-center"><span class="small text-muted text-truncate d-block" style="font-size:0.6rem;">${data.filename}</span></div></div>`;
                grid.insertBefore(col, grid.firstChild); uploadInput.value = '';
            }
        }).catch(e=>{errorEl.textContent='Upload error';errorEl.classList.remove('d-none');})
        .finally(()=>{progress.classList.add('d-none');uploadBtn.disabled = false;});
    });
    grid.addEventListener('click', function(e) {
        const item = e.target.closest('.image-manager-item'); if(!item) return;
        const path = item.dataset.path;
        navigator.clipboard.writeText(path).catch(()=>{});
        const toast = document.createElement('div'); toast.className='position-fixed bottom-0 end-0 p-3'; toast.style.zIndex='9999';
        toast.innerHTML=`<div class="toast show align-items-center text-bg-success border-0"><div class="d-flex"><div class="toast-body small">Copied: <code>${path}</code></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`;
        document.body.appendChild(toast); setTimeout(()=>toast.remove(), 3000);
    });
});

function openEditModal(id) { var m = bootstrap.Modal.getOrCreateInstance(document.getElementById('editSettingModal'+id)); m.show(); }

function toggleBoolean(id, el) {
    fetch('{{ route("admin.settings.index") }}/'+id, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ value: el.checked ? 'true' : 'false' })
    }).then(r=>{if(!r.ok) el.checked=!el.checked;});
}
</script>
@endpush
@endsection