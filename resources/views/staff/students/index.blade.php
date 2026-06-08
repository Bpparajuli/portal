@extends('layouts.staff')

@section('page-title', 'Students')
@section('title', 'Staff | Students')

@section('staff-content')
<div class="container-fluid p-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h5 class="fw-bold mb-0" style="color: var(--primary);">Students</h5>
            <p class="text-muted mb-0 small">Manage all students</p>
        </div>
        <form method="GET" action="{{ route('staff.students.index') }}" class="d-flex gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search students..." value="{{ request('search') }}" style="min-width: 220px;">
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i>Search</button>
        </form>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Agent</th>
                        <th>Created Date</th>
                        <th class="pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                    <tr>
                        <td class="ps-3">
                            <a href="{{ route('staff.student.show', $student) }}" class="text-decoration-none fw-medium">{{ $student->full_name }}</a>
                        </td>
                        <td class="text-muted">{{ $student->email }}</td>
                        <td class="text-muted">{{ $student->phone ?? '—' }}</td>
                        <td class="text-muted">{{ $student->agent?->name ?? '—' }}</td>
                        <td class="text-muted small">{{ $student->created_at?->format('M d, Y') }}</td>
                        <td class="pe-3">
                            <a href="{{ route('staff.student.show', $student) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No students found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($students->hasPages())
        <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
            {{ $students->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
