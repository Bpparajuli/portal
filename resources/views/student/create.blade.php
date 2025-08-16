<!-- resources/views/students/create.blade.php -->
@extends('layout.app')
@section('content')
<h2>Create Student</h2>

<form method="POST" action="{{ route('student.store') }}">
    @csrf
    @include('student.form')
    <!-- We will create a partial form -->
    <button class="btn btn-primary">Create Student</button>
</form>
@endsection
