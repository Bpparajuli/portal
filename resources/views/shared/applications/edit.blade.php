@php
    $__user = auth()->user();
    $user = $__user;
    $__isAgent = $__user->is_agent;
    $__isStaff = $__user->is_staff && !$__user->is_admin_staff;
    $__layout = $__isAgent ? 'layouts.agent' : ($__isStaff ? 'layouts.staff' : 'layouts.admin');
    $__section = $__isAgent ? 'agent-content' : ($__isStaff ? 'staff-content' : 'admin-content');
    $role = $__user->role;
@endphp

@extends($__layout)

@section('title', 'Edit Application #' . $application->application_number)
@section('page-title', 'Edit Application #' . $application->application_number)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
@endpush

@section($__section)
<div class="container-fluid px-3 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route($role . '.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route($role . '.applications.index') }}" class="text-decoration-none">Applications</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0" style="color:#1f2937;"><i class="fas fa-edit text-warning me-2"></i>Edit Application</h4>
            <p class="text-muted mb-0 small">#{{ $application->application_number }}</p>
        </div>
        <a href="{{ route($role . '.applications.index') }}" class="btn btn-outline-secondary" style="font-size:0.82rem;padding:8px 20px;border-radius:10px;">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <form action="{{ route($role . '.applications.update', $application) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="app-form-section">
                    <div class="app-form-section-header" style="border-left-color:#6366f1;"><i class="fas fa-user-graduate" style="color:#6366f1;"></i>Student</div>
                    <div class="app-form-section-body">
                        <input type="text" class="app-form-control form-control bg-light" value="{{ $application->student->first_name }} {{ $application->student->last_name }}" readonly style="font-size:0.85rem;font-weight:600;">
                        @if($user->is_agent && isset($students))
                            <label class="app-form-label mt-3">Change Student <small class="text-muted">(optional)</small></label>
                            <select name="student_id" class="app-form-control form-select">
                                @foreach($students as $s)
                                    <option value="{{ $s->id }}" {{ $application->student_id == $s->id ? 'selected' : '' }}>{{ $s->first_name }} {{ $s->last_name }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="hidden" name="student_id" value="{{ $application->student_id }}">
                        @endif
                    </div>
                </div>

                <div class="app-form-section">
                    <div class="app-form-section-header" style="border-left-color:#8b5cf6;"><i class="fas fa-university" style="color:#8b5cf6;"></i>University &amp; Course</div>
                    <div class="app-form-section-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="app-form-label">University</label>
                                <select name="university_id" class="app-form-control form-select" id="university_select" required>
                                    <option value="">-- Select University --</option>
                                    @foreach ($universities as $uni)
                                        <option value="{{ $uni->id }}" {{ $application->university_id == $uni->id ? 'selected' : '' }}>{{ $uni->name }} - {{ $uni->city }}, {{ $uni->country }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="app-form-label">Course</label>
                                <select name="course_id" class="app-form-control form-select" id="course_select" required>
                                    <option value="">-- Select Course --</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}" {{ $application->course_id == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                @if(($user->is_admin || $user->is_admin_staff) && $statuses->count())
                <div class="app-form-section">
                    <div class="app-form-section-header" style="border-left-color:#f59e0b;"><i class="fas fa-exchange-alt" style="color:#f59e0b;"></i>Status</div>
                    <div class="app-form-section-body">
                        <label class="app-form-label">Application Status</label>
                        <select name="application_status_id" class="app-form-control form-select">
                            @foreach($statuses as $st)
                                <option value="{{ $st->id }}" {{ $application->application_status_id == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                <div class="app-form-section">
                    <div class="app-form-section-header" style="border-left-color:#10b981;"><i class="fas fa-file-alt" style="color:#10b981;"></i>SOP Document</div>
                    <div class="app-form-section-body">
                        @if ($application->sop_file)
                            <div class="p-3 rounded-3 mb-3" style="background:#f3f4f6;border:1px solid #e5e7eb;">
                                <label class="app-form-label">Current SOP</label>
                                <div class="mt-1 d-flex gap-2">
                                    <a href="{{ Storage::url($application->sop_file) }}" target="_blank" class="btn btn-sm btn-outline-primary previewable" style="border-radius:8px;font-size:0.75rem;"
                                       data-url="{{ Storage::url($application->sop_file) }}"
                                       data-filename="SOP_{{ $application->application_number }}"
                                       data-preview-type="document"><i class="fas fa-eye me-1"></i>View</a>
                                    <a href="{{ Storage::url($application->sop_file) }}" download class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:0.75rem;"><i class="fas fa-download me-1"></i>Download</a>
                                </div>
                            </div>
                        @endif
                        <label class="app-form-label">Replace SOP <small class="text-muted">(optional)</small></label>
                        <input type="file" name="sop_file" class="app-form-control form-control" accept=".pdf,.doc,.docx">
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route($role . '.applications.show', $application) }}" class="btn btn-outline-secondary" style="font-size:0.82rem;padding:8px 24px;border-radius:10px;"><i class="fas fa-times me-1"></i>Cancel</a>
                    <button type="submit" class="btn btn-success" style="font-size:0.82rem;padding:8px 28px;border-radius:10px;"><i class="fas fa-save me-1"></i>Update Application</button>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="app-show-card">
                    <div class="app-show-card-header"><i class="fas fa-eye" style="color:#3b82f6;"></i>Preview</div>
                    <div class="app-show-card-body">
                        <div class="p-3 rounded-3 mb-2" style="background:#f8faff;">
                            <small class="text-muted d-block" style="font-size:0.65rem;text-transform:uppercase;">Student</small>
                            <span class="fw-semibold" style="font-size:0.85rem;">{{ $application->student->first_name }} {{ $application->student->last_name }}</span>
                        </div>
                        <div class="p-3 rounded-3 mb-2" style="background:#f8faff;">
                            <small class="text-muted d-block" style="font-size:0.65rem;text-transform:uppercase;">University</small>
                            <span class="fw-semibold" style="font-size:0.85rem;">{{ $application->university?->name ?? 'Not selected' }}</span>
                        </div>
                        <div class="p-3 rounded-3 mb-2" style="background:#f8faff;">
                            <small class="text-muted d-block" style="font-size:0.65rem;text-transform:uppercase;">Course</small>
                            <span class="fw-semibold" style="font-size:0.85rem;">{{ $application->course?->title ?? 'Not selected' }}</span>
                        </div>
                        <div class="p-3 rounded-3" style="background:#f8faff;">
                            <small class="text-muted d-block" style="font-size:0.65rem;text-transform:uppercase;">Status</small>
                            <span class="app-status-badge mt-1" style="background:{{ $application->status?->bg_color ?? '#6c757d' }}20;color:{{ $application->status?->bg_color ?? '#6c757d' }};">
                                <span style="width:6px;height:6px;border-radius:50%;background:{{ $application->status?->bg_color ?? '#6c757d' }};display:inline-block;"></span>
                                {{ $application->status?->name ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="app-show-card">
                    <div class="app-show-card-header"><i class="fas fa-info-circle" style="color:#3b82f6;"></i>Information</div>
                    <div class="app-show-card-body" style="font-size:0.82rem;">
                        <div class="mb-3"><small class="text-muted d-block" style="font-size:0.65rem;text-transform:uppercase;">Agent</small><span class="fw-semibold">{{ $application->agent?->business_name ?? 'N/A' }}</span></div>
                        <div class="mb-3"><small class="text-muted d-block" style="font-size:0.65rem;text-transform:uppercase;">Created</small><span class="fw-semibold">{{ $application->created_at->format('F j, Y') }}</span></div>
                        <div><small class="text-muted d-block" style="font-size:0.65rem;text-transform:uppercase;">Updated</small><span class="fw-semibold">{{ $application->updated_at->format('F j, Y') }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const uniSelect = document.getElementById('university_select');
    const courseSelect = document.getElementById('course_select');
    if (uniSelect && courseSelect) {
        uniSelect.addEventListener('change', function() {
            const uniId = this.value;
            if (!uniId) { courseSelect.disabled = true; courseSelect.innerHTML = '<option value="">-- Select Course --</option>'; return; }
            courseSelect.innerHTML = '<option value="">Loading...</option>';
            courseSelect.disabled = true;
            fetch('{{ route($role . '.applications.get-courses', ['universityId' => '__ID__']) }}'.replace('__ID__', uniId))
                .then(r => r.json())
                .then(data => {
                    let opts = '<option value="">-- Select Course --</option>';
                    if (Array.isArray(data) && data.length) {
                        data.forEach(c => { opts += `<option value="${c.id}" ${c.id == {{ $application->course_id }} ? 'selected' : ''}>${c.title}</option>`; });
                        courseSelect.disabled = false;
                    } else { opts = '<option value="">No courses available</option>'; courseSelect.disabled = true; }
                    courseSelect.innerHTML = opts;
                });
        });
    }
});
</script>
@endpush