<!-- resources/views/students/edit.blade.php -->
@extends('layouts.app')
@section('content')
<h2>Edit Student</h2>

<form method="POST" action="{{ route('agent.students.update',$student->id) }}">
    @csrf @method('PUT')
    @include('agent.students.form')
    <button class="btn btn-primary">Update Student</button>
</form>
@endsection
