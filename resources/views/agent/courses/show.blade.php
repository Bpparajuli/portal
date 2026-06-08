@extends('layouts.agent')

@section('title', $course->title . ' - Course Details')

@section('content')
<div class="container-fluid py-4">
    <a href="{{ route('agent.courses.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="fas fa-arrow-left"></i> Back to Courses
    </a>
    @include('shared.course-detail', ['prefix' => 'agent'])
</div>
@endsection
