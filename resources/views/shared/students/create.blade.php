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

@section('title', 'Add New Student')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
@endpush

@section($__section)
<div class="{{ $__isMgmt ? 'container-lg py-4' : ($__isAgent ? 'container-lg py-4' : 'container-fluid p-3') }}">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0"><i class="fas fa-user-plus me-2 text-primary"></i> Add New Student</h4>
            <p class="text-muted small mb-0 mt-1">Fill in the student's details below</p>
        </div>
        <a href="{{ route($__routePrefix . '.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Students
        </a>
    </div>

    <form action="{{ route($__routePrefix . '.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf

        @include('shared.students.form', [
            'student' => new \App\Models\Student(),
            'agents' => $agents ?? null,
            'universities' => $universities ?? null,
            'courses' => $courses ?? null,
        ])

        <div class="d-flex justify-content-between align-items-center mt-4">
            <a href="{{ route($__routePrefix . '.index') }}" class="btn btn-outline-danger btn-sm">
                Cancel
            </a>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-plus me-1"></i> Add Student
            </button>
        </div>
    </form>
</div>
@endsection
