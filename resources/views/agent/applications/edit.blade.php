@extends('layouts.agent')

@section('agent-content')
<div class="container p-4">
    <h3>✏️ Edit Application</h3>

    <form action="{{ route('agent.applications.update', $application->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <x-form.input name="student_name" label="Student" :value="$application->student->first_name . ' ' . $application->student->last_name" readonly />
        <input type="hidden" name="student_id" value="{{ $application->student->id }}">

        <x-form.select name="university_id" label="University" required id="university_select">
            <option value="">-- Select University --</option>
            @foreach($universities as $uni)
            <option value="{{ $uni->id }}" {{ old('university_id', $application->university_id) == $uni->id ? 'selected' : '' }}>
                {{ $uni->name }} - {{ $uni->city }}
            </option>
            @endforeach
        </x-form.select>

        <x-form.select name="course_id" label="Course" required id="course_select">
            @foreach($courses as $course)
            <option value="{{ $course->id }}" {{ old('course_id', $application->course_id) == $course->id ? 'selected' : '' }}>
                {{ $course->title }}
            </option>
            @endforeach
        </x-form.select>

        {{-- SOP File --}}
        <x-form.file name="sop_file" label="SOP (Statement of Purpose)" :value="$application->sop_file" />

        <button type="submit" class="btn btn-primary mt-3">Update Application</button>
    </form>
</div>

{{-- Reuse the same dynamic course JS from create --}}
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
                            const selected = course.id == "{{ $application->course_id }}" ? 'selected' : '';
                            options += `<option value="${course.id}" ${selected}>${course.title}</option>`;
                        });
                    }
                    courseSelect.innerHTML = options;
                })
                .catch(() => courseSelect.innerHTML = '<option value="">-- Select Course --</option>');
        });
    });

</script>
@endsection
