@extends('layouts.admin')

@section('page-title', 'Courses')
@section('title', 'Admin | Courses')

@section('admin-content')
    @include('shared.courses._listing')
@endsection
