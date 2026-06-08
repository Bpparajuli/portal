@extends('layouts.app')

@section('title', 'Universities')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/styles.css') }}">
@endpush

@section('content')
    @include('shared.universities._listing')
@endsection
