@extends('layout.app')

@section('content')
<div class="container">
    <h2>Add New University</h2>
    <form action="{{ route('universities.store') }}" method="POST" enctype="multipart/form-data">
        @include('universities._form')
    </form>
</div>
@endsection
