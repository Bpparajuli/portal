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
        @include('shared.universities._form')
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
