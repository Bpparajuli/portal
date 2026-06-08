@extends('layouts.app')

@php $role = auth()->user()->role; @endphp

@section('title', 'Add Course')

@section('content')
<div class="container-fluid px-3 py-3">
    <h4 class="fw-bold mb-3"><i class="fas fa-plus-circle text-primary me-2"></i>Add New Course</h4>

    <form action="{{ route($role . '.courses.store') }}" method="POST">
        @csrf
        @include('shared.courses._form')
        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route($role . '.courses.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-success">Add Course</button>
        </div>
    </form>
</div>
@endsection
