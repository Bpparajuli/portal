@extends('layouts.guest')

@section('content')
<div class="container py-4">
    <a href="{{ route('guest.universities.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="fas fa-arrow-left"></i> Back to Universities
    </a>
    @include('shared.university-detail', ['prefix' => 'guest'])
</div>
@endsection
