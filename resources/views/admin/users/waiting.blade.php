@extends('layouts.app')
@if(auth()->check() && auth()->user()->is_admin)
@section('content')
<div class="p-3">
    <h2 class="text-center mb-4">Users Waiting for Approval</h2>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($users->isEmpty())
    <div class="alert alert-info">No users waiting for approval.</div>
    @else
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Business Name</th>
                <th>Name</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Address</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->business_name }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->contact }}</td>
                <td>{{ $user->address }}</td>
                <td>
                    <form method="POST" action="{{ route('user.approve', $user->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
@endif
