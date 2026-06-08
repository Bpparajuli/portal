@extends('layouts.app')
@php $role = auth()->user()->role; $user = auth()->user(); @endphp

@section('title', 'Edit Application #' . $application->application_number)

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route($role . '.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route($role . '.applications.index') }}">Applications</a></li>
                    <li class="breadcrumb-item active">Edit Application</li>
                </ol>
            </nav>
            <h1 class="display-6 fw-bold mb-0">Edit Application</h1>
            <p class="text-muted mt-2">Application #{{ $application->application_number }}</p>
        </div>
        <a href="{{ route($role . '.applications.index') }}" class="btn btn-outline-secondary rounded-pill px-4"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-5">
                    <form action="{{ route($role . '.applications.update', $application) }}" method="POST" enctype="multipart/form-data">
                        @csrf @method('PUT')

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Student</label>
                            <input type="text" class="form-control bg-light rounded-3" value="{{ $application->student->first_name }} {{ $application->student->last_name }}" readonly>
                            @if($user->is_agent && isset($students))
                                <select name="student_id" class="form-select rounded-3 mt-2">
                                    @foreach($students as $s)
                                        <option value="{{ $s->id }}" {{ $application->student_id == $s->id ? 'selected' : '' }}>{{ $s->first_name }} {{ $s->last_name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Change student (optional)</small>
                            @else
                                <input type="hidden" name="student_id" value="{{ $application->student_id }}">
                            @endif
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">University</label>
                            <select name="university_id" class="form-select rounded-3" id="university_select" required>
                                <option value="">-- Select University --</option>
                                @foreach ($universities as $uni)
                                    <option value="{{ $uni->id }}" {{ $application->university_id == $uni->id ? 'selected' : '' }}>{{ $uni->name }} - {{ $uni->city }}, {{ $uni->country }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Course</label>
                            <select name="course_id" class="form-select rounded-3" id="course_select" required>
                                <option value="">-- Select Course --</option>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}" {{ $application->course_id == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if($user->is_admin && $statuses->count())
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="application_status_id" class="form-select rounded-3">
                                    @foreach($statuses as $st)
                                        <option value="{{ $st->id }}" {{ $application->application_status_id == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Upload New SOP (Optional)</label>
                            <input type="file" name="sop_file" class="form-control rounded-3" accept=".pdf,.doc,.docx">
                            <small class="text-muted">Leave empty to keep current SOP</small>
                        </div>

                        @if ($application->sop_file)
                            <div class="mb-4 p-3 bg-light rounded-3">
                                <label class="form-label fw-semibold">Current SOP</label>
                                <div>
                                    <a href="{{ Storage::url($application->sop_file) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-eye me-1"></i>View</a>
                                    <a href="{{ Storage::url($application->sop_file) }}" download class="btn btn-sm btn-outline-secondary rounded-pill"><i class="fas fa-download me-1"></i>Download</a>
                                </div>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            <a href="{{ route($role . '.applications.show', $application) }}" class="btn btn-outline-secondary rounded-pill px-4"><i class="fas fa-times me-2"></i>Cancel</a>
                            <button type="submit" class="btn btn-success rounded-pill px-4"><i class="fas fa-save me-2"></i>Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-eye me-2 text-primary"></i>Preview</h5>
                    <div class="p-3 bg-light rounded-3 mb-2">
                        <small class="text-muted d-block">Student</small>
                        <span class="fw-semibold">{{ $application->student->first_name }} {{ $application->student->last_name }}</span>
                    </div>
                    <div class="p-3 bg-light rounded-3 mb-2">
                        <small class="text-muted d-block">University</small>
                        <span class="fw-semibold">{{ $application->university?->name ?? 'Not selected' }}</span>
                    </div>
                    <div class="p-3 bg-light rounded-3 mb-2">
                        <small class="text-muted d-block">Course</small>
                        <span class="fw-semibold">{{ $application->course?->title ?? 'Not selected' }}</span>
                    </div>
                    <div class="p-3 bg-light rounded-3">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge" style="background:{{ $application->status?->bg_color ?? '#6c757d' }};">{{ $application->status?->name ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Information</h5>
                    <div class="mb-3"><small class="text-muted d-block">Agent</small><span class="fw-semibold">{{ $application->agent?->business_name ?? 'N/A' }}</span></div>
                    <div class="mb-3"><small class="text-muted d-block">Created</small><span class="fw-semibold">{{ $application->created_at->format('F j, Y') }}</span></div>
                    <div><small class="text-muted d-block">Updated</small><span class="fw-semibold">{{ $application->updated_at->format('F j, Y') }}</span></div>
                </div>
            </div>
        </div>
    </div>
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
