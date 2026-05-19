@extends('layouts.admin')

@section('admin-content')
    <style>
        /* Statistics Cards */
        .stat-card {
            padding: 1.25rem;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s;
            font-weight: 800;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stat-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-top: 0.5rem;
        }

        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }

        /* Avatar Placeholder */
        .avatar-placeholder {
            width: 40px;
            height: 40px;
            background: var(--active);
            border-radius: 10%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
        }

        /* Overlay */
        .avatar-wrapper {
            position: relative;
            display: inline-block;
        }

        .avatar-wrapper img,
        .avatar-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
            position: relative;
        }

        /* ID Overlay */

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;

            background: rgba(0, 0, 0, 0.6);
            color: #fff;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 14px;
            font-weight: 600;
            border-radius: 10%;

            opacity: 0;
            transition: opacity 0.3s ease;
        }

        /* Show on hover */
        .avatar-wrapper:hover .overlay {
            opacity: 1;
        }

        /* Dropdown Styling */
        .dropdown-toggle {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #495057;
            font-size: 0.85rem;
            padding: 0.35rem 0.75rem;
        }

        .dropdown-toggle:hover {
            background-color: #e9ecef;
            color: #212529;
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            cursor: pointer;
        }

        .dropdown-item i {
            width: 20px;
        }

        .dropdown-item:hover {
            background-color: #c0c0c0;
        }


        /* Responsive */
        @media (max-width: 768px) {
            .stat-number {
                font-size: 1.5rem;
            }

            .stat-icon {
                font-size: 1.8rem;
            }

            .dropdown-toggle {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            .dropdown-item {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }
        }
    </style>
    <div class="container-fluid px-4 py-4">
        {{-- Header Section --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">📋 Applications Management</h2>
                <p class="text-muted mb-0">View and manage all student applications</p>
            </div>
            <a class="btn btn-primary px-4" href="{{ route('admin.applications.create') }}">
                <i class="fas fa-plus me-2"></i>New Application
            </a>
        </div>

        {{-- Status Filter Cards --}}
        <div class="row g-3 mb-4">

            {{-- Total Applications --}}
            <div class="col-md-3">
                <a href="{{ route('admin.applications.index') }}" class="text-decoration-none">
                    <div class="stat-card bg-soft-secondary {{ !request('status') ? 'border border-2 border-dark' : '' }}">
                        <div>
                            <span class="stat-label">Total Applications</span>
                            <h2 class="stat-number mb-0">{{ $applications->total() }}</h2>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Approved --}}
            <div class="col-md-3">
                <a href="{{ route('admin.applications.index', ['status' => 14]) }}" class="text-decoration-none">
                    <div
                        class="stat-card bg-soft-success {{ request('status') == 14 ? 'border border-2 border-dark' : '' }}">
                        <div>
                            <span class="stat-label">Approved</span>
                            <h2 class="stat-number mb-0">{{ $acceptedCount }}</h2>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Rejected --}}
            <div class="col-md-3">
                <a href="{{ route('admin.applications.index', ['status' => 15]) }}" class="text-decoration-none">
                    <div
                        class="stat-card bg-soft-danger {{ request('status') == 15 ? 'border border-2 border-dark' : '' }}">
                        <div>
                            <span class="stat-label">Rejected</span>
                            <h2 class="stat-number mb-0">{{ $rejectedCount }}</h2>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Lost --}}
            <div class="col-md-3">
                <a href="{{ route('admin.applications.index', ['status' => 18]) }}" class="text-decoration-none">
                    <div
                        class="stat-card bg-soft-warning {{ request('status') == 18 ? 'border border-2 border-dark' : '' }}">
                        <div>
                            <span class="stat-label">Lost</span>
                            <h2 class="stat-number mb-0">{{ $lostCount }}</h2>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </a>
            </div>

        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('admin.students.index') }}" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold mb-1">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="form-control form-control-sm" placeholder="Name or email…" id="searchInput"
                                onkeyup="debounceSubmit()">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Applied Country</label>
                            <select name="applied_country" class="form-select form-select-sm"
                                onchange="submitWithSingleFilter('applied_country', this.value)" id="appliedCountrySelect">
                                <option value="">Applied Countries ({{ $countries->count() }})</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country->name }}"
                                        {{ request('applied_country') == $country->name ? 'selected' : '' }}>
                                        {{ $country->name }} ({{ $country->count }} apps)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Applied University</label>
                            <select name="university" class="form-select form-select-sm"
                                onchange="submitWithSingleFilter('university', this.value)" id="universitySelect">
                                <option value="">All Universities ({{ $universities->count() }})</option>
                                @foreach ($universities as $uni)
                                    <option value="{{ $uni->id }}"
                                        {{ request('university') == $uni->id ? 'selected' : '' }}>
                                        {{ $uni->name }}– {{ $uni->city }} ({{ $uni->applications_count }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Agent</label>
                            <select name="agent" class="form-select form-select-sm"
                                onchange="submitWithSingleFilter('agent', this.value)" id="agentSelect">
                                <option value="">Applying Agents ({{ $agents->count() }})</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}"
                                        {{ request('agent') == $agent->id ? 'selected' : '' }}>
                                        {{ $agent->business_name ?? $agent->username }}
                                        ({{ $agent->students_count }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Application Status</label>
                            <select name="status" class="form-select form-select-sm"
                                onchange="submitWithSingleFilter('status', this.value)" id="statusSelect">
                                <option value="">All Status ({{ $statuses->count() }})</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}"
                                        {{ request('status') == $status->id ? 'selected' : '' }}>
                                        {{ $status->name }} ({{ $status->applications_count }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-1">
                            @if (request()->hasAny(['search', 'status', 'agent', 'university', 'applied_country']))
                                <a href="{{ request()->url() }}" class="btn btn-sm btn-outline-danger w-100"
                                    onclick="return clearAllFilters()">
                                    Clear Filters
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Preserve quick_filter in filter form --}}
                    @if (request('quick_filter'))
                        <input type="hidden" name="quick_filter" value="{{ request('quick_filter') }}">
                    @endif
                </form>
            </div>
        </div>

        {{-- Applications Table --}}
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Profile</th>
                                <th>Student/Agent</th>
                                <th>Course & University</th>
                                <th>Status</th>
                                <th>SOP</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applications as $app)
                                <tr>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-wrapper">
                                                <a href="{{ route('admin.students.show', $app->student->id) }}">
                                                    @if ($app->student->students_photo && Storage::disk('public')->exists($app->student->students_photo))
                                                        <img src="{{ Storage::url($app->student->students_photo) }}"
                                                            class="rounded" width="40" height="40"
                                                            style="object-fit: cover;">
                                                        <div class="overlay">
                                                            {{ $app->student->id }}
                                                        </div>
                                                    @else
                                                        <div class="avatar-placeholder me-2">
                                                            <i class="fas fa-user rounded"></i>
                                                            <div class="overlay">
                                                                {{ $app->student->id }}
                                                            </div>
                                                        </div>
                                                    @endif
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.students.show', $app->student->id) }}"
                                            class="text-decoration-none fw-semibold text-dark">
                                            {{ $app->student->first_name }} {{ $app->student->last_name }}
                                        </a>
                                        <br>
                                        <small class="text-muted">{{ $app->student->email }}</small>
                                        <br>
                                        <i class="fas fa-user-tie text-primary me-1"></i>
                                        <a href="{{ route('admin.users.show', $app->agent->id) }}">
                                            {{ $app->agent->business_name ?? $app->agent->username }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $app->course->title ?? 'N/A' }}</div>
                                        <small class="text-muted">
                                            <i class="fas fa-university me-1"></i>
                                            {{ $app->university->name ?? 'N/A' }}
                                        </small>
                                    </td>

                                    <td>
                                        <a href="{{ route('admin.applications.show', $app->id) }}">
                                            <span class="status-badge"
                                                style="background: {{ $app->status?->bg_color ?? '#6c757d' }}; color: {{ $app->status?->text_color ?? '#6c757d' }};">
                                                {{ $app->status?->name ?? 'N/A' }}
                                            </span>
                                        </a>
                                        <br>
                                        <span>Started at : {{ $app->created_at->format('d M Y') }}</span>
                                    </td>
                                    <td>
                                        @if ($app->sop_file)
                                            <button type="button" class="btn btn-sm btn-outline-primary view-sop"
                                                data-sop-url="{{ Storage::url($app->sop_file) }}"
                                                data-student-name="{{ $app->student->first_name }} {{ $app->student->last_name }}">
                                                <i class="fas fa-file-pdf me-1"></i>View
                                            </button>
                                        @else
                                            <span class="text-muted">No SOP</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-cog me-1"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('admin.applications.show', $app->id) }}">
                                                        <i class="fas fa-eye text-info me-2"></i> View Application
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('admin.applications.edit', $app->id) }}">
                                                        <i class="fas fa-edit text-warning me-2"></i> Edit Application
                                                    </a>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item btn-delete"
                                                        data-url="{{ route('admin.applications.destroy', $app->id) }}"
                                                        data-name="Application of {{ $app->student->first_name }} {{ $app->student->last_name }} on {{ $app->university->name }}">
                                                        <i class="fas fa-trash text-danger me-2"></i> Delete Application
                                                    </button>
                                                </li>
                                                @if ($app->sop_file)
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <button type="button" class="dropdown-item view-sop"
                                                            data-sop-url="{{ Storage::url($app->sop_file) }}"
                                                            data-student-name="{{ $app->student->first_name }} {{ $app->student->last_name }}">
                                                            <i class="fas fa-file-alt text-primary me-2"></i> View SOP
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ Storage::url($app->sop_file) }}" download>
                                                            <i class="fas fa-download text-success me-2"></i> Download SOP
                                                        </a>
                                                    </li>
                                                @endif
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('admin.students.show', $app->student->id) }}">
                                                        <i class="fas fa-user-graduate me-2"></i> View Student Profile
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="mailto:{{ $app->student->email }}">
                                                        <i class="fas fa-envelope me-2"></i> Send Email
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No applications found</h5>
                                        <p class="text-muted">Click the "New Application" button to get started.</p>
                                        <a href="{{ route('admin.applications.create') }}" class="btn btn-primary mt-2">
                                            <i class="fas fa-plus me-2"></i>Create Application
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($applications->hasPages())
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-center">
                        {{ $applications->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        // Auto-submit form when any select filter changes
        document.querySelectorAll(
            'select[name="status_filter"], select[name="university_filter"], select[name="agent_filter"], select[name="country_filter"]'
        ).forEach(select => {
            select.addEventListener('change', function() {
                this.closest('form').submit();
            });
        });

        // Optional: Debounced search input
        let searchTimeout;
        document.querySelector('input[name="search"]').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.closest('form').submit();
            }, 500);
        });
    </script>
@endsection
