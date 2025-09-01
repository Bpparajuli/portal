@extends('layouts.app')

@section('content')
<div class="create-form-wrapper">
    <h2>Add New University</h2>
    <form action="{{ route('admin.universities.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        @include('admin.universities.form')

        <button type="submit" class="btn btn-success">Add University</button>
        <a href="{{ route('admin.universities.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

    <hr>
    <h3>Or Add a Course</h3>
    <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">Add New Course</a>
</div>
@endsection
