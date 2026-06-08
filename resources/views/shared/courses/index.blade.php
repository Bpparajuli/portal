@extends('layouts.app')

@php $role = auth()->user()->role; @endphp

@section('title', 'Courses')

@section('content')
    @include('shared.courses._listing')
@endsection
