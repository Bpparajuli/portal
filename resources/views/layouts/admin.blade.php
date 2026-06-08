@extends('layouts.app')

@section('page-title', $title ?? 'Admin Dashboard')
@section('title', 'Admin | ' . ($title ?? 'Dashboard'))

@section('content')
    @yield('admin-content')
@endsection
