@extends('layouts.agent')

@section('title', 'Add New Student')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
@endpush
@section('agent-content')
    <div class="container-lg py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-0"><i class="fas fa-user-plus me-2 text-primary"></i> Add New Student</h4>
                <p class="text-muted mb-0 small mt-1">Fill in the student's details below</p>
            </div>
            <a href="{{ route('agent.students.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Students
            </a>
        </div>

        <form action="{{ route('agent.students.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf

            @include('agent.students.form', ['student' => new \App\Models\Student()])

            <div class="d-flex justify-content-between align-items-center mt-4">
                <a href="{{ route('agent.students.index') }}" class="btn btn-outline-danger btn-sm">
                    Cancel
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i> Add Student
                </button>
            </div>
        </form>
    </div>
@endsection
