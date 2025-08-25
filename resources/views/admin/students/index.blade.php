@extends('layouts.app')
@section('content')
<div class="mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2>All Students</h2>
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary">Add New Student</a>
    </div>
    <hr>

    {{-- Filter Form --}}
    <form class="mb-4 p-3 border rounded bg-light" method="GET" action="{{ route('admin.students.index') }}">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}">
            </div>

            @if(Auth::user()->is_admin)
            <div class="col-md-3">
                <label for="agent" class="form-label">Filter by Agent</label>
                <select class="form-select" id="agent" name="agent">
                    <option value="">All Agents</option>
                    @foreach($agents as $agent)
                    <option value="{{ $agent->id }}" {{ request('agent') == $agent->id ? 'selected' : '' }}>
                        {{ $agent->business_name ?? $agent->username }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="col-md-3">
                <label for="university" class="form-label">Filter by University</label>
                <select class="form-select" id="university" name="university">
                    <option value="">All Universities</option>
                    @foreach($universities as $university)
                    <option value="{{ $university->id }}" {{ request('university') == $university->id ? 'selected' : '' }}>
                        {{ $university->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="course_title" class="form-label">Filter by Course Name</label>
                <select class="form-select" id="course_title" name="course_title">
                    <option value="">All Courses</option>
                    @foreach($courses as $course)
                    <option value="{{ $course->title }}" {{ request('course_title') == $course->title ? 'selected' : '' }}>
                        {{ $course->title }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="status" class="form-label">Filter by Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Statuses</option>
                    @php
                    $statuses = ['pending', 'approved', 'rejected'];
                    @endphp
                    @foreach($statuses as $status)
                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                        {{ ucwords(str_replace('_', ' ', $status)) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="sort_by" class="form-label">Sort By</label>
                <select class="form-select" id="sort_by" name="sort_by">
                    @php
                    $sortOptions = ['created_at' => 'Created At', 'first_name' => 'First Name', 'email' => 'Email'];
                    @endphp
                    @foreach($sortOptions as $key => $value)
                    <option value="{{ $key }}" {{ request('sort_by') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="sort_order" class="form-label">Sort Order</label>
                <select class="form-select" id="sort_order" name="sort_order">
                    <option value="ASC" {{ request('sort_order') == 'ASC' ? 'selected' : '' }}>Ascending</option>
                    <option value="DESC" {{ request('sort_order', 'DESC') == 'DESC' ? 'selected' : '' }}>Descending</option>
                </select>
            </div>

            <div class="col-md-12 mt-3 d-flex justify-content-end">
                <a href="{{ route('admin.students.index') }}" class="btn btn-secondary me-2">Clear Filters</a>
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </div>
    </form>

    {{-- Student Table --}}
    <div class="table-responsive">
        <table class="table table-striped table-hover border">
            <thead class="bg-primary text-white">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>University</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Agent Name</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                <tr>
                    <td>{{ $student->id }}</td>
                    <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                    <td>{{ $student->email }}</td>
                    <td>{{ $student->university->name ?? 'N/A' }}</td>
                    <td>{{ $student->course->title ?? 'N/A' }}</td>
                    <td>
                        <span class="badge bg-light text-dark">
                            {{ ucwords(str_replace('_', ' ', $student->student_status)) }}
                        </span>
                    </td>
                    <td>{{ $student->agent->username ?? 'N/A' }}</td>
                    <td>{{ $student->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-outline-primary me-1">View</a>
                        <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-sm btn-outline-secondary me-1">Edit</a>
                        {{-- Delete button - use a form for security --}}
                        <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this student?');">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center">No students found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Links --}}
    <div class="mt-4">
        {{ $students->appends(request()->query())->links() }}
    </div>

</div>
@endsection
