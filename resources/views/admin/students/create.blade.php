@extends('layouts.app')

@section('content')
<div class="create-form-wrapper">
    <h2 class="form-title">➕ Add New Student</h2>

    <form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data" class="student-form">
        @csrf
        @include('admin.students.form')

        <div class="form-actions">
            <button type="submit" class="btn-main">💾 Save Student</button>
            <a href="{{ route('admin.students.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
