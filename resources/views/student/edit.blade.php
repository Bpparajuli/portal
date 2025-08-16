<!-- resources/views/students/edit.blade.php -->
@extends('layout.app')
@section('content')
<h2>Edit Student</h2>

<form method="POST" action="{{ route('student.update',$student->id) }}">
    @csrf @method('PUT')
    @include('student.form')
    <button class="btn btn-primary">Update Student</button>
</form>
@endsection
