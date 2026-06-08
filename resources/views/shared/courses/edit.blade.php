@extends('layouts.app')

@php $role = auth()->user()->role; @endphp

@section('title', 'Edit Course')

@section('content')
<div class="container-fluid px-3 py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">
            <i class="fas fa-edit text-primary me-2"></i>Edit Course: {{ $course->title }}
        </h4>
        <div class="d-flex gap-2">
            @if ($previous)
            <a href="{{ route($role . '.courses.edit', $previous->id) }}" class="btn btn-outline-secondary btn-sm">← Previous</a>
            @endif
            @if ($next)
            <a href="{{ route($role . '.courses.edit', $next->id) }}" class="btn btn-outline-primary btn-sm">Next →</a>
            @endif
        </div>
    </div>

    @can('delete', $course)
    <div class="mb-3">
        <x-confirm-delete
            action="{{ $role }}.courses.destroy"
            :id="$course->id"
            label="Delete Course"
            title="Delete {{ $course->course_code }} - {{ $course->title }}?"
            message="This action cannot be undone."
            class="btn btn-danger btn-sm"
        />
    </div>
    @endcan

    <form action="{{ route($role . '.courses.update', $course->id) }}" method="POST">
        @csrf
        @method('PUT')
        @include('shared.courses._form')
        <div class="d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-success">Update Course</button>
        </div>
    </form>
</div>
@endsection
