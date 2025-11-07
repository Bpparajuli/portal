@extends('layouts.app')

@section('content')
<div class="p-3">
    @if(auth()->check() && auth()->user()->is_admin)
    <h2 class="text-center mb-4">Users Waiting for Approval</h2>

    @if($users->isEmpty())
    <div class="alert alert-info text-center">No users waiting for approval.</div>
    @else
    <div class="table-responsive">
        <table class="table table-bordered align-middle text-center">
            <thead class="table-dark">
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
                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    @else
    <div class="alert alert-danger text-center mt-5">
        You are not authorized to view this page.
    </div>
    @endif
</div>
@endsection
