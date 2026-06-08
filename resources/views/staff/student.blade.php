@extends('layouts.staff')

@section('staff-content')
    <div class="container-fluid px-0">
        <x-page-header title="Student Details">
            <x-slot:actions>
                <a href="{{ route('staff.students.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Students
                </a>
                <a href="{{ route('staff.student.edit', $student) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit me-1"></i>Edit
                </a>
            </x-slot:actions>
        </x-page-header>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                                style="width: 56px; height: 56px; font-size: 1.5rem; font-weight: 700;">
                                {{ strtoupper(substr($student->full_name, 0, 1)) }}
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">{{ $student->full_name }}</h5>
                                <span class="badge bg-primary bg-opacity-10 text-primary">{{ $student->email }}</span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small text-uppercase fw-semibold">Email</label>
                                    <div class="fw-medium">{{ $student->email }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small text-uppercase fw-semibold">Phone</label>
                                    <div class="fw-medium">{{ $student->phone ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small text-uppercase fw-semibold">Country</label>
                                    <div class="fw-medium">{{ $student->country ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small text-uppercase fw-semibold">Agent</label>
                                    <div class="fw-medium">{{ $student->agent ? $student->agent->name : '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small text-uppercase fw-semibold">Created</label>
                                    <div class="fw-medium">{{ $student->created_at ? $student->created_at->format('F j, Y') : '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(isset($documentStats))
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="fw-bold mb-0"><i class="fas fa-file-alt me-2 text-primary"></i>Document Status</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted small">Uploaded</span>
                                <span class="fw-bold">{{ $documentStats['uploaded'] ?? 0 }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
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
    </div>
@endsection
