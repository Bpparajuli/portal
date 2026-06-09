@extends('layouts.app')

@php $role = auth()->user()->role; @endphp

@section('title', 'Add University')

@section('content')
<div class="container-fluid px-3 py-3">
    <h4 class="fw-bold mb-3"><i class="fas fa-plus-circle text-primary me-2"></i>Add New University</h4>

    <form action="{{ route($role . '.universities.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                <input type="text" id="country" name="country" value="{{ old('country') }}" class="form-control @error('country') is-invalid @enderror" required>
                @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="city" class="form-label">City</label>
                <input type="text" id="city" name="city" value="{{ old('city') }}" class="form-control @error('city') is-invalid @enderror">
                @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="website" class="form-label">Website</label>
                <input type="url" id="website" name="website" value="{{ old('website') }}" class="form-control @error('website') is-invalid @enderror">
                @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address') }}</textarea>
            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
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
