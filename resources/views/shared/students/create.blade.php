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
@section('page-title', 'Add New Student')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
@endpush

@section($__section)
<form action="{{ route($__routePrefix . '.store') }}" method="POST" enctype="multipart/form-data" id="createForm">
    @csrf

    <div class="edit-page-wrapper">
        @include('shared.students.form', [
            'student' => new \App\Models\Student(),
            'agents' => $agents ?? null,
            'universities' => $universities ?? null,
            'courses' => $courses ?? null,
        ])
    </div>

    <div class="edit-fixed-bar">
        <div class="bar-left">
            <span class="bar-student" style="color:#9ca3af;font-size:0.8rem;">Adding a new student</span>
        </div>
        <div class="bar-right">
            <a href="{{ route($__routePrefix . '.index') }}" class="btn btn-outline-secondary" style="font-size:0.75rem;padding:7px 16px;border-radius:8px;">Cancel</a>
            <button type="submit" class="btn btn-success" style="font-size:0.75rem;padding:7px 20px;border-radius:8px;"><i class="fas fa-plus me-1"></i>Add Student</button>
        </div>
    </div>
</form>
@endsection