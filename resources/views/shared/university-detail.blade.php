@php
    $prefix = $prefix ?? (request()->routeIs('admin.*') ? 'admin' : (request()->routeIs('agent.*') ? 'agent' : (request()->routeIs('staff.*') ? 'staff' : 'guest')));
@endphp

<div class="card shadow-lg border-0 mb-5" data-aos="fade-up">
    <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center">
        <div class="mb-3 mb-md-0">
            <h2 class="fw-bold mb-2 text-primary">
                <i class="fas fa-university me-2"></i> {{ $university->name }}
                @if($university->short_name)
                    <small class="text-muted">({{ $university->short_name }})</small>
                @endif
            </h2>
            <p class="mb-1"><i class="fas fa-globe text-secondary me-2"></i><strong>Country:</strong> {{ $university->country }}</p>
            <p class="mb-1"><i class="fas fa-city text-secondary me-2"></i><strong>City:</strong> {{ $university->city ?? 'N/A' }}</p>
            <p class="mb-1"><i class="fas fa-envelope text-secondary me-2"></i><strong>Email:</strong> {{ $university->contact_email ?? 'N/A' }}</p>
            <p class="mb-1"><i class="fas fa-link text-secondary me-2"></i><strong>Website:</strong>
                <a href="{{ $university->website }}" target="_blank" class="text-decoration-none text-info">{{ $university->website }}</a>
            </p>
            @auth
                @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
                <p class="mb-1"><i class="fas fa-toggle-on text-secondary me-2"></i><strong>Status:</strong>
                    @if($university->is_active ?? true)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </p>
                @endif
            @endauth
            <p class="mt-3 text-muted"><i class="fas fa-info-circle text-secondary me-2"></i>{{ $description ?? ($university->description ?? 'No description available.') }}</p>
        </div>

        <div class="text-center">
            @if($university->university_logo)
                <img src="{{ asset('storage/uni_logo/' . $university->university_logo) }}" alt="University Logo"
                     class="img-fluid rounded shadow-sm border" style="max-height: 130px;">
            @else
                <div class="bg-light text-muted p-4 rounded">
                    <i class="fas fa-university fa-3x"></i>
                    <p class="mt-2 small">No Logo</p>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="card shadow-lg border-0 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-secondary mb-0">
            <i class="fas fa-book-open me-2"></i> Courses Offered
        </h3>
        @auth
            @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
            <a href="{{ route('admin.courses.create', ['university_id' => $university->id]) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Course
            </a>
            @endif
        @endauth
    </div>

    @if($university->courses->count())
    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle shadow-sm">
            <thead class="table-dark text-center">
                <tr>
                    <th>Course Code</th>
                    <th>Title</th>
                    <th>Duration</th>
                    <th>Fee</th>
                    <th>Intakes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="text-center">
                @foreach($university->courses as $course)
                <tr>
                    <td>{{ $course->course_code ?? '—' }}</td>
                    <td>
                        <a href="{{ route($prefix . '.courses.show', $course->id) }}" class="fw-semibold">
                            {{ $course->title }}
                        </a>
                    </td>
                    <td>{{ $course->duration ?? '—' }}</td>
                    <td>@if($course->fee)<span class="text-success fw-bold">{{ $course->fee }}</span>@else <span class="text-muted">—</span>@endif</td>
                    <td>
                        @php
                            $intakeList = is_array($course->intakes) ? $course->intakes : explode(',', $course->intakes ?? '');
                        @endphp
                        @foreach(array_slice($intakeList, 0, 2) as $intake)
                            <span class="badge bg-info me-1">{{ trim($intake) }}</span>
                        @endforeach
                        @if(count($intakeList) > 2)
                            <span class="badge bg-secondary">+{{ count($intakeList) - 2 }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="{{ route($prefix . '.courses.show', $course->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                            @auth
                                @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
                                    <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @elseif(auth()->user()->role === 'staff')
                                    <a href="{{ route('staff.courses.edit', $course->id) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                            @endauth
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="alert alert-info shadow-sm">
        <i class="fas fa-exclamation-circle me-2"></i> No courses found for this university.
    </div>
    @endif
</div>
