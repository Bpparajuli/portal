<!-- resources/views/students/edit.blade.php -->
@extends('layout.app')
@section('content')
<h2>Edit Student</h2>

<form method="POST" action="{{ route('students.update',$student->id) }}">
    @csrf @method('PUT')
    @include('students.form')
    <button class="btn btn-primary">Update Student</button>
</form>
@endsection
