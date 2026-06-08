@extends('layouts.staff')

@section('page-title', 'Courses')
@section('title', 'Staff | Courses')

@section('staff-content')
<div class="container-fluid p-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h5 class="fw-bold mb-0" style="color: var(--primary);">Courses</h5>
            <p class="text-muted mb-0 small">Browse available courses</p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Course Title</th>
                        <th>University</th>
                        <th>Duration</th>
                        <th class="pe-3">Fee</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                    <tr>
                        <td class="ps-3 fw-medium">{{ $course->title }}</td>
                        <td class="text-muted">{{ $course->university?->name ?? '—' }}</td>
                        <td class="text-muted">{{ $course->duration ?? '—' }}</td>
                        <td class="pe-3">{{ is_numeric($course->fee) ? number_format($course->fee, 2) : ($course->fee ?: '—') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">No courses found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($courses->hasPages())
        <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
            {{ $courses->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
