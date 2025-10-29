@extends('layouts.app')
@if(auth()->check() && auth()->user()->is_admin)
@section('content')
<div class="p-3">
    <h2 class="text-center mb-4">Users Waiting for Approval</h2>
    @if($users->isEmpty())
    <div class="alert alert-info">No users waiting for approval.</div>
    @else
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Logo</th>
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
                <td>
                    @if($user->business_logo)
                    <img src="{{ Storage::url($user->business_logo) }}" alt="Logo" width="60" height="60" class="rounded-circle">
                    @else
                    <span class="text-muted">No Logo</span>
                    @endif
                </td>
                <td>{{ $user->business_name }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->contact }}</td>
                <td>{{ $user->address }}</td>
                <td>
                    <form action="{{ route('admin.users.approve', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-success">Approve</button>
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
