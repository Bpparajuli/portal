@extends('layouts.admin')

@section('admin-content')
<div class="container p-4">
    <div class="d-flex justify-content-between align-items-center">
        <h3>✏️ Edit Application: {{ $application->application_number }}</h3>
        <h5>Agent: {{ $application->agent->business_name }}</h5>
    </div>
    {{-- Update Form --}}
    <form action="{{ route('admin.applications.update', $application->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- STUDENT (readonly) --}}
        <x-form.input name="student_name" label="Student" :value="$application->student->first_name . ' ' . $application->student->last_name" readonly />
        <input type="hidden" name="student_id" value="{{ $application->student->id }}">

        {{-- UNIVERSITY --}}
        <x-form.select name="university_id" label="University" required id="university_select">
            <option value="">-- Select University --</option>
            @foreach($universities as $uni)
            <option value="{{ $uni->id }}" {{ $application->university_id == $uni->id ? 'selected' : '' }}>
                {{ $uni->name }} - {{ $uni->city }}
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

        {{-- SOP File --}}
        <x-form.file name="sop_file" label="Upload New SOP (PDF/DOC)" />
        @if($application->sop_file)
        <a href="{{ Storage::url($application->sop_file) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
            👁️ Current SOP
        </a>
        @endif

        {{-- APPLICATION STATUS --}}
        <x-form.select name="application_status" label="Change Application Status" required>
            @foreach(\App\Models\Application::STATUSES as $status)
            <option value="{{ $status }}" {{ $application->application_status == $status ? 'selected' : '' }}>
                {{ $status }}
            </option>
            @endforeach
        </x-form.select>

        {{-- Submit --}}
        <button type="submit" class="btn btn-success mt-3">Update Application</button>
    </form>

    {{-- Delete Button --}}
    <form action="{{ route('admin.applications.destroy', $application->id) }}" method="POST" class="mt-2">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
            <i class="fa fa-trash"></i> Delete
        </button>
    </form>
</div>

{{-- DYNAMIC COURSES SCRIPT --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const uniSelect = document.getElementById('university_select');
        const courseSelect = document.getElementById('course_select');

        uniSelect.addEventListener('change', function() {
            const uniId = this.value;
            courseSelect.innerHTML = '<option>Loading...</option>';

            if (!uniId) {
                courseSelect.innerHTML = '<option>-- Select Course --</option>';
                return;
            }

            fetch("/admin/get-courses/" + uniId)
                .then(res => res.json())
                .then(data => {
                    let html = '<option value="">-- Select Course --</option>';
                    data.forEach(course => {
                        html += `<option value="${course.id}">${course.title}</option>`;
                    });
                    courseSelect.innerHTML = html;
                })
                .catch(err => {
                    console.error(err);
                    courseSelect.innerHTML = '<option>-- Select Course --</option>';
                });
        });
    });

</script>
@endsection
