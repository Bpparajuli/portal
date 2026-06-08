@extends('layouts.app')

@php $role = auth()->user()->role; @endphp

@section('title', 'Add University')

@section('content')
<div class="container-fluid px-3 py-3">
    <h4 class="fw-bold mb-3"><i class="fas fa-plus-circle text-primary me-2"></i>Add New University</h4>

    <form action="{{ route($role . '.universities.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('shared.universities._form')
        <div class="mb-3">
            <label for="university_logo" class="form-label">University Logo</label>
            <input type="file" id="university_logo" name="university_logo" class="form-control @error('university_logo') is-invalid @enderror">
            @error('university_logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route($role . '.universities.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-success">Add University</button>
        </div>
    </form>
</div>
@endsection
