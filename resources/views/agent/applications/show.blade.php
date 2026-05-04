@extends('layouts.agent')

@section('agent-content')
    <div class="container py-4">
        {{-- Modern Header with Gradient --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('agent.dashboard') }}"
                                class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('agent.applications.index') }}"
                                class="text-decoration-none">Applications</a></li>
                        <li class="breadcrumb-item active">Application #{{ $application->application_number }}</li>
                    </ol>
                </nav>
                <h1 class="display-6 fw-bold mb-0">📄 Application Details</h1>
            </div>
            <a href="{{ route('agent.applications.index') }}" class="btn btn-outline-secondary btn-lg rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i>Back to Applications
            </a>
        </div>

        <div class="row g-4">
            {{-- Main Content Column --}}
            <div class="col-lg-8">
                {{-- Status Card --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <span class="badge px-4 py-2 rounded-pill fs-6"
                                    style="background: linear-gradient(135deg, {{ $application->status?->bg_color ?? '#1a0262' }}, {{ $application->status?->bg_color ?? '#1a0262' }}dd);
                                           color: {{ $application->status?->text_color ?? '#ffffff' }};">
                                    <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                                    {{ $application->status?->name ?? 'No Status' }}
                                </span>
                            </div>
                            <div class="text-muted">
                                <i class="far fa-calendar-alt me-1"></i>
                                <strong>Document Completed on:</strong><br>
                                {{ optional($application->created_at)->format('F j, Y') }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Student Information Card --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0"><i class="fas fa-user-graduate me-2 text-primary"></i>Student Information
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-4 text-center">
                                @if ($application->student->students_photo && Storage::disk('public')->exists($application->student->students_photo))
                                    <img src="{{ Storage::url($application->student->students_photo) }}"
                                        class="rounded border shadow-sm"
                                        style="width: 150px; height: 150px; object-fit: cover;">
                                @else
                                    <div class="rounded bg-gradient-primary d-flex align-items-center justify-content-center mx-auto shadow-sm"
                                        style="width: 150px; height: 150px; background: var(--active);">
                                        <i class="fas fa-user fa-4x text-white"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-8">
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label class="text-muted small text-uppercase">Full Name</label>
                                        <p class="fw-semibold mb-0">{{ $application->student->first_name }}
                                            {{ $application->student->last_name }}</p>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="text-muted small text-uppercase">Email Address</label>
                                        <p class="fw-semibold mb-0">{{ $application->student->email }}</p>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="text-muted small text-uppercase">Phone Number</label>
                                        <p class="fw-semibold mb-0">{{ $application->student->phone_number }}</p>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="text-muted small text-uppercase">Agent</label>
                                        <p class="fw-semibold mb-0">
                                            {{ $application->student->agent?->business_name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-12">
                                        <label class="text-muted small text-uppercase">Address</label>
                                        <p class="fw-semibold mb-0">{{ $application->student->permanent_address }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- SOP Card --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0"><i class="fas fa-file-alt me-2 text-primary"></i>Statement of Purpose (SOP)
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        @if ($application->sop_file)
                            <div class="text-center">
                                <div class="mb-3">
                                    <i class="fas fa-file-pdf fa-4x text-danger"></i>
                                </div>
                                <p class="mb-3">Click the button below to view the SOP document</p>
                                <a href="{{ Storage::url($application->sop_file) }}" target="_blank"
                                    class="btn btn-primary rounded-pill px-4">
                                    <i class="fas fa-eye me-2"></i>View SOP Document
                                </a>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-file-alt fa-4x mb-3 opacity-50"></i>
                                <p>No SOP document has been uploaded for this application.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar Column --}}
            <div class="col-lg-4">
                {{-- University & Course Information Card --}}

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0"><i class="fas fa-university me-2 text-primary"></i>University & Course
                            Details</h5>
                    </div>
                    <div class="p-3 bg-light rounded-3">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-building fa-2x text-primary me-3"></i>
                            <div>
                                <h6 class="mb-0 fw-bold">{{ $application->university->name ?? 'N/A' }}</h6>
                                <small class="text-muted">{{ $application->university->city ?? 'N/A' }},
                                    {{ $application->university->country ?? 'N/A' }}</small>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="mt-2">
                            <i class="fas fa-graduation-cap me-2 text-primary"></i>
                            <span class="fw-semibold">Course:</span> {{ $application->course->title ?? 'N/A' }}
                        </div>
                        <hr class="my-2">
                        <div class="mt-2">
                            <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>
                            <span class="fw-semibold">Application Fee:</span>
                            {{ $application->course->application_fee ?? 'N/A' }}
                        </div>

                        <hr class="my-2">

                        <div class="mt-2">
                            <i class="fas fa-money-bill-wave me-2 text-success"></i>
                            <span class="fw-semibold">Course Fee:</span>
                            {{ $application->course->fee ?? 'N/A' }}
                        </div>

                        <hr class="my-2">

                        <div class="mt-2">
                            <i class="fas fa-award me-2 text-primary"></i>
                            <span class="fw-semibold">Scholarships:</span>
                            {{ $application->course->scholarships ?? 'N/A' }}
                        </div>
                    </div>
                </div>


                {{-- Quick Actions Card --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0"><i class="fas fa-bolt me-2 text-primary"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-grid gap-3">
                            <a href="{{ route('agent.applications.edit', $application->id) }}"
                                class="btn btn-outline-primary rounded-pill py-2">
                                <i class="fas fa-edit me-2"></i>Edit Application
                            </a>

                            @if (!$application->withdrawn_at)
                                <button class="btn btn-outline-danger rounded-pill py-2" data-bs-toggle="modal"
                                    data-bs-target="#withdrawModal">
                                    <i class="fas fa-ban me-2"></i>Withdraw Application
                                </button>
                            @endif

                            <button class="btn btn-outline-secondary rounded-pill py-2" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>Print Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Withdraw Modal --}}
    @if (!$application->withdrawn_at)
        <div class="modal fade" id="withdrawModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">Withdraw Application</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to withdraw this application?</p>
                        <p class="text-muted small">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancel</button>
                        <form action="{{ route('agent.applications.withdraw', $application->id) }}" method="POST"
                            class="d-inline">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-danger rounded-pill px-4">Yes, Withdraw</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
