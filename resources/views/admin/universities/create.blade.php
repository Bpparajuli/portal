@extends('layouts.app')

@section('content')
<div class=" mt-4">
    <h2>Add New University</h2>

    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Success Message --}}
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

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
