@extends('layouts.app')

@section('title', 'Add New Student')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
@endpush
@section('content')
    <div class="container-lg py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-0"><i class="fas fa-user-plus me-2 text-primary"></i> Add New Student</h4>
                <p class="text-muted small mb-0 mt-1">Fill in the student's details below</p>
            </div>
            <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Students
            </a>
        </div>

        <form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf

            @include('admin.students.form', [
                'student' => new \App\Models\Student(),
                'agents' => $agents,
                'universities' => $universities,
                'courses' => $courses,
            ])

            <div class="d-flex justify-content-between align-items-center mt-4">
                <a href="{{ route('admin.students.index') }}" class="btn btn-outline-danger btn-sm">
                    Cancel
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i> Add Student
                </button>
            </div>
        </form>
    </div>
@endsection
