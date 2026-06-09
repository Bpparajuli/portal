@extends('layouts.admin')

@section('title', 'Application Status Management')

@push('styles')
<style>
.status-card { transition: all 0.2s; }
.status-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
</style>
@endpush

@section('admin-content')
<div class="container-fluid px-4 py-4">
    <x-page-header title="Application Statuses" subtitle="Manage application workflow statuses">
        <x-slot:actions>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStatusModal">
                <i class="fas fa-plus me-1"></i> Add Status
            </button>
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0" style="font-size:0.82rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:60px;">#</th>
                            <th>Name</th>
                            <th>Badge Preview</th>
                            <th>Sort Order</th>
                            <th>Status</th>
                            <th class="pe-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statuses as $status)
                        <tr>
                            <td class="ps-3 fw-medium">{{ $status->id }}</td>
                            <td class="fw-semibold">{{ $status->name }}</td>
                            <td>
                                <span class="badge"
                                      style="background:{{ $status->bg_color ?? '#6c757d' }}; color:{{ $status->text_color ?? '#fff' }};">
                                    {{ $status->name }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $status->sort_order ?? 0 }}</span>
                            </td>
                            <td>
                                @if($status->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="pe-3 text-end">
                                <button class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editStatusModal{{ $status->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <x-confirm-delete
                                    action="admin.application-status.destroy"
                                    :id="$status->id"
                                    label=""
                                    title="Delete {{ $status->name }}?"
                                    message="This will permanently delete this status. Applications with this status will not be affected."
                                    mode="form"
                                    class="btn btn-sm btn-outline-danger"
                                />
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No statuses defined yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add Status Modal --}}
<div class="modal fade" id="addStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.application-status.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Application Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Status Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Visa Granted">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Badge Background Color</label>
                        <input type="color" name="bg_color" class="form-control form-control-color" value="#0d6efd">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Text Color</label>
                        <input type="color" name="text_color" class="form-control form-control-color" value="#ffffff">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="0" min="0">
                </div>
                <div class="form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" value="1" checked id="addIsActive">
                    <label class="form-check-label small" for="addIsActive">Active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Save Status</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Status Modals --}}
@foreach($statuses as $status)
<div class="modal fade" id="editStatusModal{{ $status->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.application-status.update', $status->id) }}" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Status Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $status->name }}" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Badge Background Color</label>
                        <input type="color" name="bg_color" class="form-control form-control-color" value="{{ $status->bg_color ?? '#0d6efd' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Text Color</label>
                        <input type="color" name="text_color" class="form-control form-control-color" value="{{ $status->text_color ?? '#ffffff' }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ $status->sort_order ?? 0 }}" min="0">
                </div>
                <div class="form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ $status->is_active ? 'checked' : '' }} id="editIsActive{{ $status->id }}">
                    <label class="form-check-label small" for="editIsActive{{ $status->id }}">Active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Update Status</button>
            </div>
        </form>
    </div>
</div>
@endforeach

@endsection