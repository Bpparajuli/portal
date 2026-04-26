@extends('layouts.agent')

@section('title', 'Edit Student: ' . $student->full_name)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
@endpush
@section('agent-content')
    <div class="container-lg py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-0"><i class="fas fa-user-pen me-2 text-primary"></i> Edit Student</h4>
                <p class="text-muted mb-0 small mt-1">{{ $student->full_name }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('agent.students.show', $student) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-eye me-1"></i> View Profile
                </a>
                <a href="{{ route('agent.students.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('agent.students.update', $student) }}" enctype="multipart/form-data"
            novalidate>
            @csrf
            @method('PUT')

            @include('agent.students.form', ['student' => $student])

            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="div"> <a href="{{ route('agent.students.show', $student) }}"
                        class="btn btn-outline-danger btn-sm">
                        Cancel
                    </a>
                    <button type="button" class="btn btn-sm btn-danger btn-delete"
                        data-url="{{ route('agent.students.destroy', $student->id) }}"
                        data-name="{{ $student->first_name }} {{ $student->last_name }}">
                        <i class="fa-solid fa-trash"></i>Delete Student
                    </button>
                </div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-floppy-disk me-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
@endsection
