@extends('layouts.agent')

@section('title', 'Student Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/students.css') }}">
@endpush
@section('agent-content')

    <div class="students-page">
        {{-- ================================================================ --}}
        {{-- Page Header --}}
        {{-- ================================================================ --}}
        <div class="page-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h1 class="page-title mb-0">
                    <i class="fa-solid fa-users me-2"></i> Student Management
                </h1>
                <p class="text-muted mb-0 mt-1 small">Manage your student pipeline</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" id="exportBtn">
                    <i class="fa-solid fa-download me-1"></i> Export
                </button>
                <a href="{{ route('agent.students.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus me-1"></i> Add Student
                </a>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- Stats Cards --}}
        {{-- ================================================================ --}}
        <div class="row g-3 mb-4">
            @foreach ([['label' => 'Total Students', 'value' => $totalStudents, 'icon' => 'fa-users', 'color' => 'primary'], ['label' => 'Total Applied', 'value' => $totalApplied, 'icon' => 'fa-paper-plane', 'color' => 'info'], ['label' => 'Admitted/Enrolled', 'value' => $admittedEnrolled, 'icon' => 'fa-user-graduate', 'color' => 'success'], ['label' => 'Docs Complete', 'value' => $documentCompleted, 'icon' => 'fa-file-alt', 'color' => 'warning']] as $stat)
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 h-100 shadow-sm hover-lift">
                        <div class="card-body d-flex align-items-center gap-3 p-3">
                            <div class="rounded-3 p-3" style="background: rgba(var(--bs-{{ $stat['color'] }}-rgb), 0.1);">
                                <i class="fas {{ $stat['icon'] }} fa-lg" style="color: var(--bs-{{ $stat['color'] }});"></i>
                            </div>
                            <div>
                                <div class="text-muted small">{{ $stat['label'] }}</div>
                                <div class="fs-4 fw-bold">{{ number_format($stat['value']) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ================================================================ --}}
        {{-- Filters --}}
        {{-- ================================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('agent.students.index') }}" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold mb-1">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="form-control form-control-sm" placeholder="Name, email, phone…">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Country</label>
                            <select name="country" class="form-select form-select-sm">
                                <option value="">All Countries</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country }}"
                                        {{ request('country') == $country ? 'selected' : '' }}>
                                        {{ $country }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">University</label>

                            <select name="university" class="form-select form-select-sm">
                                <option value=""> Applied Universities-{{ $universities->count() }}
                                </option>

                                @foreach ($universities as $uni)
                                    <option value="{{ $uni->id }}">
                                        {{ $uni->name }}– {{ $uni->city }} ({{ $uni->applications_count }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">App Status</label>
                            <select name="application_status" class="form-select form-select-sm">
                                <option value="">All</option>
                                @foreach ($applicationStatuses as $status)
                                    <option value="{{ $status }}"
                                        {{ request('application_status') == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Doc Status</label>
                            <select name="document_status" class="form-select form-select-sm">
                                <option value="">All</option>
                                @foreach (['Not Uploaded', 'Incomplete', 'Completed'] as $ds)
                                    <option value="{{ $ds }}"
                                        {{ request('document_status') == $ds ? 'selected' : '' }}>
                                        {{ $ds }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                            <a href="{{ route('agent.students.index') }}" class="btn btn-outline-danger btn-sm">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- Table --}}
        {{-- ================================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <div>
                    <h6 class="mb-0 fw-semibold">All Students</h6>
                    <small class="text-muted">
                        Showing {{ $students->firstItem() ?? 0 }}–{{ $students->lastItem() ?? 0 }}
                        of {{ $students->total() }}
                    </small>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Contact</th>
                            <th>Application status </th>
                            <th>Documents</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $student)
                            @php
                                $latestApp = $student->applications->sortByDesc('created_at')->first();
                                $docStatusColor = match ($student->document_status) {
                                    'Completed' => 'success',
                                    'Incomplete' => 'warning',
                                    default => 'danger',
                                };
                                $fillClass = 'fill-' . $docStatusColor;
                                $applications = $student->applications->sortByDesc('created_at');
                            @endphp
                            <tr>
                                {{-- Student profile --}}
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <a href="{{ route('agent.students.show', $student) }}">
                                            @if ($student->students_photo && Storage::disk('public')->exists($student->students_photo))
                                                <img src="{{ Storage::url($student->students_photo) }}"
                                                    class="rounded object-fit-cover" width="50" height="50"
                                                    alt="Photo">
                                            @else
                                                <div class="rounded bg-primary d-flex align-items-center justify-content-center"
                                                    style="width:50px;height:50px;">
                                                    <i class="fa-solid fa-user text-white"></i>
                                                </div>
                                            @endif
                                        </a>
                                        <div>
                                            <a href="{{ route('agent.students.show', $student) }}"
                                                class="fw-semibold text-dark text-decoration-none d-block">
                                                {{ $student->full_name }}
                                            </a>
                                            <small class="text-muted">
                                                <i class="fa-solid fa-location-dot me-1"></i>
                                                {{ $student->preferred_country ?? '—' }}
                                                @if ($student->preferred_city)
                                                    · {{ $student->preferred_city }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                {{-- Contact --}}
                                <td>
                                    @if ($student->email)
                                        <a href="mailto:{{ $student->email }}" class="d-block text-decoration-none small">
                                            <i class="fa-solid fa-envelope me-1 text-muted"></i>
                                            {{ Str::limit($student->email, 28) }}
                                        </a>
                                    @else
                                        <span class="small text-muted"><i class="fa-solid fa-envelope me-1"></i>—</span>
                                    @endif

                                    @if ($student->phone_number)
                                        <a href="tel:{{ $student->phone_number }}"
                                            class="d-block text-decoration-none small mt-1">
                                            <i class="fa-solid fa-phone me-1 text-muted"></i>
                                            {{ $student->phone_number }}
                                        </a>
                                    @else
                                        <span class="small text-muted mt-1 d-block"><i
                                                class="fa-solid fa-phone me-1"></i>—</span>
                                    @endif
                                </td>

                                {{-- Applications --}}
                                <td>
                                    @if ($applications->count())
                                        <div class="d-flex flex-column gap-1">
                                            @foreach ($applications as $app)
                                                <a href="{{ route('agent.applications.show', $app) }}"
                                                    class="d-flex align-items-center justify-content-between text-decoration-none p-2 rounded-1 border small">
                                                    <span
                                                        class="badge {{ $app->status_class ?? 'bg-secondary' }} text-truncate">
                                                        {{ ucfirst($app->application_status ?? 'Pending') }}
                                                    </span>
                                                    <span class="text-muted ms-2 text-truncate" style="max-width:100px;">
                                                        {{ optional($app->university)->short_name ?? 'N/A' }}
                                                    </span>
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="badge bg-light text-muted border">Not started</span>
                                    @endif
                                </td>

                                {{-- Documents --}}
                                <td>
                                    <a href="{{ route('agent.documents.index', $student) }}"
                                        class="text-decoration-none doc-progress-wrap d-block">
                                        <span class="small text-{{ $docStatusColor }} fw-semibold">
                                            {{ $student->document_status }}
                                        </span>
                                        <div class="doc-progress-bar">
                                            <div class="doc-progress-fill {{ $fillClass }}"
                                                style="width: {{ $student->document_progress }}%"></div>
                                        </div>
                                        <span class="text-muted" style="font-size:.7rem;">
                                            {{ $student->uploaded_count }}/{{ $totalRequiredDocs }}
                                        </span>
                                    </a>
                                </td>

                                {{-- Actions --}}
                                <td>
                                    <div class="d-flex justify-content-end gap-1">
                                        <div>
                                            @if ($applications->count())
                                                {{-- Already applied → show view applications --}}
                                                <div class="d-flex flex-column gap-1">
                                                    <a href="{{ route('agent.students.applications', $student->id) }}"
                                                        class="btn btn-sm btn-outline-primary"
                                                        title="View Applications">View Applications</a>
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
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                                <li><a class="dropdown-item py-2"
                                                        href="{{ route('agent.students.show', $student) }}"><i
                                                            class="fas fa-eye me-2"></i> View</a></li>
                                                <li><a class="dropdown-item py-2"
                                                        href="{{ route('agent.students.edit', $student) }}"><i
                                                            class="fas fa-edit me-2"></i> Edit</a></li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <button type="button" class="btn btn-sm btn-danger btn-delete"
                                                        data-url="{{ route('agent.students.destroy', $student->id) }}"
                                                        data-name="{{ $student->first_name }} {{ $student->last_name }}">
                                                        <i class="fa-solid fa-trash"></i>Delete Student
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="fa-solid fa-users-slash fa-3x text-muted mb-3 d-block"></i>
                                    <h6 class="text-muted">No students found</h6>
                                    <a href="{{ route('agent.students.create') }}" class="btn btn-primary btn-sm mt-2">
                                        <i class="fa-solid fa-plus me-1"></i> Add First Student
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($students->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-center py-3">
                    {{ $students->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Loading overlay --}}
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

@endsection


@push('scripts')
    <script>
        document.getElementById('filterForm').addEventListener('submit', function() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        });

        document.getElementById('exportBtn').addEventListener('click', function() {
            const params = new URLSearchParams(new FormData(document.getElementById('filterForm'))).toString();
            window.location.href = '{{ route('agent.students.index') }}?' + params;
        });
    </script>
@endpush
