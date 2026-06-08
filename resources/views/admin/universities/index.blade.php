@extends('layouts.admin')

@section('page-title', 'Universities')
@section('title', 'Admin | Universities')

@section('admin-content')
    @include('shared.universities._listing')
@endsection
