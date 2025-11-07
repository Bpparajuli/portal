@extends('layouts.admin')

@section('admin-content')
<div class="container p-4">
    <h3>✏️ Edit Application #{{ $application->id }}</h3>

    <form action="{{ route('admin.applications.update', $application->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- STUDENT --}}
        <x-form.input name="student_name" label="Student" :value="$application->student->first_name . ' ' . $application->student->last_name" readonly />
        <input type="hidden" name="student_id" value="{{ $application->student->id }}">

        {{-- UNIVERSITY --}}
        <x-form.select name="university_id" label="University" required id="university_select">
            <option value="">-- Select University --</option>
            @foreach($universities as $uni)
            <option value="{{ $uni->id }}" {{ $application->university_id == $uni->id ? 'selected' : '' }}>
                {{ $uni->name }} - {{$uni->city}}
            </option>
            @endforeach
        </x-form.select>

        {{-- COURSE (dynamic) --}}
        <x-form.select name="course_id" label="Course" id="course_select">
            <option value="">-- Select Course --</option>
            @foreach($courses as $course)
            <option value="{{ $course->id }}" {{ $application->course_id == $course->id ? 'selected' : '' }}>
                {{ $course->title }}
            </option>
            @endforeach
        </x-form.select>

        {{-- SOP --}}
        <x-form.file name="sop" label="Upload New SOP (PDF/DOC)" />
        @if($application->sop && $application->sop->file_path)
        <a href="{{ Storage::url($application->sop->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
            👁️ Current SOP
        </a>
        @endif

        {{-- STATUS --}}
        <x-form.select name="application_status" label="Status" required>
            @foreach(\App\Models\Application::STATUSES as $status)
            <option value="{{ $status }}" {{ $application->application_status == $status ? 'selected' : '' }}>
                {{ $status }}
            </option>
            @endforeach
        </x-form.select>

        <button type="submit" class="btn btn-success mt-3">Update Application</button>
    </form>
</div>

{{-- DYNAMIC COURSES --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const uniSelect = document.getElementById('university_select');
        const courseSelect = document.getElementById('course_select');

        uniSelect.addEventListener('change', function() {
            const uniId = this.value;
            courseSelect.innerHTML = '<option value="">Loading...</option>';

            if (!uniId) {
                courseSelect.innerHTML = '<option value="">-- Select Course --</option>';
                return;
            }

            fetch(`/admin/applications/get-courses/${uniId}`)
                .then(response => response.json())
                .then(data => {
                    let options = '<option value="">-- Select Course --</option>';
                    data.forEach(course => {
                        options += `<option value="${course.id}">${course.title}</option>`;
                    });
                    courseSelect.innerHTML = options;
                })
                .catch(err => {
                    console.error('Error fetching courses:', err);
                    courseSelect.innerHTML = '<option value="">-- Select Course --</option>';
                });
        });
    });

</script>
@endsection
