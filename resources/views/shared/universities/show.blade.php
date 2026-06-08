@extends('layouts.app')
@php $role = auth()->user()->role; @endphp

@section('title', $university->name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route($role . '.universities.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <div class="d-flex gap-2">
            @can('update', $university)
            <a href="{{ route($role . '.universities.edit', $university) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endcan
            @can('delete', $university)
            <x-confirm-delete
                action="{{ $role }}.universities.destroy"
                :id="$university->id"
                label="Delete"
                title="Delete {{ $university->name }}?"
                message="This will permanently delete this university and all its courses."
            />
            @endcan
        </div>
    </div>
    @include('shared.university-detail')
</div>
@endsection
