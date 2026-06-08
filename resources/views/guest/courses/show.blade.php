@extends('layouts.guest')
@section('title', $course->title . ' - Course Details')

@section('content')
<div class="container py-4">
    <a href="{{ route('guest.courses.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="fas fa-arrow-left"></i> Back to Courses
    </a>
    @include('shared.course-detail', ['prefix' => 'guest'])
</div>
@endsection
