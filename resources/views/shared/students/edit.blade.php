@php
    $__user = auth()->user();
    $__isMgmt = $__user->is_admin || $__user->is_admin_staff;
    $__isAgent = $__user->is_agent;
    $__isStaff = $__user->is_staff && !$__user->is_admin_staff;
    $__layout = $__isAgent ? 'layouts.agent' : ($__isStaff ? 'layouts.staff' : 'layouts.admin');
    $__section = $__isAgent ? 'agent-content' : ($__isStaff ? 'staff-content' : 'admin-content');
    $__prefix = $__isAgent ? 'agent' : ($__isStaff ? 'staff' : 'admin');
    $__routePrefix = $__prefix . '.students';
@endphp

@extends($__layout)

@section('title', 'Edit Student: ' . $student->full_name)
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
@endpush

@section($__section)
<div class="{{ $__isMgmt ? 'container-lg py-4' : ($__isAgent ? 'container-lg py-4' : 'container-fluid p-3') }}">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0"><i class="fas fa-user-pen me-2 text-primary"></i> Edit Student</h4>
            <p class="text-muted small mb-0 mt-1">{{ $student->full_name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route($__routePrefix . '.show', $student) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-eye me-1"></i> View Profile
            </a>
            @if($__isMgmt || $__isAgent)
            <a href="{{ route($__prefix . '.documents.index', $student) }}" class="btn btn-outline-warning btn-sm">
                <i class="fas fa-folder-open me-1"></i> Documents
            </a>
            @endif
        </div>
    </div>

    <form action="{{ route($__routePrefix . '.update', $student) }}" method="POST" enctype="multipart/form-data" novalidate id="editForm">
        @csrf
        @method('PUT')

        @include('shared.students.form', [
            'student' => $student,
            'agents' => $agents ?? null,
            'universities' => $universities ?? null,
            'courses' => $courses ?? null,
        ])

        <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
            <div class="d-flex gap-2">
                <a href="{{ route($__routePrefix . '.show', $student) }}" class="btn btn-outline-secondary btn-sm">
                    Cancel
                </a>
                @can('delete', $student)
                <x-confirm-delete
                    url="{{ route($__routePrefix . '.destroy', $student->id) }}"
                    label="Delete Student"
                    title="Delete {{ $student->first_name }} {{ $student->last_name }}?"
                    message="This will permanently delete this student and all associated data."
                    mode="native"
                    class="btn btn-sm btn-danger"
                />
                @endcan
            </div>
            <button type="submit" form="editForm" class="btn btn-success">
                <i class="fas fa-floppy-disk me-1"></i> Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
