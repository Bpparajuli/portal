@extends('layouts.admin')

@section('page-title', $course->title)
@section('title', 'Admin | ' . $course->title)

@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Courses
        </a>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
            <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" onsubmit="return confirm('Delete this course?');">
                @csrf @method('DELETE')
                <button class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
            </form>
            @endif
        </div>
    </div>

    @include('shared.course-detail', ['prefix' => 'admin'])
@endsection
