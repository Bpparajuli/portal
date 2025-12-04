@extends('layouts.admin')

@section('admin-content')
<div class="container-fluid p-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <h2>All Users</h2>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fas fa-user-plus me-2"></i> Add User
        </a>
    </div>

    {{-- Search + Filter --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-3">
        <div class="d-flex gap-2 align-items-center">
            <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control" placeholder="Search by name, email, or business...">
            <select name="role" class="form-select">
                <option value="">All Roles</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admins</option>
                <option value="agent" {{ request('role') === 'agent' ? 'selected' : '' }}>Agents</option>
            </select>
            <button type="submit" class="btn btn-secondary"><i class="fas fa-search me-1"></i> Filter</button>
        </div>
    </form>

    {{-- === Admins Table === --}}
    @if(auth()->id() === 1 && $admins->count())
    <div class="card-section mb-4">
        <h4>Admins</h4>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-primary">
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
                            <a href="{{ route('admin.users.show', $admin->slug) }}" class="text-decoration-none">
                                {{ $admin->id }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $admin->slug) }}">
                                @if($admin->business_logo)
                                <img src="{{ Storage::url($admin->business_logo) }}" alt="Logo" width="50" height="50" class="rounded-circle">
                                @else
                                <span class="text-muted">No Logo</span>
                                @endif
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $admin->slug) }}" class="text-decoration-none fw-semibold">
                                {{ $admin->business_name }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $admin->slug) }}" class="text-decoration-none">
                                {{ $admin->name }}
                            </a>
                        </td>
                        <td>{{ $admin->email }}</td>
                        <td>
                            <span class="badge {{ $admin->active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $admin->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.edit', $admin->slug) }}" class="btn btn-light btn-sm"><i class="fa fa-edit"></i></a>
                            @if(auth()->id() === 1 && $admin->id !== 1)
                            <form action="{{ route('admin.users.destroy', $admin->slug) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{-- Admin Pagination --}}
            <div class="mt-2">
                {{ $admins->withQueryString()->links() }}
            </div>
        </div>
    </div>
    @endif

    {{-- === Agents Table === --}}
    @if($agents->count())
    <div class="card-section">
        <h4>Agents</h4>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Logo</th>
                        <th>Business</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Agreement</th>
                        <th>Students</th>
                        <th>Applications</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agents as $agent)
                    <tr>
                        <td>
                            <a href="{{ route('admin.users.show', $agent->slug) }}">
                                {{ $agent->id }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $agent->slug) }}">
                                @if($agent->business_logo)
                                <img src="{{ Storage::url($agent->business_logo) }}" alt="Logo" width="45" height="45" class="rounded-3">
                                @else
                                <span class="text-muted">No Logo</span>
                                @endif
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $agent->slug) }}" class="text-decoration-none fw-semibold">
                                {{ $agent->business_name }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $agent->slug) }}" class="text-decoration-none">
                                {{ $agent->name }}
                            </a>
                        </td>
                        <td>{{ $agent->email }}<br><small>{{ $agent->contact }}</small></td>
                        <td>
                            <span class="badge {{ $agent->active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $agent->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.edit', $agent->slug) }}" class="text-decoration-none">
                                @if ($agent->agreement_status === 'not_uploaded')
                                <span class="badge bg-secondary">Not Uploaded</span>
                                @elseif ($agent->agreement_status === 'uploaded')
                                <span class="badge bg-warning text-dark">Uploaded</span>
                                @elseif ($agent->agreement_status === 'verified')
                                <span class="badge bg-success">Verified</span>
                                @else
                                <span class="badge bg-danger">Unknown</span>
                                @endif
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.students', $agent->slug) }}" class="btn btn-info btn-sm">{{ $agent->students_count }}</a>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.applications', $agent->slug) }}" class="btn btn-secondary btn-sm">{{ $agent->applications_count }}</a>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.edit', $agent->slug) }}" class="btn btn-light btn-sm"><i class="fa fa-edit"></i></a>
                            @if(in_array(auth()->id(), [1, 2]) && !in_array($agent->id, [1, 2]))
                            <form action="{{ route('admin.users.destroy', $agent->slug) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Do you want to delete user {{ $agent->business_name }}?')"><i class="fa fa-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{-- Agent Pagination --}}
            <div class="mt-2">
                {{ $agents->withQueryString()->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
