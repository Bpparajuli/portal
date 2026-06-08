@extends('layouts.app')
@php $role = auth()->user()->role; @endphp

@section('title', $course->title . ' - Course Details')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route($role . '.courses.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Courses
        </a>
        <div class="d-flex gap-2">
            @can('update', $course)
            <a href="{{ route($role . '.courses.edit', $course) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endcan
            @can('delete', $course)
            <x-confirm-delete
                action="{{ $role }}.courses.destroy"
                :id="$course->id"
                label="Delete Course"
                title="Delete {{ $course->course_code }} - {{ $course->title }}?"
                message="This action cannot be undone."
            />
            @endcan
        </div>
    </div>
    @include('shared.course-detail', ['prefix' => $role])
</div>
@endsection
