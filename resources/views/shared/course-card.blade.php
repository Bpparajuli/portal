@php
    $prefix = $prefix ?? (request()->routeIs('admin.*') ? 'admin' : (request()->routeIs('agent.*') ? 'agent' : (request()->routeIs('staff.*') ? 'staff' : 'guest')));
    $course = $courseItem ?? ($course ?? null);
@endphp

@if($course)
<div class="card course-card shadow-sm h-100 border-0" data-aos="fade-up">
    <div class="card-body d-flex flex-column p-3">
        <div class="d-flex align-items-start gap-3 mb-3">
            @if($course->university && $course->university->university_logo)
                <img src="{{ asset('storage/uni_logo/' . $course->university->university_logo) }}"
                     alt="{{ $course->university->name }}"
                     class="course-uni-logo border">
            @else
                <div class="course-uni-logo bg-light d-flex align-items-center justify-content-center border rounded">
                    <i class="fas fa-university text-muted"></i>
                </div>
            @endif
            <div class="min-w-0" style="flex:1;min-width:0;">
                <h5 class="card-title mb-1">{{ $course->title }}</h5>
                @if($course->university)
                    <small class="text-muted">{{ $course->university->name }}</small>
                @endif
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-light text-dark border">{{ $course->course_type }}</span>
            @if($course->duration)
                <span class="badge bg-light text-dark border"><i class="far fa-clock me-1"></i>{{ $course->duration }}</span>
            @endif
            @if($course->fee)
                <span class="badge bg-light text-dark border text-truncate" style="max-width:100%;overflow-wrap:break-word;"><i class="fas fa-tag me-1"></i>{{ $course->fee }}</span>
            @endif
        </div>
        @if($course->description)
            <p class="card-text small text-muted flex-grow-1" style="font-size:0.8rem;line-height:1.5;">{{ Str::limit($course->description, 120) }}</p>
        @endif

        @auth
            @php $user = auth()->user(); @endphp
            @if($user->is_admin)
            <div class="d-flex gap-1 mb-2 flex-wrap">
                <a href="{{ route($prefix . '.courses.edit', $course) }}" class="btn btn-sm btn-outline-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                @can('delete', $course)
                <x-confirm-delete
                    action="{{ $prefix }}.courses.destroy"
                    :id="$course->id"
                    label="Delete"
                    title="Delete {{ $course->course_code }}?"
                    message="This action cannot be undone."
                    class="btn btn-sm btn-outline-danger"
                />
                @endcan
            </div>
            @elseif($user->is_agent)
            <div class="d-flex gap-1 mb-2">
                <a href="{{ route('agent.applications.quick-start', ['course_id' => $course->id]) }}"
                   class="btn btn-sm btn-success">
                    <i class="fas fa-paper-plane me-1"></i>Apply
                </a>
            </div>
            @elseif($user->is_staff)
                @can('update', $course)
                <div class="d-flex gap-1 mb-2">
                    <a href="{{ route($prefix . '.courses.edit', $course) }}" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
                @endcan
            @endif
        @endauth

        <a href="{{ route($prefix . '.courses.show', $course->id) }}" class="btn btn-primary btn-sm mt-auto align-self-start">
            <i class="fas fa-info-circle me-1"></i>View Details
        </a>
    </div>
</div>
@endif
