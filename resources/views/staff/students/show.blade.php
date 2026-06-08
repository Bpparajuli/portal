@extends('layouts.staff')

@section('page-title', 'Student Details')
@section('title', 'Staff | Student Details')

@section('staff-content')
<div class="container-fluid p-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h5 class="fw-bold mb-0" style="color: var(--primary);">{{ $student->full_name }}</h5>
            <p class="text-muted mb-0 small">Student details and documents</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('staff.students.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
            <a href="{{ route('staff.student.edit', $student) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-2">
                    <h6 class="fw-bold mb-0">Student Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-semibold">Name</label>
                            <div class="fw-medium">{{ $student->full_name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-semibold">Email</label>
                            <div class="fw-medium">{{ $student->email }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-semibold">Phone</label>
                            <div class="fw-medium">{{ $student->phone ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-semibold">Country</label>
                            <div class="fw-medium">{{ $student->country ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-semibold">Agent</label>
                            <div class="fw-medium">{{ $student->agent?->name ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-semibold">Created</label>
                            <div class="fw-medium">{{ $student->created_at?->format('F j, Y') ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($documentStats))
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-2">
                    <h6 class="fw-bold mb-0">Document Status</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Uploaded</span>
                        <span class="fw-bold">{{ $documentStats['uploaded'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Required</span>
                        <span class="fw-bold">{{ $documentStats['required'] ?? 0 }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        @php
                            $pct = isset($documentStats['required']) && $documentStats['required'] > 0
                                ? round(($documentStats['uploaded'] ?? 0) / $documentStats['required'] * 100)
                                : 0;
                        @endphp
                        <div class="progress-bar bg-success" role="progressbar"
                            style="width: {{ $pct }}%;"
                            aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <div class="text-center small text-muted mt-2">{{ $pct }}% complete</div>
                </div>
            </div>
        </div>
        @endif
    </div>

    @if($student->applications->isNotEmpty())
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white py-2">
            <h6 class="fw-bold mb-0">Applications ({{ $student->applications->count() }})</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">University</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th class="pe-3">Created Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($student->applications as $app)
                    <tr>
                        <td class="ps-3 fw-medium">{{ $app->university?->name ?? '—' }}</td>
                        <td class="text-muted">{{ $app->course?->title ?? '—' }}</td>
                        <td>
                            <span class="badge" style="background: {{ $app->status?->bg_color ?? '#6c757d' }};">
                                {{ $app->status?->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="text-muted small pe-3">{{ $app->created_at?->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($student->documents->isNotEmpty())
    <div class="card shadow-sm">
        <div class="card-header bg-white py-2">
            <h6 class="fw-bold mb-0">Documents ({{ $student->documents->count() }})</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Name</th>
                        <th>Type</th>
                        <th class="pe-3">Uploaded Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($student->documents as $doc)
                    <tr>
                        <td class="ps-3">{{ $doc->custom_name ?? str_replace('_', ' ', $doc->document_type) }}</td>
                        <td class="text-muted">{{ $doc->document_type }}</td>
                        <td class="text-muted small pe-3">{{ $doc->created_at?->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
