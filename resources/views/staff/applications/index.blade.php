@extends('layouts.staff')

@section('page-title', 'Applications')
@section('title', 'Staff | Applications')

@section('staff-content')
<div class="container-fluid p-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h5 class="fw-bold mb-0" style="color: var(--primary);">Applications</h5>
            <p class="text-muted mb-0 small">Manage student applications</p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Student</th>
                        <th>University</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Agent</th>
                        <th class="pe-3">Created Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                    <tr>
                        <td class="ps-3 fw-medium">{{ $app->student?->name ?? '—' }}</td>
                        <td class="text-muted">{{ $app->university?->name ?? '—' }}</td>
                        <td class="text-muted">{{ $app->course?->title ?? '—' }}</td>
                        <td>
                            <span class="badge" style="background: {{ $app->status?->bg_color ?? '#6c757d' }};">
                                {{ $app->status?->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="text-muted">{{ $app->agent?->name ?? '—' }}</td>
                        <td class="text-muted small pe-3">{{ $app->created_at?->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No applications found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($applications->hasPages())
        <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
            {{ $applications->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
