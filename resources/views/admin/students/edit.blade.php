@extends('layouts.app')

@section('title', 'Edit Student: ' . $student->full_name)
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
@endpush
@section('content')
    <div class="container-lg py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-0"><i class="fas fa-user-pen me-2 text-primary"></i> Edit Student</h4>
                <p class="text-muted small mb-0 mt-1">{{ $student->full_name }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.students.show', $student) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-eye me-1"></i> View Profile
                </a>
                <a href="{{ route('admin.documents.index', $student) }}" class="btn btn-outline-warning btn-sm">
                    <i class="fas fa-folder-open me-1"></i> Documents
                </a>
            </div>
        </div>

        {{-- Main edit form --}}
        <form action="{{ route('admin.students.update', $student) }}" method="POST" enctype="multipart/form-data"
            novalidate id="editForm">
            @csrf
            @method('PUT')

            @include('admin.students.form', [
                'student' => $student,
                'agents' => $agents,
                'universities' => $universities,
                'courses' => $courses,
            ])

            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.students.show', $student) }}" class="btn btn-outline-secondary btn-sm">
                        Cancel
                    </a>
                    {{-- Delete – separate form so it doesn't interfere with PUT --}}
                    <button type="button" class="btn btn-sm btn-danger btn-delete"
                        data-url="{{ route('admin.students.destroy', $student->id) }}"
                        data-name="{{ $student->first_name }} {{ $student->last_name }}">
                        <i class="fa-solid fa-trash"></i>Delete Student
                    </button>
                </div>
                <button type="submit" form="editForm" class="btn btn-success">
                    <i class="fas fa-floppy-disk me-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
@endsection
