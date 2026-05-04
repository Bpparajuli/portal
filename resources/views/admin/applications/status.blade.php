@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold">Application Status Management</h3>
        </div>

        {{-- Add New Status --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header fw-bold">
                Add New Status
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('admin.application-status.store') }}">
                    @csrf

                    <div class="row g-3">

                        {{-- Status Name --}}
                        <div class="col-md-4">
                            <label class="form-label">Status Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        {{-- Background Color --}}
                        <div class="col-md-2">
                            <label class="form-label">Background Color</label>
                            <input type="color" name="bg_color" class="form-control  form-control-color w-100"
                                value="#1a0262" title="Choose background color">
                        </div>

                        {{-- Text Color --}}
                        <div class="col-md-2">
                            <label class="form-label">Text Color</label>
                            <input type="color" name="text_color" class="form-control form-control-color w-100"
                                value="#ffffff" title="Choose text color">
                        </div>

                        {{-- Sort Order --}}
                        <div class="col-md-2">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>

                        {{-- Active --}}
                        <div class="col-md-1">
                            <label class="form-label d-block">Active</label>

                            <input type="hidden" name="is_active" value="0">

                            <input type="checkbox" name="is_active" value="1" checked>
                        </div>

                        {{-- Add Button --}}
                        <div class="col-md-1 d-flex align-items-end">
                            <button class="btn btn-primary w-100">
                                Add
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- Status List --}}
        <div class="card shadow-sm">
            <div class="card-header fw-bold">
                All Application Statuses
            </div>

            <div class="card-body table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th width="60">ID</th>
                            <th width="180">Status Preview</th>
                            <th width="120">BG Color</th>
                            <th width="120">Text Color</th>
                            <th width="100">Sort</th>
                            <th width="100">Active</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($statuses as $status)
                            {{-- Main Row --}}
                            <tr>
                                <td>{{ $status->id }}</td>

                                {{-- Badge Preview --}}
                                <td>
                                    <span class="badge"
                                        style="
                                            background-color: {{ $status->bg_color ?: '#6c757d' }};
                                            color: {{ $status->text_color ?: '#ffffff' }};
                                        ">
                                        {{ $status->name }}
                                    </span>
                                </td>

                                <td>{{ $status->bg_color ?: '-' }}</td>

                                <td>{{ $status->text_color ?: '-' }}</td>

                                <td>{{ $status->sort_order }}</td>

                                {{-- Active --}}
                                <td>
                                    @if ($status->is_active)
                                        <span class="badge bg-success">
                                            Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td>
                                    {{-- Edit --}}
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="collapse"
                                        data-bs-target="#editForm{{ $status->id }}">
                                        Edit
                                    </button>

                                    {{-- Delete --}}
                                    <form action="{{ route('admin.application-status.destroy', $status->id) }}"
                                        method="POST" class="d-inline" onsubmit="return confirm('Delete this status?')">

                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-sm btn-danger">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            {{-- Inline Edit Form --}}
                            <tr class="collapse" id="editForm{{ $status->id }}">
                                <td colspan="8">
                                    <form method="POST"
                                        action="{{ route('admin.application-status.update', $status->id) }}">

                                        @csrf
                                        @method('PUT')

                                        <div class="row g-2">

                                            {{-- Name --}}
                                            <div class="col-md-4">
                                                <input type="text" name="name" class="form-control"
                                                    value="{{ $status->name }}" required>
                                            </div>

                                            {{-- Background Color --}}
                                            <div class="col-md-2">
                                                <input type="color" name="bg_color"
                                                    class="form-control form-control-color w-100"
                                                    value="{{ $status->bg_color ?: '#0d6efd' }}">
                                            </div>

                                            {{-- Text Color --}}
                                            <div class="col-md-2">
                                                <input type="color" name="text_color"
                                                    class="form-control form-control-color w-100"
                                                    value="{{ $status->text_color ?: '#ffffff' }}">
                                            </div>

                                            {{-- Sort Order --}}
                                            <div class="col-md-1">
                                                <input type="number" name="sort_order" class="form-control"
                                                    value="{{ $status->sort_order }}">
                                            </div>

                                            {{-- Active --}}
                                            <div class="col-md-1 d-flex align-items-center">
                                                <input type="hidden" name="is_active" value="0">

                                                <input type="checkbox" name="is_active" value="1"
                                                    {{ $status->is_active ? 'checked' : '' }}>
                                            </div>

                                            {{-- Buttons --}}
                                            <div class="col-md-2 d-flex gap-2">
                                                <button class="btn btn-success w-100">
                                                    Update
                                                </button>

                                                <button type="button" class="btn btn-secondary w-100"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#editForm{{ $status->id }}">
                                                    Cancel
                                                </button>
                                            </div>

                                        </div>
                                    </form>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    No application statuses found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
