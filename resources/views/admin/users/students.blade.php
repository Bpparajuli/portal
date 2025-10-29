@extends('layouts.admin')
@section('admin-content')

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="fa fa-users text-primary me-2"></i>Students of {{ $agent->business_name }}</h3>
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search students...">
            <button class="btn btn-primary"><i class="fa fa-search me-1"></i>Search</button>
        </form>
    </div>

    @if($students->count())
    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle shadow-sm rounded text-center">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Applications</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $index => $student)
                <tr>
                    <td>{{ $students->firstItem() + $index }}</td>
                    <td>
                        <a href="{{ route('admin.students.show', $student->id) }}" class="text-decoration-none">
                            {{ $student->first_name }} {{ $student->last_name }}
                        </a>
                    </td>
                    <td>{{ $student->email }}</td>
                    <td>
                        <a href="{{ route('admin.students.show', $student->id) }}" class="badge bg-info text-decoration-none">
                            {{ $student->applications_count }}
                        </a>
                    </td>
                    <td>{{ $student->created_at->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $students->links('pagination::bootstrap-5') }}
    </div>
    @else
    <div class="alert alert-info text-center">No students found for this agent.</div>
    @endif
</div>

@endsection
