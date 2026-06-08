@extends('layouts.admin')
@section('admin-content')
<div style="max-width: 1400px; margin: 0 auto;">
    <x-page-header title="Application Status Management" subtitle="Create, edit and prioritize statuses for the application workflow.">
        <x-slot:actions>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#addNewStatusCard">
                <i class="fas fa-plus me-1"></i> Add New Status
            </button>
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Add New Status Collapsible --}}
    <div class="collapse mb-4" id="addNewStatusCard">
        <div class="card mb-4">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-plus-circle text-primary me-2"></i>Add New Status</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.application-status.store') }}">
                    @csrf
                    <div class="row g-3">
                        {{-- Status Name --}}
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-semibold">Status Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., Offer Issued" required>
                        </div>
                        {{-- Background Color --}}
                        <div class="col-md-2">
                            <label class="form-label text-muted small fw-semibold">Background Color</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" name="bg_color" class="form-control form-control-color border-0" value="#4f46e5" style="width:45px;height:38px;padding:2px;border-radius:6px;cursor:pointer;">
                                <input type="text" class="form-control text-uppercase" id="bgHex" value="#4F46E5" readonly style="font-size:0.8rem;background-color:var(--gray-50);">
                            </div>
                        </div>

                        {{-- Text Color --}}
                        <div class="col-md-2">
                            <label class="form-label text-muted small fw-semibold">Text Color</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" name="text_color" class="form-control form-control-color border-0" value="#ffffff" style="width:45px;height:38px;padding:2px;border-radius:6px;cursor:pointer;">
                                <input type="text" class="form-control text-uppercase" id="textHex" value="#FFFFFF" readonly style="font-size:0.8rem;background-color:var(--gray-50);">
                            </div>
                        </div>

                        {{-- Sort Order --}}
                        <div class="col-md-2">
                            <label class="form-label text-muted small fw-semibold">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>

                        {{-- Active --}}
                        <div class="col-md-1">
                            <label class="form-label text-muted small fw-semibold d-block">Active</label>
                            <div class="form-check form-switch pt-2">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked style="width:40px;height:20px;cursor:pointer;">
                            </div>
                        </div>

                        {{-- Add Button --}}
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary w-100 py-2"><i class="fas fa-save me-1"></i> Save</button>
                            <button type="button" class="btn btn-ghost py-2" data-bs-toggle="collapse" data-bs-target="#addNewStatusCard">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Status List Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr class="bg-light">
                            <th class="ps-4" width="80">ID</th>
                            <th width="200">Status Preview</th>
                            <th width="150">BG Color</th>
                            <th width="150">Text Color</th>
                            <th width="120">Sort Order</th>
                            <th width="120">Status</th>
                            <th class="pe-4 text-end" width="180">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statuses as $status)
                            <tr class="app-row">
                                <td class="ps-4 fw-semibold text-muted">{{ $status->id }}</td>
                                <td>
                                    <span class="badge px-3 py-2 rounded-pill fw-semibold"
                                        style="
                                            background-color: {{ $status->bg_color ?: '#6c757d' }};
                                            color: {{ $status->text_color ?: '#ffffff' }};
                                            font-size: 0.8rem;
                                            box-shadow: 0 2px 4px rgba(0,0,0,0.06);
                                        ">
                                        {{ $status->name }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="d-inline-block rounded-circle" style="width:12px;height:12px;background-color:{{ $status->bg_color ?: '#6c757d' }};"></span>
                                        <code class="text-uppercase small" style="color:var(--text-color);">{{ $status->bg_color ?: '#6C757D' }}</code>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="d-inline-block rounded-circle border" style="width:12px;height:12px;background-color:{{ $status->text_color ?: '#ffffff' }};"></span>
                                        <code class="text-uppercase small" style="color:var(--text-color);">{{ $status->text_color ?: '#FFFFFF' }}</code>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark px-2 py-1 small border">{{ $status->sort_order }}</span>
                                </td>
                                <td>
                                    @if ($status->is_active)
                                        <span class="status-pill bg-soft-success text-success fw-bold">
                                            <i class="fas fa-circle me-1" style="font-size: 6px;"></i> Active
                                        </span>
                                    @else
                                        <span class="status-pill bg-soft-danger text-danger fw-bold">
                                            <i class="fas fa-circle me-1" style="font-size: 6px;"></i> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <button class="btn btn-sm btn-ghost me-1" data-bs-toggle="collapse" data-bs-target="#editForm{{ $status->id }}">
                                        <i class="fas fa-edit me-1 text-primary"></i> Edit
                                    </button>
                                    <form action="{{ route('admin.application-status.destroy', $status->id) }}"
                                        method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this status? Any application with this status will need to be re-assigned.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-ghost text-danger">
                                            <i class="fas fa-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            {{-- Inline Edit Form --}}
                            <tr class="collapse" id="editForm{{ $status->id }}" style="background-color: var(--gray-50);">
                                <td colspan="7" class="p-4 border-bottom">
                                    <form method="POST" action="{{ route('admin.application-status.update', $status->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="row g-3">
                                            {{-- Name --}}
                                            <div class="col-md-3">
                                                <label class="form-label text-muted small fw-semibold">Status Name</label>
                                                <input type="text" name="name" class="form-control" value="{{ $status->name }}" required>
                                            </div>

                                            {{-- Background Color --}}
                                            <div class="col-md-2">
                                                <label class="form-label text-muted small fw-semibold">Background Color</label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="color" name="bg_color" class="form-control form-control-color border-0 bg-transparent" value="{{ $status->bg_color ?: '#4f46e5' }}" id="editBg{{ $status->id }}" style="width:45px;height:38px;padding:2px;border-radius:6px;cursor:pointer;">
                                                    <input type="text" class="form-control text-uppercase" id="editBgHex{{ $status->id }}" value="{{ $status->bg_color ?: '#4F46E5' }}" readonly style="font-size:0.8rem;background-color:#fff;">
                                                </div>
                                            </div>

                                            {{-- Text Color --}}
                                            <div class="col-md-2">
                                                <label class="form-label text-muted small fw-semibold">Text Color</label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="color" name="text_color" class="form-control form-control-color border-0 bg-transparent" value="{{ $status->text_color ?: '#ffffff' }}" id="editTxt{{ $status->id }}" style="width:45px;height:38px;padding:2px;border-radius:6px;cursor:pointer;">
                                                    <input type="text" class="form-control text-uppercase" id="editTxtHex{{ $status->id }}" value="{{ $status->text_color ?: '#FFFFFF' }}" readonly style="font-size:0.8rem;background-color:#fff;">
                                                </div>
                                            </div>

                                            {{-- Sort Order --}}
                                            <div class="col-md-2">
                                                <label class="form-label text-muted small fw-semibold">Sort Order</label>
                                                <input type="number" name="sort_order" class="form-control" value="{{ $status->sort_order }}">
                                            </div>

                                            {{-- Active --}}
                                            <div class="col-md-1">
                                                <label class="form-label text-muted small fw-semibold d-block">Active</label>
                                                <div class="form-check form-switch pt-2">
                                                    <input type="hidden" name="is_active" value="0">
                                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $status->is_active ? 'checked' : '' }} style="width:40px;height:20px;cursor:pointer;">
                                                </div>
                                            </div>

                                            {{-- Buttons --}}
                                            <div class="col-md-2 d-flex align-items-end gap-2">
                                                <button type="submit" class="btn btn-primary w-100 py-2"><i class="fas fa-check-circle me-1"></i> Update</button>
                                                <button type="button" class="btn btn-outline-secondary w-100 py-2" data-bs-toggle="collapse" data-bs-target="#editForm{{ $status->id }}">Cancel</button>
                                            </div>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="empty-state text-center py-4">
                                        <i class="fas fa-tags text-muted display-4 mb-3" style="opacity: 0.3;"></i>
                                        <h5 class="fw-bold mb-1">No application statuses found</h5>
                                        <p class="text-muted small mb-3">Add some custom status codes to get started with application pipeline management.</p>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#addNewStatusCard">
                                            <i class="fas fa-plus me-1"></i> Add Your First Status
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Live hex code updater for color pickers
    document.querySelector('input[name="bg_color"]').addEventListener('input', function(e) {
        document.getElementById('bgHex').value = e.target.value.toUpperCase();
    });
    document.querySelector('input[name="text_color"]').addEventListener('input', function(e) {
        document.getElementById('textHex').value = e.target.value.toUpperCase();
    });

    @foreach($statuses as $status)
    document.getElementById('editBg{{ $status->id }}').addEventListener('input', function(e) {
        document.getElementById('editBgHex{{ $status->id }}').value = e.target.value.toUpperCase();
    });
    document.getElementById('editTxt{{ $status->id }}').addEventListener('input', function(e) {
        document.getElementById('editTxtHex{{ $status->id }}').value = e.target.value.toUpperCase();
    });
    @endforeach
</script>
@endpush
@endsection
