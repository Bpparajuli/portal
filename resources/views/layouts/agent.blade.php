@extends('layouts.app')

@section('page-title', $title ?? 'Agent Dashboard')
@section('title', 'Agent | ' . ($title ?? 'Dashboard'))

@section('content')
    @yield('agent-content')
@endsection
