@props([
    'student',
    'routePrefix' => 'admin',
    'totalRequiredDocs' => 0,
    'isAgent' => false,
    'isStaff' => false,
    'isMgmt' => true,
])

@php
    $rolePrefix = $isAgent ? 'agent' : ($isStaff ? 'staff' : 'admin');
    $latestApp = $student->applications()->with('status', 'university')->latest()->first();
    $applications = $student->applications;
    $docStatus = $student->document_status ?? 'Not Uploaded';
    $docStatusColor = match ($docStatus) {
        'Completed' => 'success',
        'Incomplete' => 'warning',
        default => 'danger',
    };
    $docProgress = $student->document_progress ?? 0;
    $uploadedCount = $student->uploaded_count ?? 0;
@endphp

<tr class="datatable-row">
    <td>
        <div class="d-flex align-items-center gap-2">
            <div class="avatar-wrap">
                <a href="{{ route($routePrefix . '.show', $student) }}">
                    @if ($student->students_photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($student->students_photo))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($student->students_photo) }}"
                            class="student-avatar-sm" alt="">
                    @else
                        <div class="student-avatar-sm d-flex">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    @endif
                </a>
            </div>
            <div class="min-w-0">
                <div class="student-name-row">
                    <a href="{{ route($routePrefix . '.show', $student) }}"
                        class="fw-semibold text-dark text-decoration-none text-truncate">
                        {{ $student->full_name }}
                    </a>
                    @unless($isAgent ?? false)<span class="student-id-label">{{ $student->id }}</span>@endunless
                </div>
                @if ($student->preferred_country)
                    <span class="text-muted d-block" style="font-size:0.66rem;">
                        <i class="fa-solid fa-location-dot me-1"
                            style="font-size:0.6rem;"></i>{{ $student->preferred_country }}
                    </span>
                @endif
            </div>
        </div>
    </td>

    <td>
        @if ($student->email)
            <a href="mailto:{{ $student->email }}"
                class="d-inline-flex align-items-center gap-1 text-muted text-decoration-none small mb-1">
                <i class="fa-solid fa-envelope" style="font-size:0.6rem;"></i>
                <span class="text-truncate d-inline-block" style="max-width:140px;">{{ $student->email }}</span>
            </a>
            <br>
        @endif
        @if ($student->phone_number)
            <a href="tel:{{ $student->phone_number }}"
                class="d-inline-flex align-items-center gap-1 text-muted text-decoration-none small">
                <i class="fa-solid fa-phone" style="font-size:0.6rem;"></i>
                <span>{{ $student->phone_number }}</span>
            </a>
        @endif
    </td>

    @if (!$isAgent)
        <td>
            @if ($student->agent)
                <a href="{{ route($rolePrefix . '.users.show', $student->agent->slug ?? $student->agent->id) }}"
                    class="fw-medium text-decoration-none small" style="color:var(--primary);">
                    {{ $student->agent->business_name ?? ($student->agent->name ?? '—') }}
                </a>
            @else
                <span class="text-muted small">—</span>
            @endif
        </td>
    @endif

    <td>
        @if ($applications->count())
            @foreach ($applications as $app)
                <a href="{{ route($rolePrefix . '.applications.show', $app) }}" class="d-inline-block mb-1">
                    <span class="app-status-badge"
                        style="background:{{ $app->status?->bg_color ?? '#6c757d' }}18;color:{{ $app->status?->bg_color ?? '#6c757d' }};border-color:{{ $app->status?->bg_color ?? '#6c757d' }}25;">
                        {{ $app->university?->short_name ?? '' }} {{ $app->status?->name ?? 'N/A' }}
                    </span>
                </a>
            @endforeach
        @else
            <span class="app-status-badge bg-light text-muted border-0">Not Applied</span>
        @endif
    </td>

    @if (!$isStaff)
        <td>
            <a href="{{ route($rolePrefix . '.documents.index', $student) }}" class="text-decoration-none d-block">
                <span class="fw-semibold small text-{{ $docStatusColor }}">{{ $docStatus }}</span>
                <div class="doc-progress-track">
                    <div class="doc-progress-fill bg-{{ $docStatusColor }}" style="width:{{ $docProgress }}%;"></div>
                </div>
                <span class="small"
                    style="font-size:0.62rem;color:var(--text-muted);">{{ $uploadedCount }}/{{ $totalRequiredDocs }}</span>
            </a>
        </td>
    @endif

    <td class="text-end">
        <div class="action-group">
            @if ($isAgent)
                @if ($applications->count())
                    <a href="{{ route($routePrefix . '.applications', $student->id) }}" class="action-icon"
                        title="View Applications">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                @elseif ($uploadedCount >= $totalRequiredDocs)
                    <a href="{{ route('agent.applications.create', ['student_id' => $student->id]) }}"
                        class="action-icon text-primary" title="Create Application">
                        <i class="fa-solid fa-paper-plane"></i>
                    </a>
                @else
                    <a href="{{ route($rolePrefix . '.documents.index', $student) }}" class="action-icon text-warning"
                        title="Upload Docs">
                        <i class="fa-solid fa-upload"></i>
                    </a>
                @endif
            @elseif($isStaff)
                <a href="{{ route($routePrefix . '.show', $student) }}" class="action-icon" title="View">
                    <i class="fa-solid fa-eye"></i>
                </a>
            @else
                @if ($applications->count())
                    <a href="{{ route($rolePrefix . '.applications.show', $latestApp) }}"
                        class="action-icon text-success" title="View Application">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                @elseif ($uploadedCount >= $totalRequiredDocs)
                    <a href="{{ route($rolePrefix . '.applications.create', ['student_id' => $student->id]) }}"
                        class="action-icon text-primary" title="Create Application">
                        <i class="fa-solid fa-paper-plane"></i>
                    </a>
                @else
                    <a href="{{ route($rolePrefix . '.documents.index', $student) }}" class="action-icon text-warning"
                        title="Upload Docs">
                        <i class="fa-solid fa-upload"></i>
                    </a>
                @endif
            @endif

            <div class="dropdown d-inline-block">
                <button class="action-dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"
                    title="More actions">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 small py-1">
                    <li>
                        <a class="dropdown-item py-1" href="{{ route($routePrefix . '.show', $student) }}">
                            <i class="fas fa-eye me-2 text-info"></i>View
                        </a>
                    </li>
                    @if (!$isStaff)
                        <li>
                            <a class="dropdown-item py-1" href="{{ route($routePrefix . '.edit', $student) }}">
                                <i class="fas fa-edit me-2 text-warning"></i>Edit
                            </a>
                        </li>
                    @endif
                    @can('delete', $student)
                        <li>
                            <hr class="dropdown-divider my-1">
                        </li>
                        <li>
                            <x-confirm-delete url="{{ route($routePrefix . '.destroy', $student->id) }}" label="Delete"
                                title="Delete {{ $student->full_name }}?"
                                message="This will permanently delete this student and all associated data."
                                class="dropdown-item text-danger py-1" />
                        </li>
                    @endcan
                </ul>
            </div>
        </div>
    </td>
</tr>
