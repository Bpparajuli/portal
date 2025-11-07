@extends('layouts.admin')

@section('admin-content')
<div class="container p-4">
    <h3>➕ Create Application</h3>

    <form action="{{ route('admin.applications.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- STUDENT --}}
        @if(isset($student))
        <x-form.input name="student_name" label="Student" :value="$student->first_name . ' ' . $student->last_name" readonly />
        <input type="hidden" name="student_id" value="{{ $student->id }}">
        @else
        <x-form.select name="student_id" label="Select Student" required>
            <option value="">-- Select Student --</option>
            @foreach($students as $s)
            <option value="{{ $s->id }}">{{ $s->first_name }} {{ $s->last_name }}</option>
            @endforeach
        </x-form.select>
        @endif

        {{-- UNIVERSITY --}}
        <x-form.select name="university_id" label="University" required id="university_select">
            <option value="">-- Select University --</option>
            @foreach($universities as $uni)
            <option value="{{ $uni->id }}">{{ $uni->name }} - {{$uni->city}}</option>
            @endforeach
        </x-form.select>

        {{-- COURSE (dynamic) --}}
        <x-form.select name="course_id" label="Course" id="course_select">
            <option value="">-- Select Course --</option>
        </x-form.select>

        {{-- SOP --}}
        <x-form.file name="sop" label="Upload SOP (PDF/DOC)" required />

        <button type="submit" class="btn btn-success mt-3">Submit Application</button>
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
