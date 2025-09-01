@extends('layouts.app')

@section('content')
<div class="student-form-wrapper">
    <div class="header-bar d-flex justify-content-between align-items-center mb-3">
        <h2 class="form-title">✏️ Edit Student</h2>
        <a href="{{ route('agent.students.show', $student->id) }}" class="btn btn-outline-secondary">👤 Back to Profile</a>
    </div>

    <form action="{{ route('agent.students.update', $student->id) }}" method="POST" enctype="multipart/form-data" class="student-form">
        @csrf @method('PUT')
        @include('agent.students.form')

        <div class="form-actions mt-3">
            <a href="{{ route('agent.documents.create', $student->id) }}" class="btn btn-warning">📄 Upload Document</a>
            <button type="submit" class="btn btn-success">💾 Save Changes</button>
        </div>
    </form>
</div>
@endsection
