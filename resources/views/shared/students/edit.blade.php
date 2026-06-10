@php
    $__user = auth()->user();
    $__isMgmt = $__user->is_admin || $__user->is_admin_staff;
    $__isStaff = $__user->is_staff && !$__user->is_admin_staff;
    $__isAgent = $__user->is_agent || $__user->is_agent_staff;
    $__layout = $__isAgent ? 'layouts.agent' : ($__isStaff ? 'layouts.staff' : 'layouts.admin');
    $__section = $__isAgent ? 'agent-content' : ($__isStaff ? 'staff-content' : 'admin-content');
    $__prefix = $__isAgent ? 'agent' : ($__isStaff ? 'staff' : 'admin');
    $__routePrefix = $__prefix . '.students';
@endphp

@extends($__layout)
@section('title', 'Edit: ' . $student->full_name)
@section('page-title', 'Edit: ' . $student->full_name)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/students.css') }}">
@endpush

@section($__section)
<form action="{{ route($__routePrefix . '.update', $student) }}" method="POST" enctype="multipart/form-data" id="editForm">
    @csrf @method('PUT')

    <div class="edit-page-wrapper">
        @include('shared.students.form', [
            'student' => $student,
            'agents' => $agents ?? null,
            'universities' => $universities ?? null,
            'courses' => $courses ?? null,
        ])
    </div>

    <div class="edit-fixed-bar">
        <div class="bar-left">
            <span class="bar-student">Editing: <strong>{{ $student->full_name }}</strong></span>
            <a href="{{ route($__routePrefix . '.show', $student) }}" class="btn btn-outline-secondary" style="font-size:0.75rem;padding:7px 14px;border-radius:8px;"><i class="fas fa-eye me-1"></i>View</a>
        </div>
        <div class="bar-right">
            <a href="{{ route($__routePrefix . '.show', $student) }}" class="btn btn-outline-secondary" style="font-size:0.75rem;padding:7px 16px;border-radius:8px;">Cancel</a>
            @can('delete', $student)
            <x-confirm-delete url="{{ route($__routePrefix . '.destroy', $student->id) }}" label="Delete" title="Delete {{ $student->full_name }}?" message="This action is permanent." class="btn btn-outline-danger" style="font-size:0.75rem;padding:7px 14px;border-radius:8px;" />
            @endcan
            <button type="submit" class="btn btn-success" style="font-size:0.75rem;padding:7px 20px;border-radius:8px;"><i class="fas fa-floppy-disk me-1"></i>Save</button>
        </div>
    </div>
</form>
@endsection