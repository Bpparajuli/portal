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
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}">
            </div>

            @if(auth()->user()->is_admin)
            <div class="col-md-3">
                <label class="form-label">Filter by Agent</label>
                <select class="form-select" name="agent">
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
                <label class="form-label">Filter by University</label>
                <select class="form-select" name="university">
                    <option value="">All Universities</option>
                    @foreach($universities as $university)
                    <option value="{{ $university->id }}" {{ request('university') == $university->id ? 'selected' : '' }}>
                        {{ $university->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Filter by Course</label>
                <select class="form-select" name="course_title">
                    <option value="">All Courses</option>
                    @foreach($courses as $course)
                    <option value="{{ $course->title }}" {{ request('course_title') == $course->title ? 'selected' : '' }}>
                        {{ $course->title }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Filter by Status</label>
                <select class="form-select" name="status">
                    <option value="">All Statuses</option>
                    @foreach(\App\Models\Student::STATUSES as $status)
                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                        {{ \App\Models\Student::getStatusLabel($status) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Sort By</label>
                <select class="form-select" name="sort_by">
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
                <label class="form-label">Sort Order</label>
                <select class="form-select" name="sort_order">
                    <option value="ASC" {{ request('sort_order') == 'ASC' ? 'selected' : '' }}>Ascending</option>
                    <option value="DESC" {{ request('sort_order', 'DESC') == 'DESC' ? 'selected' : '' }}>Descending</option>
                </select>
            </div>

            <div class="col-12 mt-3 d-flex justify-content-end">
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
                    <th>Agent</th>
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
                    <td><span class="badge bg-light text-dark">{{ \App\Models\Student::getStatusLabel($student->student_status) }}</span></td>
                    <td>{{ $student->agent->business_name ?? $student->agent->username ?? 'N/A' }}</td>
                    <td>{{ $student->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                        <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">Delete</button>
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

    <div class="mt-4">
        {{ $students->appends(request()->query())->links() }}
    </div>
</div>
@endsection
