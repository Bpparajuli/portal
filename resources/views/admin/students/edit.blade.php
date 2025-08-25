<!-- resources/views/students/edit.blade.php -->
@extends('layouts.app')
@section('content')
<h2>Edit Student</h2>

<form method="POST" action="{{ route('admin.students.update',$student->id) }}">
    @csrf @method('PUT')
    @include('admin.students.form')
    <button class="btn btn-primary">Update Student</button>
</form>
@endsection
