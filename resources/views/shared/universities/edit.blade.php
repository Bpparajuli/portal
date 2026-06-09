@extends('layouts.app')
@php $role = auth()->user()->role; @endphp

@section('title', 'Edit University')

@section('content')
<div class="container-fluid px-3 py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">
            <i class="fas fa-edit text-primary me-2"></i>Edit University: {{ $university->name }}
        </h4>
        @can('delete', $university)
        <x-confirm-delete
            action="{{ $role }}.universities.destroy"
            :id="$university->id"
            label="Delete University"
            title="Delete {{ $university->name }}?"
            message="This will permanently delete this university and all its courses."
            class="btn btn-danger btn-sm"
        />
        @endcan
    </div>

    <form action="{{ route($role . '.universities.update', $university) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $university->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                <input type="text" id="country" name="country" value="{{ old('country', $university->country) }}" class="form-control @error('country') is-invalid @enderror" required>
                @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="city" class="form-label">City</label>
                <input type="text" id="city" name="city" value="{{ old('city', $university->city) }}" class="form-control @error('city') is-invalid @enderror">
                @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="website" class="form-label">Website</label>
                <input type="url" id="website" name="website" value="{{ old('website', $university->website) }}" class="form-control @error('website') is-invalid @enderror">
                @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $university->address) }}</textarea>
            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $university->description) }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="university_logo" class="form-label">University Logo</label>
            @if($university->university_logo)
            <div class="mb-2">
                <img src="{{ asset('storage/uni_logo/' . $university->university_logo) }}" style="height:60px;" class="border rounded p-1">
            </div>
            @endif
            <input type="file" id="university_logo" name="university_logo" class="form-control @error('university_logo') is-invalid @enderror">
            @error('university_logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-success">Update University</button>
        </div>
    </form>
</div>
@endsection
