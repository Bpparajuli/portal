@extends('layouts.app')

@php $role = auth()->user()->role; @endphp

@section('title', 'Applications')

@section('content')
<div class="container-fluid px-3 py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4 class="fw-bold mb-0"><i class="fas fa-file-alt text-primary me-2"></i>Applications</h4>
        <div class="d-flex gap-2">
            @if(auth()->user()->is_admin)
            <a href="{{ route('admin.exports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-download me-1"></i>Export</a>
            @endif
            @if(auth()->user()->is_admin || auth()->user()->is_agent)
            <a href="{{ route($role . '.applications.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Application</a>
            @endif
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-md-3 col-6">
            <div class="stat-card-app p-3 border rounded bg-white">
                <div class="lbl small text-muted text-uppercase">Total</div>
                <div class="num fs-3 fw-bold text-primary">{{ $applications->total() }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card-app p-3 border rounded bg-white">
                <div class="lbl small text-muted text-uppercase">Accepted</div>
                <div class="num fs-3 fw-bold text-success">{{ $acceptedCount ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card-app p-3 border rounded bg-white">
                <div class="lbl small text-muted text-uppercase">Rejected</div>
                <div class="num fs-3 fw-bold text-danger">{{ $rejectedCount ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card-app p-3 border rounded bg-white">
                <div class="lbl small text-muted text-uppercase">Lost</div>
                <div class="num fs-3 fw-bold text-secondary">{{ $lostCount ?? 0 }}</div>
            </div>
        </div>
    </div>

    @include('shared.applications._filters')

    <div class="table-responsive bg-white rounded border">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>University</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Agent</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $app)
                <tr>
                    <td class="text-muted small">{{ $app->application_number ?? $app->id }}</td>
                    <td><a href="{{ route($role === 'staff' ? 'staff.student.show' : $role . '.students.show', $app->student_id) }}" class="text-decoration-none fw-semibold">{{ $app->student?->full_name ?? '—' }}</a></td>
                    <td>{{ $app->university?->short_name ?? $app->university?->name ?? '—' }}</td>
                    <td class="small">{{ $app->course?->title ?? '—' }}</td>
                    <td><span class="badge" style="background:{{ $app->status?->bg_color ?? '#6c757d' }};">{{ $app->status?->name ?? '—' }}</span></td>
                    <td class="small">{{ $app->agent?->business_name ?? '—' }}</td>
                    <td class="small text-muted">{{ $app->created_at->format('d M Y') }}</td>
                    <td class="text-end">
                        <a href="{{ route($role . '.applications.show', $app->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4 text-muted">No applications found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $applications->appends(request()->query())->links() }}</div>
</div>
@endsection
