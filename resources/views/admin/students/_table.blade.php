<div class="card border-0 shadow-sm mb-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">

        {{-- LEFT --}}
        <div>
            <h6 class="mb-0 fw-semibold">
                <i class="fa-solid fa-table me-2 text-primary"></i>{{ $title }}
            </h6>

        </div>

        {{-- RIGHT: SORT --}}
        <div class="d-flex align-items-center gap-2">
            <small class="">
                {{ $students->total() }} student{{ $students->total() !== 1 ? 's' : '' }}
            </small>
        </div>

    </div>

    {{-- TABLE --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">
                <tr>
                    <th>Students Id/Name</th>
                    <th>Contact</th>
                    <th>Agent</th>
                    <th>Applications</th>
                    <th>Documents</th>
                    <th class="text-end">Action</th>
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
                    @endphp

                    <tr>
                        {{-- STUDENT --}}
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-wrapper">
                                    <a href="{{ route('admin.students.show', $student) }}">
                                        @if ($student->students_photo && Storage::disk('public')->exists($student->students_photo))
                                            <img src="{{ Storage::url($student->students_photo) }}"
                                                class="student-avatar" alt="Photo">
                                            <div class="overlay">
                                                {{ $student->id }}
                                            </div>
                                        @else
                                            <div class="student-avatar-placeholder">
                                                <i class="fa-solid fa-user text-secondary" style="font-size:20px;"></i>
                                                <div class="overlay">
                                                    {{ $student->id }}
                                                </div>
                                            </div>
                                        @endif
                                    </a>

                                </div>

                                <div>
                                    <a href="{{ route('admin.students.show', $student) }}"
                                        class="fw-semibold text-dark text-decoration-none d-block">
                                        {{ $student->full_name }}
                                    </a>

                                    @if ($student->preferred_country)
                                        <small class="">
                                            <i class="fa-solid fa-location-dot me-1"></i>
                                            {{ $student->preferred_country }}
                                        </small>
                                    @endif
                                </div>

                            </div>
                        </td>

                        {{-- CONTACT --}}
                        <td>
                            @if ($student->email)
                                <a href="mailto:{{ $student->email }}" class="d-block text-decoration-none small">
                                    <i class="fa-solid fa-envelope me-1 "></i>
                                    {{ Str::limit($student->email, 28) }}
                                </a>
                            @else
                                <span class="small ">—</span>
                            @endif

                            @if ($student->phone_number)
                                <a href="tel:{{ $student->phone_number }}"
                                    class="d-block text-decoration-none small mt-1">
                                    <i class="fa-solid fa-phone me-1 "></i>
                                    {{ $student->phone_number }}
                                </a>
                            @endif
                        </td>

                        {{-- AGENT --}}
                        <td>
                            <a href="{{ route('admin.users.show', $student->agent->slug) }}"
                                class="text-decoration-none fw-bold small text-secondary">
                                {{ $student->agent?->business_name ?? ($student->agent?->username ?? '—') }}
                            </a>
                        </td>

                        {{-- LATEST APP --}}
                        <td>
                            @if ($student->applications->count())
                                <div class="d-flex flex-column gap-1">
                                    @foreach ($student->applications as $app)
                                        <a href="{{ route('admin.applications.show', $app) }}"
                                            class="d-flex align-items-center justify-content-between text-decoration-none p-2 rounded-1 border small">
                                            <span
                                                class="badge {{ $app->status_class ?? 'bg-secondary' }} text-truncate">
                                                {{ ucfirst($app->application_status ?? 'Pending') }}
                                            </span>
                                            <span class=" ms-2 text-truncate" style="max-width:100px;">
                                                {{ optional($app->university)->short_name ?? 'N/A' }}
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <span class="badge bg-light  border">Not started</span>
                            @endif
                        </td>

                        {{-- DOCUMENTS --}}
                        <td>
                            <a href="{{ route('admin.documents.index', $student) }}"
                                class="text-decoration-none doc-progress-wrap d-block">

                                <span class="small text-{{ $docStatusColor }} fw-semibold">
                                    {{ $student->document_status }}
                                </span>

                                <div class="doc-progress-bar">
                                    <div class="doc-progress-fill {{ $fillClass }}"
                                        style="width: {{ $student->document_progress }}%"></div>
                                </div>

                                <span class="" style="font-size:.7rem;">
                                    {{ $student->uploaded_count }}/{{ $totalRequiredDocs }}
                                </span>

                            </a>
                        </td>

                        {{-- ACTION --}}

                        <td>
                            <div class="d-flex justify-content-end gap-1">
                                <div>
                                    @if ($student->applications->count())
                                        {{-- Already applied → show applications and download documents --}}
                                        <a href="{{ route('admin.documents.index', $student) }}"
                                            class="btn btn-sm btn-outline-warning m-1">
                                            Download Documents <i class="fa-solid fa-download"></i>
                                        </a>
                                        @if ($student->applications->count() > 1)
                                            <a href="{{ route('admin.students.applications', $student) }}"
                                                class="btn btn-sm btn-outline-success m-1">
                                                Update Status <i class="fa-solid fa-sliders me-1"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('admin.applications.show', $latestApp) }}"
                                                class="btn btn-sm btn-outline-success m-1">
                                                Update Status <i class="fa-solid fa-sliders me-1"></i>
                                            </a>
                                        @endif
                                    @elseif ($student->uploaded_count >= $totalRequiredDocs)
                                        {{-- Docs complete → allow apply --}}
                                        <a href="{{ route('admin.applications.create', $student) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            Add Application <i class="fa-solid fa-paper-plane"></i>
                                        </a>
                                    @else
                                        {{-- Not applied and incomplete docs → prompt to complete --}}
                                        <a href="{{ route('admin.documents.index', $student) }}"
                                            class="btn btn-sm btn-outline-warning">
                                            Complete Documents <i class="fa-solid fa-triangle-exclamation"></i>
                                        </a>
                                    @endif
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border-0" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                        <li><a class="dropdown-item py-2"
                                                href="{{ route('admin.students.show', $student) }}"><i
                                                    class="fas fa-eye me-2"></i> View</a></li>
                                        <li><a class="dropdown-item py-2"
                                                href="{{ route('admin.students.edit', $student) }}"><i
                                                    class="fas fa-edit me-2"></i> Edit</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <button type="button" class="btn btn-sm btn-danger btn-delete"
                                                data-url="{{ route('admin.students.destroy', $student->id) }}"
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
                        <td colspan="8" class="text-center py-5 ">
                            <i class="fa-solid fa-users-slash fa-2x mb-2 d-block"></i>
                            No students found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    @if ($students->hasPages())
        <div class="card-footer bg-white border-top d-flex justify-content-center py-3">
            {{ $students->appends(request()->query())->links() }}
        </div>
    @endif

</div>
