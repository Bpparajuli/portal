@extends('layouts.agent')

@section('title', 'Student Profile: ' . $student->full_name)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
@endpush
@section('agent-content')
    <div>
        {{-- Header with gradient background --}}
        <div class="row mb-5">
            <div class="col-12">
                <div class="d-flex flex-wrap justify-content-between align-items-center p-4 rounded-bottom-4"
                    style="background: var(--active); box-shadow: var(--shadow);">
                    <div>
                        <h1 class="text-white fw-bold mb-2">
                            {{ $student->full_name }}
                        </h1>
                        <div class="d-flex gap-2 mt-2 flex-wrap align-items-center">
                            @if ($student->email)
                                <div>
                                    <a href="mailto:{{ $student->email }}" class="btn btn-light p-2 rounded-3">
                                        <i class="fa-solid fa-envelope"></i>
                                        {{ Str::limit($student->email, 30) }}
                                    </a>
                                </div>
                            @else
                                <div class="bg-muted small">
                                    <i class="fa-solid fa-envelope"></i> N/A
                                </div>
                            @endif
                            <div>|</div>
                            @if ($student->phone_number)
                                <div>
                                    <a href="tel:{{ $student->phone_number }}" class="btn btn-light p-2 rounded-3">
                                        <i class="fa-solid fa-phone"></i>
                                        {{ $student->phone_number }}
                                    </a>
                                </div>
                            @else
                                <div class="bg-muted mt-1 small">
                                    <i class="fa-solid fa-phone"></i> N/A
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex flex-column align-items-center gap-2 mt-3 mt-md-0">
                        <a href="{{ route('agent.students.index') }}" class="btn btn-outline-light px-4 shadow-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                        <a href="{{ route('agent.students.edit', $student) }}"
                            class="btn btn-outline-secondary px-4 text-white border-white shadow-sm">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>

                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- Left Sidebar – Profile Card (Glassmorphism) --}}
            <div class="col-lg-4">
                <div class="card border-0 rounded-4 shadow-lg sticky-top"
                    style="top: 20px; background: var(--glass-gradient); backdrop-filter: blur(10px);">
                    <div class="card-body text-center p-4">
                        {{-- Photo --}}
                        <div class="mb-4 position-relative d-inline-block">
                            @if ($student->students_photo)
                                <img src="{{ Storage::url($student->students_photo) }}" alt="Student Photo"
                                    class="rounded shadow-lg student-img">
                            @else
                                <div
                                    class="bg-white bg-opacity-25 rounded d-inline-flex align-items-center justify-content-center shadow-sm student-img">
                                    <i class="fas fa-user-graduate fa-5x text-white"></i>
                                </div>
                            @endif
                            <div class="ribbon-bottom ">
                                <span class="text-bold text-capitalize">
                                    {{ $student->preferred_country ?? 'N/A' }}
                                </span>
                            </div>
                        </div>

                        <h3 class="fw-bold text-primary">{{ $student->full_name }}</h3>

                        {{-- Quick Stats --}}
                        <hr class="bg-light bg-opacity-25">
                        <div class="row text-start mt-3">
                            <div class="col-6">
                                <i class="fas fa-folder-open me-2 text-info"></i>
                                Documents
                                <p class="fw-bold mb-0 text-secondary">{{ $student->documents->count() }}</p>
                            </div>
                            <div class="col-6">
                                <i class="fas fa-file-alt me-2 text-primary"></i>
                                Applications
                                <p class="fw-bold mb-0 text-secondary">{{ $student->applications->count() }}</p>
                            </div>
                        </div>
                        <hr class="bg-light bg-opacity-25">
                        {{-- Notes Tab (fixed AJAX) --}}
                        <div>
                            <h6 class="fw-bold text-start text-sm" style="color: var(--primary);">
                                Remarks or Additional Information</h6>
                            <div id="notes-view-mode">
                                <p id="notes-text" class="p-3 bg-light rounded-3">
                                    {{ $student->notes ?? 'No any remarks added.' }}</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Right Main Content --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-header bg-white p-0 border-0">
                        <ul class="nav nav-tabs nav-justified border-0" id="studentTab" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active fw-semibold py-3" data-bs-toggle="tab"
                                    data-bs-target="#overview">
                                    <i class="fas fa-id-card me-2"></i> Overview
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-semibold py-3" data-bs-toggle="tab"
                                    data-bs-target="#applications">
                                    <i class="fas fa-university me-2"></i> Applications
                                    <span
                                        class="badge bg-primary rounded-pill ms-1">{{ $student->applications->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-semibold py-3" data-bs-toggle="tab" data-bs-target="#documents">
                                    <i class="fas fa-folder-open me-2"></i> Documents
                                    <span class="badge bg-info rounded-pill ms-1">{{ $student->documents->count() }}</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-4 tab-content">
                        {{-- Overview Tab (stays similar but with gradient cards) --}}
                        <div class="tab-pane fade show active" id="overview">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="p-3 rounded-4 h-100 shadow-sm" style="background: var(--light-gradient);">
                                        <h6 class="fw-bold" style="color: var(--primary);"><i class="fas fa-user me-2"></i>
                                            Personal</h6>
                                        <hr>
                                        <p><strong>DOB:</strong>
                                            {{ $student->dob ? $student->dob->format('M d, Y') : 'N/A' }}<br>
                                            <strong>Gender:</strong> {{ $student->gender ?? 'N/A' }}<br>
                                            <strong>Nationality:</strong> {{ $student->nationality ?? 'N/A' }}<br>
                                            <strong>Marital Status:</strong> {{ $student->marital_status ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-4 h-100 shadow-sm" style="background: var(--light-gradient);">
                                        <h6 class="fw-bold" style="color: var(--success);"><i
                                                class="fas fa-passport me-2"></i> Passport</h6>
                                        <hr>
                                        <p><strong>Number:</strong> {{ $student->passport_number ?? 'N/A' }}<br>
                                            <strong>Expiry:</strong>
                                            {{ $student->passport_expiry ? $student->passport_expiry->format('M d, Y') : 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-4 h-100 shadow-sm" style="background: var(--light-gradient);">
                                        <h6 class="fw-bold" style="color: var(--warning);"><i
                                                class="fas fa-graduation-cap me-2"></i> Education</h6>
                                        <hr>
                                        <p><strong>Qualification:</strong> {{ $student->qualification ?? 'N/A' }}<br>
                                            <strong>Passed Year:</strong> {{ $student->passed_year ?? 'N/A' }}<br>
                                            <strong>Gap:</strong> {{ $student->gap ?? '0' }} years<br>
                                            <strong>Grades:</strong> {{ $student->last_grades ?? 'N/A' }}<br>
                                            <strong>Board:</strong> {{ $student->education_board ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-4 h-100 shadow-sm" style="background: var(--light-gradient);">
                                        <h6 class="fw-bold" style="color: var(--info);"><i class="fas fa-globe me-2"></i>
                                            Study Preferences</h6>
                                        <hr>
                                        <p><strong>Country:</strong> {{ $student->preferred_country ?? 'N/A' }}<br>
                                            <strong>City:</strong> {{ $student->preferred_city ?? 'N/A' }}<br>
                                            <strong>Course:</strong> {{ $student->preferred_course ?? 'N/A' }}<br>
                                            <strong>University:</strong> {{ $student->preferred_university ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-3 rounded-4 shadow-sm" style="background: var(--light-gradient);">
                                        <h6 class="fw-bold" style="color: var(--secondary);"><i
                                                class="fas fa-home me-2"></i> Addresses</h6>
                                        <hr>
                                        <p><strong>Permanent:</strong> {{ $student->permanent_address ?? 'N/A' }}<br>
                                            <strong>Temporary:</strong> {{ $student->temporary_address ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Applications Tab (improved card design) --}}
                        <div class="tab-pane fade" id="applications" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                                <h5 class="fw-bold mb-2 mb-sm-0" style="color: var(--primary);">
                                    <i class="fas fa-file-alt me-2"></i> Applications & Communication
                                </h5>
                                <div>
                                    @if ($applications->count())
                                        {{-- Already applied → show view applications --}}
                                        <div class="d-flex flex-column gap-1">
                                            <a href="{{ route('agent.students.applications', $student->id) }}"
                                                class="btn btn-sm btn-outline-primary" title="View Applications">View
                                                Applications</a>
                                            <a href="{{ route('agent.applications.create') }}?student_id={{ $student->id }}"
                                                class="btn btn-sm btn-success" title="Apply">
                                                Add Another <i class="fa-solid fa-paper-plane"></i>
                                            </a>
                                        </div>
                                    @elseif ($student->uploaded_count >= $totalRequiredDocs)
                                        {{-- Docs complete → allow apply --}}
                                        <a href="{{ route('agent.applications.create') }}?student_id={{ $student->id }}"
                                            class="btn btn-sm btn-success" title="Apply">
                                            Apply Now <i class="fa-solid fa-paper-plane"></i>
                                        </a>
                                    @else
                                        {{-- Docs incomplete → upload --}}
                                        <a href="{{ route('agent.documents.index', $student) }}"
                                            class="btn btn-sm btn-outline-warning" title="Upload Docs">
                                            Upload Docs <i class="fa-solid fa-folder-open"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            @if ($student->applications->isNotEmpty())
                                <div class="row g-4">
                                    @foreach ($student->applications as $app)
                                        <div class="col-12">
                                            <div
                                                class="card border-0 shadow-sm rounded-4 overflow-hidden hover-scale transition">
                                                <div class="card-header py-3 px-4"
                                                    style="background: var(--active); color: white;">
                                                    <div
                                                        class="d-flex justify-content-between align-items-center flex-wrap">
                                                        <div>
                                                            <i class="fas fa-ticket-alt me-2"></i>
                                                            <strong>Application
                                                                #{{ $app->application_number ?? $app->id }}</strong>
                                                        </div>
                                                        <div>
                                                            <i class="far fa-calendar-alt me-1"></i>
                                                            {{ $app->created_at->format('M d, Y') }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body p-4">
                                                    <div class="row g-4">
                                                        <div class="col-md-6">
                                                            <div class="info-group mb-3">
                                                                <label
                                                                    class="text-muted small text-uppercase fw-semibold">University</label>
                                                                <p class="fw-bold mb-0">
                                                                    {{ $app->university->name ?? 'N/A' }}</p>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <div class="info-group mb-3">
                                                                        <label
                                                                            class="text-muted small text-uppercase fw-semibold">Country</label>
                                                                        <p class="mb-0">
                                                                            {{ $app->university->country ?? 'N/A' }}</p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div class="info-group mb-3">
                                                                        <label
                                                                            class="text-muted small text-uppercase fw-semibold">City</label>
                                                                        <p class="mb-0">
                                                                            {{ $app->university->city ?? 'N/A' }}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="info-group mb-3">
                                                                <label
                                                                    class="text-muted small text-uppercase fw-semibold">Course</label>
                                                                <p class="mb-0">{{ $app->course->title ?? 'N/A' }}</p>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <div class="info-group mb-3">
                                                                        <label
                                                                            class="text-muted small text-uppercase fw-semibold">Duration</label>
                                                                        <p class="mb-0">
                                                                            {{ $app->course->duration ?? 'N/A' }}</p>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div class="info-group mb-3">
                                                                        <label
                                                                            class="text-muted small text-uppercase fw-semibold">Fee</label>
                                                                        <p class="mb-0">{{ $app->course->fee ?? 'N/A' }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="d-flex flex-wrap gap-3 mb-3">
                                                                <div class="bg-light rounded-3 p-3 flex-fill text-center">
                                                                    <div class="small text-muted">Application Status</div>
                                                                    <span
                                                                        class="badge {{ $app->status_class ?? 'bg-warning' }} fs-6 px-3 py-2 mt-1">
                                                                        {{ $app->application_status }}
                                                                    </span>
                                                                </div>
                                                                <div class="bg-light rounded-3 p-3 flex-fill text-center">
                                                                    <div class="small text-muted">Statement of Purpose
                                                                    </div>
                                                                    @if ($app->sop_file)
                                                                        <a href="{{ Storage::url($app->sop_file) }}"
                                                                            target="_blank"
                                                                            class="btn btn-sm btn-outline-primary mt-2 rounded-pill">
                                                                            <i class="fas fa-file-pdf me-1"></i> View SOP
                                                                        </a>
                                                                    @else
                                                                        <a href="{{ route('agent.applications.edit', $app->id) }}"
                                                                            class="btn btn-sm btn-outline-secondary mt-2 rounded-pill">
                                                                            <i class="fas fa-upload me-1"></i> Upload SOP
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="d-flex gap-2 justify-content-end mt-3">
                                                                <a href="{{ route('agent.applications.show', $app->id) }}"
                                                                    class="btn btn-sm btn-outline-info rounded-pill">
                                                                    <i class="fas fa-eye me-1"></i> View Full
                                                                </a>
                                                                <a href="{{ route('agent.applications.edit', $app->id) }}"
                                                                    class="btn btn-sm btn-outline-success rounded-pill">
                                                                    <i class="fas fa-edit me-1"></i> Edit
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Messages Section --}}
                                                    <div class="mt-4">
                                                        <div
                                                            class="d-flex justify-content-between align-items-center mb-2">
                                                            <h6 class="fw-bold mb-0"><i
                                                                    class="fas fa-comment-dots me-2"></i> Conversation</h6>
                                                            <button class="btn btn-sm btn-link text-decoration-none"
                                                                type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#messages-{{ $app->id }}">
                                                                <i class="fas fa-chevron-down"></i>
                                                            </button>
                                                        </div>
                                                        <div class="collapse show" id="messages-{{ $app->id }}">
                                                            <div class="border rounded-3 p-3 bg-light"
                                                                style="max-height: 300px; overflow-y: auto;">
                                                                @forelse($app->messages as $msg)
                                                                    <div
                                                                        class="d-flex mb-3 {{ $msg->type === 'agent' ? 'justify-content-start' : 'justify-content-end' }}">
                                                                        <div class="p-3 rounded-3 shadow-sm"
                                                                            style="max-width:75%; background-color: {{ $msg->type === 'agent' ? '#fff' : 'var(--primary)' }}; color: {{ $msg->type === 'agent' ? '#000' : '#fff' }};">
                                                                            <p class="mb-1">{{ $msg->message }}</p>
                                                                            <small class="opacity-75">
                                                                                <i class="far fa-user-circle me-1"></i>
                                                                                {{ $msg->user->name ?? 'System' }} •
                                                                                {{ $msg->created_at->timezone('Asia/Kathmandu')->format('d M Y, H:i') }}
                                                                            </small>
                                                                        </div>
                                                                    </div>
                                                                @empty
                                                                    <p class="text-muted text-center">No messages yet.
                                                                        Start the conversation.</p>
                                                                @endforelse
                                                            </div>
                                                            <form method="POST"
                                                                action="{{ route('agent.applications.addMessage', $app) }}"
                                                                class="mt-3">
                                                                @csrf
                                                                <div class="input-group">
                                                                    <textarea name="message" class="form-control" rows="1" placeholder="Type your message..." required></textarea>
                                                                    <button type="submit" class="btn"
                                                                        style="background: var(--active); color: white;">
                                                                        <i class="fas fa-paper-plane"></i> Send
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5 bg-light rounded-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No applications yet.</p>
                                    <a href="{{ route('agent.applications.create', ['student_id' => $student->id]) }}"
                                        class="btn" style="background: var(--active); color: white;">
                                        Create First Application
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Documents Tab (unchanged but with gradient button) --}}
                        <div class="tab-pane fade" id="documents" role="tabpanel">
                            @php
                                $statusColor = match ($documentStats['status']) {
                                    'Completed' => 'success',
                                    'Incomplete' => 'warning',
                                    default => 'danger',
                                };

                                $icon = match ($documentStats['status']) {
                                    'Completed' => 'fa-check-circle text-success',
                                    'Incomplete' => 'fa-hourglass-half text-warning',
                                    default => 'fa-circle-exclamation text-danger',
                                };
                            @endphp

                            {{-- Header --}}
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                                <h5 class="fw-bold mb-2 mb-sm-0" style="color: var(--primary);">
                                    <i class="fas fa-folder-open me-2"></i> Student Documents
                                </h5>
                                <div class="d-flex flex-column gap-2 mb-4">
                                    @if ($documentStats['status'] === 'Completed')
                                        <a href="{{ route('agent.documents.index', $student->id) }}"
                                            class="btn btn-outline-secondary">
                                            <i class="fas fa-folder-open me-1"></i>+ Add more Docs
                                        </a>
                                    @else
                                    @endif
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid {{ $icon }}"></i>
                                    <span class="text-{{ $statusColor }} fw-bold small">
                                        {{ $documentStats['status'] }}
                                    </span>
                                </div>
                            </div>

                            {{-- Progress Section --}}
                            <div class="d-flex justify-content-between gap-2 mb-4">
                                <div class="col-md-8">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Document Progress</span>
                                        <span>
                                            {{ $documentStats['uploaded_count'] }} / {{ $totalRequiredDocs }}
                                        </span>
                                    </div>
                                    <div class="progress mb-1" style="background-color: var(--warning);">
                                        <div style="background-color: var(--success); width: {{ $documentStats['progress'] }}%;"
                                            aria-valuenow="{{ $documentStats['progress'] }}" height: 100%; border-radius:
                                            10px;">
                                        </div>
                                    </div>
                                </div>
                                {{-- Actions --}}

                                <div class="d-flex flex-column gap-2 mb-4">
                                    @if ($documentStats['status'] === 'Completed')
                                        <a href="{{ route('agent.applications.create', ['student_id' => $student->id]) }}"
                                            class="btn btn-success btn-sm rounded me-2 shadow-sm">
                                            <i class="fas fa-paper-plane me-1"></i> Apply Now
                                        </a>
                                    @else
                                        <a href="{{ route('agent.documents.index', $student->id) }}"
                                            class="btn btn-warning btn-sm">
                                            <i class="fa-solid fa-folder-open"></i> Upload Docs
                                        </a>
                                    @endif
                                </div>
                            </div>

                            @if ($student->documents->isNotEmpty())
                                <div class="row g-4">
                                    @foreach ($student->documents as $doc)
                                        @php
                                            $ext = pathinfo($doc->file_name, PATHINFO_EXTENSION);
                                            $isImage = in_array(strtolower($ext), [
                                                'jpg',
                                                'jpeg',
                                                'png',
                                                'gif',
                                                'webp',
                                            ]);
                                            $fileUrl = Storage::url($doc->file_path);
                                        @endphp
                                        <div class="col-md-4 col-lg-4">
                                            <div class="card h-60 border-0 shadow-sm rounded-4 hover-scale transition">
                                                <a href="{{ $fileUrl }}" class="previewable">
                                                    <div class="position-relative">

                                                        @if ($isImage)
                                                            <img src="{{ $fileUrl }}"
                                                                class="card-img-top rounded-top-4"
                                                                style="height: 160px; object-fit: cover;" alt="Document">
                                                        @else
                                                            <div class="bg-light d-flex align-items-center justify-content-center rounded-top-4"
                                                                style="height: 160px;">
                                                                <i class="fas fa-file-pdf fa-4x text-danger"></i>
                                                            </div>
                                                        @endif
                                                        <div class="position-absolute top-0 end-0 m-2">
                                                            <span class="badge bg-dark">{{ strtoupper($ext) }}</span>
                                                        </div>
                                                    </div>
                                                </a>

                                                <div class="p-2">
                                                    <h6 class="card-title fw-bold">
                                                        {{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}</h6>
                                                    <p class="text-muted small mb-2">
                                                        <i class="far fa-calendar-alt me-1"></i>
                                                        {{ $doc->created_at->format('M d, Y') }}
                                                    </p>
                                                    <div class="d-flex justify-content-between gap-2">
                                                        <a href="{{ route('agent.documents.download', ['student' => $student->id, 'document' => $doc->id]) }}"
                                                            class="btn btn-sm btn-outline-success flex-fill rounded-pill">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        <form
                                                            action="{{ route('agent.documents.destroy', [$student->id, $doc->id]) }}"
                                                            method="POST" class="flex-fill"
                                                            onsubmit="return confirm('Delete this document?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-danger w-100 rounded-pill">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5 bg-light rounded-4">
                                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No documents uploaded yet.</p>
                                    <button class="btn btn-primary rounded-pill" data-bs-toggle="modal"
                                        data-bs-target="#uploadDocumentModal">
                                        Upload First Document
                                    </button>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
