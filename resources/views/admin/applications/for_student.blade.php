@extends('layouts.admin')

@section('admin-content')

<style>
    .app-card {
        border-radius: 14px;
        padding: 22px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        transition: 0.2s ease;
    }

    .app-card:hover {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
    }

    .sop-preview-box {
        width: auto;
        height: 300px;
        overflow: hidden;
        border-radius: 8px;
        border: 1px solid #ddd;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fafafa;
    }

</style>

<div class="container py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">🎓 List of applications</h4>
            <p class="text-muted mb-0">
                For {{ $student->first_name }} {{ $student->last_name }}
            </p>
        </div>

        <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary btn-sm">
            ← Back to Students
        </a>
    </div>

    @if($student->applications->count())

    <div class="row g-3">
        @foreach($student->applications as $application)
        <div class="col-lg-6">
            <div class="app-card">
                {{-- University + Course --}}
                <div class="p-3">
                    <h5 class="fw-bold mb-1 text-primary">
                        {{ $application->university->name ?? 'N/A' }}
                    </h5>
                    <p>
                        <span class="text-mute fw-semibold"> {{ $application->university->city ?? 'N/A' }}</span> -
                        <span class="text-mute fw-semibold"> {{ $application->university->country ?? 'N/A' }}</span>

                    </p>
                    <p class="mb-0">
                        <span class="text-dark fw-semibold">{{ $application->course->title ?? 'N/A' }}</span>
                    </p>
                    <span class="badge bg-info text-dark p-3 mt-2">
                        {{ ucfirst($application->application_status ?? 'N/A') }}
                    </span>
                </div>

                <hr>

                {{-- SOP --}}
                <div class="">
                    <label class="fw-semibold">📑 SOP</label>

                    @if($application->sop_file)
                    <div class="gap-3 mt-2 align-items-center">
                        <div class="sop-preview-box">
                            <iframe src="{{ asset('storage/' . $application->sop_file) }}" style="width:100%; height:100%; border:none;" loading="lazy"></iframe>
                        </div>

                        @php
                        $ext = strtoupper(pathinfo($application->sop_file, PATHINFO_EXTENSION));
                        @endphp
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="fw-semibold mb-1">{{ basename($application->sop_file) }}</p>
                                <p class="small text-muted mb-1">{{ $ext }}</p>
                            </div>
                            {{-- Submitted Info --}}
                            <div>
                                <p class="fw-semibold mb-1">📅 Submitted on</p>
                                <p class="small text-muted mb-1"> {{ $application->created_at->format('d M, Y') }}
                                </p>
                            </div>
                        </div>
                        <div class="text-center">
                            <a href="#" data-preview="{{ asset('storage/' . $application->sop_file) }}" target="_blank" class="small border border-primary p-2 rounded text-primary">Open File →</a>
                        </div>
                    </div>
                    @else
                    <p class="text-danger small mt-2">⚠️ SOP not uploaded</p>
                    @endif
                </div>

                <hr>

                {{-- Application status update --}}

                <div class="p-3">
                    <h5 class="fw-bold mb-3">🛠 Update Application Status</h5>
                    <form action="{{ route('admin.applications.update', $application->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="university_id" value="{{ $application->university_id }}">
                        <input type="hidden" name="course_id" value="{{ $application->course_id }}">
                        <div class="row align-items-end">
                            <div class="col-md-8">
                                <select name="application_status" class="form-select">
                                    @foreach(\App\Models\Application::STATUSES as $status)
                                    <option value="{{ $status }}" {{ $application->application_status == $status ? 'selected' : '' }}>
                                        {{ $status }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mt-2 mt-md-0">
                                <button type="submit" class="btn btn-success w-100">Update Status</button>
                            </div>
                        </div>
                    </form>
                </div>
                <hr>

                {{-- Actions --}}
                <div class="p-3 d-flex justify-content-between">
                    <div>
                        <a href="{{ route('admin.applications.edit', $application->id) }}" class="p-2 btn btn-sm btn-dark p-1">
                            <i class="fa-solid fa-pencil me-1"></i> edit Application
                        </a>
                    </div>
                    <div>
                        <a href="{{ route('admin.applications.show', $application->id) }}" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-eye me-1"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @else
    <div class="alert alert-info">No applications found for this student.</div>
    @endif

</div>

@endsection
