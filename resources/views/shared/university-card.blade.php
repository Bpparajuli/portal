@php
    $prefix = $prefix ?? (request()->routeIs('admin.*') ? 'admin' : (request()->routeIs('agent.*') ? 'agent' : (request()->routeIs('staff.*') ? 'staff' : 'guest')));
    $uni = $university;
@endphp

<div class="uni-card" data-aos="fade-up">
    <div class="uni-card-logo">
        <a href="{{ route($prefix . '.universities.show', $uni->id) }}">
            @if ($uni->university_logo)
                <img src="{{ asset('storage/uni_logo/' . $uni->university_logo) }}"
                     alt="{{ $uni->name }}">
            @else
                <div class="no-logo">
                    <i class="fas fa-university fa-3x text-primary"></i>
                </div>
            @endif
        </a>
    </div>

    <div class="uni-card-content">
        <a href="{{ route($prefix . '.universities.show', $uni->id) }}" class="uni-title-link">
            <h3>{{ Str::limit($uni->name, 45) }}</h3>
        </a>

        <div class="uni-location">
            <i class="fas fa-map-marker-alt text-primary"></i>
            <span>{{ $uni->country }}</span>
            @if ($uni->city)
                <span class="mx-1">•</span>
                <span>{{ $uni->city }}</span>
            @endif
        </div>

        <div class="uni-badges">
            @if ($uni->short_name)
                <span class="badge bg-primary">{{ $uni->short_name }}</span>
            @endif
            <span class="badge bg-secondary">
                <i class="fas fa-book me-1"></i> {{ $uni->courses_count ?? $uni->courses->count() }} Courses
            </span>
        </div>

        @if ($uni->website)
            <a href="{{ $uni->website }}" target="_blank" class="uni-website" rel="noopener">
                <i class="fas fa-globe"></i> Visit Website
                <i class="fas fa-external-link-alt fa-xs ms-1"></i>
            </a>
        @endif

        @auth
            @php $user = auth()->user(); @endphp
            @if($user->is_admin)
            <div class="mt-2 d-flex gap-1">
                @can('update', $uni)
                <a href="{{ route($prefix . '.universities.edit', $uni) }}" class="btn btn-sm btn-outline-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                @endcan
                @can('delete', $uni)
                <x-confirm-delete
                    action="{{ $prefix }}.universities.destroy"
                    :id="$uni->id"
                    label="Delete"
                    title="Delete {{ $uni->name }}?"
                    message="This will permanently delete this university and all its courses."
                    class="btn btn-sm btn-outline-danger"
                />
                @endcan
            </div>
            @elseif($user->is_agent)
            <div class="mt-2 d-flex gap-1">
                <a href="{{ route('agent.applications.quick-start', ['university_id' => $uni->id]) }}"
                   class="btn btn-sm btn-success">
                    <i class="fas fa-paper-plane me-1"></i> Apply for Course
                </a>
            </div>
            @elseif($user->is_staff)
                @can('update', $uni)
                <div class="mt-2 d-flex gap-1">
                    <a href="{{ route($prefix . '.universities.edit', $uni) }}" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
                @endcan
            @endif
        @endauth
    </div>

    <div class="uni-card-footer">
        @if ($uni->courses && $uni->courses->count())
            <button class="btn btn-outline-primary w-60"
                    onclick="openCourseModal({{ $uni->id }})">
                <i class="fas fa-book-open me-2"></i>
                View {{ $uni->courses->count() }} Courses
                <i class="fas fa-arrow-right ms-2"></i>
            </button>
        @else
            <button class="btn btn-secondary w-100" disabled style="opacity: 0.6;">
                <i class="fas fa-times-circle me-2"></i>
                No Courses Available
            </button>
        @endif
    </div>
</div>

{{-- Course Modal --}}
<div id="courseModal{{ $uni->id }}" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-book-open me-2"></i>
                    Programs at {{ $uni->short_name ?? $uni->name }}
                </h5>
                <button type="button" class="btn-close btn-close-white"
                        onclick="closeCourseModal({{ $uni->id }})"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Program Title</th>
                                <th>Duration</th>
                                <th>Annual Fee</th>
                                <th>Intakes</th>
                                <th>English Req</th>
                                <th>Scholarship</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($uni->courses as $course)
                            <tr>
                                <td>
                                    <a href="{{ route($prefix . '.courses.show', $course->id) }}" class="fw-semibold">
                                        {{ $course->course_code }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route($prefix . '.courses.show', $course->id) }}">
                                        {{ Str::limit($course->title, 50) }}
                                    </a>
                                </td>
                                <td>{{ $course->duration ?? 'N/A' }}</td>
                                <td>
                                    @if ($course->fee)
                                        <span class="text-success fw-bold">{{ $course->fee }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $intakes = is_array($course->intakes) ? $course->intakes : explode(',', $course->intakes ?? '');
                                    @endphp
                                    @foreach (array_slice($intakes, 0, 2) as $intake)
                                        <span class="badge bg-info me-1">{{ trim($intake) }}</span>
                                    @endforeach
                                    @if (count($intakes) > 2)
                                        <span class="badge bg-secondary">+{{ count($intakes) - 2 }}</span>
                                    @endif
                                </td>
                                <td>{{ $course->ielts_pte_other_languages ?? 'Contact Us' }}</td>
                                <td>
                                    @if ($course->scholarships)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle"></i> Available
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @auth
                                        @php $u = auth()->user(); @endphp
                                        @if($u->is_admin)
                                            @can('update', $course)
                                            <a href="{{ route($prefix . '.courses.edit', $course) }}" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                                            @endcan
                                        @elseif($u->is_agent)
                                            <a href="{{ route('agent.applications.quick-start', ['course_id' => $course->id]) }}" class="btn btn-sm btn-success"><i class="fas fa-paper-plane"></i></a>
                                        @elseif($u->is_staff)
                                            @can('update', $course)
                                            <a href="{{ route($prefix . '.courses.edit', $course) }}" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                                            @endcan
                                        @endif
                                    @endauth
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> Click on course codes for detailed information
                </small>
                <button type="button" class="btn btn-secondary"
                        onclick="closeCourseModal({{ $uni->id }})">Close</button>
            </div>
        </div>
    </div>
</div>
