@extends('layouts.agent')

@section('agent-content')
<div class="container p-4">
    <div class="d-flex justify-content-between align-items-center p-2">
        <h3>➕ Create Application</h3>
        <a href="{{ route('agent.students.create') }}" class="btn btn-primary">
            <i class="fa fa-user"></i> + Add Student
        </a>
    </div>

    <form action="{{ route('agent.applications.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- STUDENT --}}
        @if(isset($student))
        <x-form.input name="student_name" label="Student" :value="$student->first_name . ' ' . $student->last_name" readonly />
        <input type="hidden" name="student_id" value="{{ $student->id }}">
        @else
        <x-form.select name="student_id" label="Select Student" required>
            <option value="">-- Select Student --</option>
            @forelse($students as $s)
            <option value="{{ $s->id }}" {{ old('student_id') == $s->id ? 'selected' : '' }}>
                {{ $s->first_name }} {{ $s->last_name }}
            </option>
            @empty
            <option value="" disabled>No students with all documents uploaded</option>
            @endforelse
        </x-form.select>
        @endif

        {{-- UNIVERSITY --}}
        @php
        $uniDisabled = isset($selectedUniversityId);
        $selectedUni = old('university_id', $selectedUniversityId ?? $application->university_id ?? '');
        @endphp
        <x-form.select name="university_id" label="University" required id="university_select" :disabled="$uniDisabled">
            <option value="">-- Select University --</option>
            @foreach($universities as $uni)
            <option value="{{ $uni->id }}" {{ $selectedUni == $uni->id ? 'selected' : '' }}>
                {{ $uni->name }} - {{ $uni->city }}
            </option>
            @endforeach
        </x-form.select>
        @if(isset($selectedUniversityId))
        <input type="hidden" name="university_id" value="{{ $selectedUniversityId }}">
        @endif

        {{-- COURSE --}}
        @php
        $courseDisabled = isset($selectedCourseId);
        $selectedCourse = old('course_id', $selectedCourseId ?? $application->course_id ?? '');
        @endphp
        <x-form.select name="course_id" label="Course" required id="course_select" :disabled="$courseDisabled">
            <option value="">-- Select Course --</option>
            @foreach($courses as $course)
            <option value="{{ $course->id }}" {{ $selectedCourse == $course->id ? 'selected' : '' }}>
                {{ $course->title }}
            </option>
            @endforeach
        </x-form.select>
        @if(isset($selectedCourseId))
        <input type="hidden" name="course_id" value="{{ $selectedCourseId }}">
        @endif

        {{-- SOP FILE (Auto Preview handled by global JS) --}}
        <x-form.file name="sop_file" label="Upload SOP (PDF/DOC/Image)" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required />

        <button type="submit" class="btn btn-success mt-3">Submit Application</button>
    </form>
</div>

{{-- Dynamic Courses --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const uniSelect = document.getElementById('university_select');
        const courseSelect = document.getElementById('course_select');

        if (!uniSelect || !courseSelect) return;

        uniSelect.addEventListener('change', function() {
            const uniId = this.value;
            courseSelect.innerHTML = '<option value="">Loading...</option>';

            if (!uniId) {
                courseSelect.innerHTML = '<option value="">-- Select Course --</option>';
                return;
            }

            fetch(`/agent/applications/get-courses/${uniId}`)
                .then(res => res.ok ? res.json() : [])
                .then(data => {
                    let options = '<option value="">-- Select Course --</option>';
                    if (Array.isArray(data)) {
                        data.forEach(course => {
                            options += `<option value="${course.id}">${course.title}</option>`;
                        });
                    }
                    courseSelect.innerHTML = options;
                })
                .catch(() => {
                    courseSelect.innerHTML = '<option value="">-- Select Course --</option>';
                });
        });
    });

</script>
@endsection
