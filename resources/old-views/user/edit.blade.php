@extends('layout.app')

@section('content')
@if(auth()->check() && auth()->user()->is_admin)
<div>
    <div class="row justify-content-center">
        <div class="col-md-6 p-3 border border-primary rounded bg-white d-flex flex-column align-items-center">
            <h3 class="d-inline-block mx-auto p-3 text-white text-center fw-bold bg-primary mb-3">Add New User</h3>
            <form action="{{ route('user.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="w-100">
                @csrf
                @method('PUT')
                <!-- Add this line -->
                @include('partials.form', ['edit' => true])
                <div class="d-flex justify-content-center">
                    <button type="submit" class="btn btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>
    <div class="row justify-content-center mt-3">
        <div class="col-md-6 d-inline-flex justify-content-center align-items-center">
            <a href="{{ route('user.list') }}" class="btn bg-gray-400 btn-secondary">Back to User List</a>
        </div>
    </div>
</div>
@endsection
@endif
