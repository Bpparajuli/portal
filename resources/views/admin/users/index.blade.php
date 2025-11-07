@extends('layouts.admin')

@section('admin-content')
<div class="container-fluid p-4">
    <div class="page-header">
        <h2>All Users</h2>
        <a href="{{ route('admin.users.create') ?? '#' }}" class="add-btn">
            <i class="fas fa-user-plus me-2"></i> Add User
        </a>
    </div>

    {{-- Search + Filter --}}
    <form method="GET" action="{{ route('admin.users.index') }}">
        <div class="filter-bar">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search by name, email, or business...">
            <select name="role">
                <option value="">All Roles</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admins</option>
                <option value="agent" {{ request('role') === 'agent' ? 'selected' : '' }}>Agents</option>
            </select>
            <button type="submit"><i class="fas fa-search me-1"></i> Filter</button>
        </div>
    </form>

    {{-- === Admins Table === --}}
    @if(auth()->id() === 1)
    @if($admins->count())
    <div class="card-section">
        <h4>Admins</h4>
        <div class="table-wrapper">
            <table class="table table-hover table-striped align-middle shadow-sm rounded text-center">
                <thead>
                    <tr>
                        <th>ID</th>
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
                            <a href="{{ route('admin.users.show', $admin->id) }}" class="text-decoration-none">
                                {{ $admin->id }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $admin->id) }}">
                                @if($admin->business_logo)
                                <img src="{{ Storage::url($admin->business_logo) }}" alt="Logo" width="50" height="50" class="rounded-circle">
                                @else
                                <span class="text-muted">No Logo</span>
                                @endif
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $admin->id) }}" class="text-decoration-none fw-semibold">
                                {{ $admin->business_name }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $admin->id) }}" class="text-decoration-none">
                                {{ $admin->name }}
                            </a>
                        </td>
                        <td>{{ $admin->email }}</td>
                        <td><span class="badge {{ $admin->active ? 'bg-success' : 'bg-secondary' }}">{{ $admin->active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <a href="{{ route('admin.users.edit', $admin->id) }}" class="btn btn-light btn-sm"><i class="fa fa-edit"></i></a>
                            @if(auth()->id() === 1 && $admin->id !== 1)
                            <form action="{{ route('admin.users.destroy', $admin->id) }}" method="POST" style="display:inline-block;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $admins->links() }}
    </div>
    @endif
    @endif

    {{-- === Agents Table === --}}
    @if($agents->count())
    <div class="card-section">
        <h4>Agents</h4>
        <div class="table-wrapper">
            <table class="table table-hover table-striped align-middle shadow-sm rounded text-center">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Logo</th>
                        <th>Business</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Students</th>
                        <th>Applications</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agents as $agent)
                    <tr>
                        <td>
                            <a href="{{ route('admin.users.show', $agent->id) }}" class="text-decoration-none">
                                {{ $agent->id }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $agent->id) }}">
                                @if($agent->business_logo)
                                <img src="{{ Storage::url($agent->business_logo) }}" alt="Logo" width="45" height="45" class="rounded-3">
                                @else
                                <span class="text-muted">No Logo</span>
                                @endif
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $agent->id) }}" class="text-decoration-none fw-semibold">
                                {{ $agent->business_name }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $agent->id) }}" class="text-decoration-none">
                                {{ $agent->name }}
                            </a>
                        </td>
                        <td>{{ $agent->email }}<br><small>{{ $agent->contact }}</small></td>
                        <td><span class="badge {{ $agent->active ? 'bg-success' : 'bg-secondary' }}">{{ $agent->active ? 'Active' : 'Inactive' }}</span></td>
                        <td><a href="{{ route('admin.users.students', $agent->id) }}" class="btn btn-info btn-sm">{{ $agent->students_count }}</a></td>
                        <td><a href="{{ route('admin.users.applications', $agent->id) }}" class="btn btn-secondary btn-sm">{{ $agent->applications_count }}</a></td>
                        <td>
                            <a href="{{ route('admin.users.edit', $agent->id) }}" class="btn btn-secondary btn-sm"><i class="fa fa-edit"></i></a>
                            @if(auth()->id() === 1)
                            <form action="{{ route('admin.users.destroy', $agent->id) }}" method="POST" style="display:inline-block;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $agents->links() }}
    </div>
    @endif
</div>
@endsection
