@extends('layouts.app')

@php $role = auth()->user()->role; @endphp

@section('title', 'Universities')

@section('content')
    @include('shared.universities._listing')
@endsection
