@extends('layouts.admin')

@section('page-title', $university->name)
@section('title', 'Admin | ' . $university->name)

@section('admin-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('admin.universities.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.universities.edit', $university->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
            <form action="{{ route('admin.universities.destroy', $university->id) }}" method="POST" onsubmit="return confirm('Delete this university?');">
                @csrf @method('DELETE')
                <button class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
            </form>
            @endif
        </div>
    </div>

    @include('shared.university-detail', ['prefix' => 'admin'])
@endsection
