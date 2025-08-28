@extends('layouts.app')
@if(auth()->check() && auth()->user()->is_admin)

@section('content')
<div class="text-center">
    <a href="{{ route('admin.users.create') }}" class="d-inline-block mx-auto p-3 text-white text-center fw-bold bg-secondary mb-3">+ Add New User</a>
</div>
<!-- Admins Table -->

<h2 class="mb-3"> Admin User List</h2>
<table class="table table-bordered table-striped">
    <thead class="bg-secondary text-white">
        <tr>
            <th class="text-white">Logo</th>
            <th class="text-white">Owner</th>
            <th class="text-white">Business Name</th>
            <th class="text-white">Email</th>
            <th class="text-white">Contact</th>
            <th class="text-white">Status</th>
            <th class="text-white">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($admins as $admin)
        <tr>
            <td onclick="window.location='{{ route('admin.users.show', $admin->id) }}'" style="cursor:pointer;">
                @if($admin->business_logo)
                <img src="{{ asset('images/Agents_logo/' . $admin->business_logo) }}" width="40" alt="Logo">
                @endif
            </td>
            <td onclick="window.location='{{ route('admin.users.show', $admin->id) }}'" style="cursor:pointer;">{{ $admin->name }}</td>
            <td onclick="window.location='{{ route('admin.users.show', $admin->id) }}'" style="cursor:pointer;">{{ $admin->business_name }}</td>
            <td onclick="window.location='{{ route('admin.users.show', $admin->id) }}'" style="cursor:pointer;">{{ $admin->email }}</td>
            <td onclick="window.location='{{ route('admin.users.show', $admin->id) }}'" style="cursor:pointer;">{{ $admin->contact }}</td>
            <td>
                @if ($admin->active)
                <span class="badge bg-success">Active</span>
                @else
                <span class="badge bg-danger">Inactive</span>
                @endif
            </td>
            <td>
                <button> <a href="{{ route('admin.users.edit', $admin->id) }}" class="bg-gray-300 p-3 mx-1">Edit</a></button>
                @if(auth()->id() === 1 && auth()->id() !== $admin->id)
                <form method="POST" action="{{ route('admin.users.destroy', $admin->id) }}" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Delete user?')" class="bg-danger text-white p-3">Delete</button>
                </form>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Agents Table -->
<h2 class="mb-3"> Agents User List</h2>
<table class="table table-bordered table-striped">
    <thead class="bg-secondary text-white">
        <tr>
            <th class="text-white">Logo</th>
            <th class="text-white">Owner</th>
            <th class="text-white">Business Name</th>
            <th class="text-white">Email</th>
            <th class="text-white">Contact</th>
            <th class="text-white">Status</th>
            <th class="text-white">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($agents as $agent)
        <tr>
            <td>
                @if($agent->business_logo)
                <img src="{{ asset('images/agents/' . $agent->business_logo) }}" alt="Logo">
                @endif
            </td>
            <td>{{ $agent->name }}</td>
            <td>{{ $agent->business_name }}</td>
            <td>{{ $agent->email }}</td>
            <td>{{ $agent->contact }}</td>
            <td>
                @if ($agent->active)
                <span class="badge bg-success">Active</span>
                @else
                <span class="badge bg-danger">Inactive</span>
                @endif
            </td>
            <td>
                <button> <a href="{{ route('admin.users.edit', $agent->id) }}" class="bg-gray-300 p-3 mx-1">Edit</a></button>
                @if(auth()->id() === 1 && auth()->id() !== $agent->id)
                <form method="POST" action="{{ route('admin.users.destroy', $agent->id) }}" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Delete user?')" class="bg-danger text-white p-3">Delete</button>
                </form>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>
@endsection
@endif
