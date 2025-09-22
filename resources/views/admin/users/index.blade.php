@extends('layouts.admin')

@section('admin-content')
<div class="container">
    <h2 class="mb-4">Users</h2>

    {{-- Admins --}}
    <h4>Admins</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Logo</th>
                <th>Business</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($admins as $admin)
            <tr>
                <td>
                    @if($admin->business_logo)
                    <img src="{{ Storage::url($admin->business_logo) }}" alt="Logo" width="60" height="60" class="rounded-circle">
                    @else
                    <span class="text-muted">No Logo</span>
                    @endif
                </td>
                <td>{{ $admin->business_name }}</td>
                <td>{{ $admin->name }}</td>
                <td>{{ $admin->email }}</td>
                <td>{{ $admin->active ? 'Active' : 'Inactive' }}</td>
                <td>
                    <a href="{{ route('admin.users.show', $admin->id) }}" class="btn btn-info btn-sm">View</a>
                    <a href="{{ route('admin.users.edit', $admin->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    @if(auth()->id() === 1 && $admin->id !== 1)
                    <form action="{{ route('admin.users.destroy', $admin->id) }}" method="POST" style="display:inline-block;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Agents --}}
    <h4 class="mt-5">Agents</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Logo</th>
                <th>Business</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($agents as $agent)
            <tr>
                <td>
                    @if($agent->business_logo)
                    <img src="{{ Storage::url($agent->business_logo) }}" alt="Logo" width="60" height="60" class="rounded-circle">
                    @else
                    <span class="text-muted">No Logo</span>
                    @endif
                </td>
                <td>{{ $agent->business_name }}</td>
                <td>{{ $agent->name }}</td>
                <td>{{ $agent->email }}</td>
                <td>{{ $agent->active ? 'Active' : 'Inactive' }}</td>
                <td>
                    <a href="{{ route('admin.users.show', $agent->id) }}" class="btn btn-info btn-sm">View</a>
                    <a href="{{ route('admin.users.edit', $agent->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    @if(auth()->id() === 1)
                    <form action="{{ route('admin.users.destroy', $agent->id) }}" method="POST" style="display:inline-block;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
