@extends('layouts.agent')

@section('agent-content')
<div class="container my-4">
    <h3>✏️ Edit Application</h3>

    <form action="{{ route('agent.applications.update',$application->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Student</label>
            <input type="text" class="form-control" value="{{ $application->student->full_name }}" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">University</label>
            <input type="text" class="form-control" value="{{ $application->university->name }}" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Course</label>
            <input type="text" class="form-control" value="{{ $application->course->name }}" readonly>
        </div>

        <x-form.textarea name="remarks" label="Remarks" :value="$application->remarks" />

        <x-form.file name="sop" label="Replace SOP (optional)" />

        <button type="submit" class="btn btn-primary">Update Application</button>
    </form>
</div>
@endsection
