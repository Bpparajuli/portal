<!-- resources/views/students/create.blade.php -->
@extends('layouts.app')
@section('content')
<h2>Create Student</h2>

<form method="POST" action="{{ route('agent.students.store') }}">
    @csrf
    @include('agent.students.form')
    <!-- We will create a partial form -->
    <button class="btn btn-primary">Create Student</button>
</form>
@endsection
