@extends('layouts.app')

@section('content')
<div class="student-form-wrapper">
    <div class="header-bar d-flex justify-content-between align-items-center mb-3">
        <h2 class="form-title">✏️ Edit Student</h2>
        <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-outline-secondary">👤 Back to Profile</a>
    </div>

    <form action="{{ route('admin.students.update', $student->id) }}" method="POST" enctype="multipart/form-data" class="student-form">
        @csrf @method('PUT')
        @include('admin.students.form')
    </form>
    <div class="form-actions d-flex justify-content-between m-2">
        <button type="submit" class="btn btn-success">💾 Save Changes</button>
        <a href="{{ route('admin.documents.create', $student->id) }}" class="btn btn-warning">📄 Upload Document</a>
        <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Are you sure?');" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">🗑️ Delete Student</button>
        </form>
    </div>
    @endsection
