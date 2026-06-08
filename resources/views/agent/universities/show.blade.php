@extends('layouts.agent')

@section('title', $university->name)

@section('content')
<div class="container-fluid py-4">
    <a href="{{ route('agent.universities.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="fas fa-arrow-left"></i> Back to Universities
    </a>
    @include('shared.university-detail', ['prefix' => 'agent'])
</div>
@endsection
