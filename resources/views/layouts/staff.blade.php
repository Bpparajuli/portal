@extends('layouts.app')

@section('page-title', $title ?? 'Team Member Dashboard')
@section('title', 'Team Member | ' . ($title ?? 'Dashboard'))

@section('content')
    @yield('staff-content')
@endsection
