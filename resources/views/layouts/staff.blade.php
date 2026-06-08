@extends('layouts.app')

@section('page-title', $title ?? 'Team Members Dashboard')
@section('title', 'Team Members | ' . ($title ?? 'Dashboard'))

@section('content')
    @yield('staff-content')
@endsection
