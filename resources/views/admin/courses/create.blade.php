@extends('layouts.app')

@section('content')
<div class="p-4">
    <h2>{{ isset($course) ? 'Edit' : 'Add' }} Course</h2>

    <form action="{{ isset($course) ? route('admin.courses.update', $course->id) : route('admin.courses.store') }}" method="POST">
        @csrf
        @if(isset($course))
        @method('PUT')
        @endif

        @php
        // If adding course from within a specific university page
        $selectedUniversityId = request()->get('university_id') ?? ($course->university_id ?? null);
        $isUniversityLocked = isset($selectedUniversityId);
        @endphp

        <div class="mb-3">
            <label>University</label>

            <select name="university_id" class="form-control" {{ $isUniversityLocked ? 'disabled' : '' }} required>
                <option value="">-- Select University --</option>
                @foreach($universities as $university)
                <option value="{{ $university->id }}" {{ $selectedUniversityId == $university->id ? 'selected' : '' }}>
                    {{ $university->name }}
                </option>
                @endforeach
            </select>

            {{-- Hidden field to ensure university_id gets submitted even if dropdown is disabled --}}
            @if($isUniversityLocked)
            <input type="hidden" name="university_id" value="{{ $selectedUniversityId }}">
            @endif
        </div>

        <div class="mb-3">
            <label>Course Code</label>
            <input type="text" name="course_code" value="{{ $course->course_code ?? '' }}" class="form-control">
        </div>

        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" value="{{ $course->title ?? '' }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Course Type</label>
            <select name="course_type" class="form-control" required>
                <option value="">-- Select Type --</option>
                <option value="ug" {{ isset($course) && $course->course_type == 'ug' ? 'selected' : '' }}>Undergraduate</option>
                <option value="pg" {{ isset($course) && $course->course_type == 'pg' ? 'selected' : '' }}>Postgraduate</option>
                <option value="diploma" {{ isset($course) && $course->course_type == 'diploma' ? 'selected' : '' }}>Diploma</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control">{{ $course->description ?? '' }}</textarea>
        </div>

        <div class="mb-3">
            <label>Duration</label>
            <input type="text" name="duration" value="{{ $course->duration ?? '' }}" class="form-control">
        </div>

        <div class="mb-3">
            <label>Fee</label>
            <input type="text" name="fee" value="{{ $course->fee ?? '' }}" class="form-control">
        </div>

        <div class="mb-3">
            <label>Intakes</label>
            <input type="text" name="intakes" value="{{ $course->intakes ?? '' }}" class="form-control">
        </div>

        <div class="mb-3">
            <label>IELTS/PTE/Other Languages</label>
            <input type="text" name="ielts_pte_other_languages" value="{{ $course->ielts_pte_other_languages ?? '' }}" class="form-control">
        </div>

        <div class="mb-3">
            <label>MOI Requirement</label>
            <input type="text" name="moi_requirement" value="{{ $course->moi_requirement ?? '' }}" class="form-control">
        </div>

        <div class="mb-3">
            <label>Application Fee</label>
            <input type="text" name="application_fee" value="{{ $course->application_fee ?? '' }}" class="form-control">
        </div>

        <div class="mb-3">
            <label>Scholarships</label>
            <input type="text" name="scholarships" value="{{ $course->scholarships ?? '' }}" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">{{ isset($course) ? 'Update' : 'Save' }}</button>

        @if(isset($selectedUniversityId))
        <a href="{{ route('admin.universities.edit', $selectedUniversityId) }}" class="btn btn-secondary">Back to University</a>
        @endif
    </form>
</div>
@endsection
