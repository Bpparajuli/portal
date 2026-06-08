@extends('layouts.admin')

@section('admin-content')
<div class="container-fluid p-4">
    <x-page-header title="Dynamic Content" subtitle="Manage dynamic content sections for the website" />

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-hand-wave text-primary me-2"></i>Guest Dashboard Welcome Text
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#welcomeModal">
                        <i class="fas fa-edit me-1"></i>Edit
                    </button>
                </div>
                <div class="card-body">
                    @php $welcomeText = Setting::getValue('guest_dashboard_welcome', 'Welcome to your dashboard'); @endphp
                    <div class="p-3 bg-light rounded-3">
                        <p class="mb-0">{{ $welcomeText }}</p>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        <i class="fas fa-info-circle me-1"></i>This text appears in the hero section of the guest dashboard.
                    </small>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-calendar-alt text-success me-2"></i>Upcoming Programs
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#programModal">
                        <i class="fas fa-plus me-1"></i>Add Program
                    </button>
                </div>
                <div class="card-body p-0">
                    @php $programs = Setting::getValue('upcoming_programs', []); @endphp
                    @if(count($programs))
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small">
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                        <th>Link</th>
                                        <th width="80" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($programs as $index => $program)
                                    <tr>
                                        <td class="fw-semibold">{{ $program['title'] ?? '' }}</td>
                                        <td>
                                            <span class="text-truncate d-inline-block" style="max-width:250px">
                                                {{ $program['description'] ?? '' }}
                                            </span>
                                        </td>
                                        <td>{{ $program['date'] ?? '' }}</td>
                                        <td>
                                            @if(!empty($program['link']))
                                                <a href="{{ $program['link'] }}" target="_blank" class="text-decoration-none small">
                                                    <i class="fas fa-external-link-alt me-1"></i>View
                                                </a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-secondary edit-program-btn"
                                                data-index="{{ $index }}"
                                                data-title="{{ $program['title'] ?? '' }}"
                                                data-description="{{ $program['description'] ?? '' }}"
                                                data-date="{{ $program['date'] ?? '' }}"
                                                data-link="{{ $program['link'] ?? '' }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-program-btn"
                                                data-index="{{ $index }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-plus fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No upcoming programs configured.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-running text-warning me-2"></i>Activities / Events
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#activityModal">
                        <i class="fas fa-plus me-1"></i>Add Activity
                    </button>
                </div>
                <div class="card-body p-0">
                    @php $activities = Setting::getValue('activities_events', []); @endphp
                    @if(count($activities))
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small">
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th width="80" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activities as $index => $activity)
                                    <tr>
                                        <td class="fw-semibold">{{ $activity['title'] ?? '' }}</td>
                                        <td>
                                            <span class="text-truncate d-inline-block" style="max-width:400px">
                                                {{ $activity['description'] ?? '' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-secondary edit-activity-btn"
                                                data-index="{{ $index }}"
                                                data-title="{{ $activity['title'] ?? '' }}"
                                                data-description="{{ $activity['description'] ?? '' }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-activity-btn"
                                                data-index="{{ $index }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-day fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No activities or events configured.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-info-circle text-info me-2"></i>About Dynamic Content
                    </h6>
                    <p class="small text-muted mb-2">
                        This page manages dynamic content displayed on the public-facing sections of the website.
                    </p>
                    <ul class="small text-muted mb-0 ps-3">
                        <li class="mb-1">Changes take effect immediately.</li>
                        <li class="mb-1">Use plain text or basic HTML for formatting.</li>
                        <li>Links should include the full URL with <code>https://</code>.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<x-modal id="welcomeModal" title="Edit Welcome Text" size="md">
    <x-slot:body>
        <form action="{{ route('admin.pages.dynamic.update') }}" method="POST" id="welcomeForm">
            @csrf
            <input type="hidden" name="section" value="welcome">
            <div class="mb-3">
                <label class="form-label fw-semibold">Welcome Text</label>
                <textarea name="value" class="form-control" rows="5" placeholder="Enter welcome text for the guest dashboard hero section">{{ Setting::getValue('guest_dashboard_welcome', 'Welcome to your dashboard') }}</textarea>
                <small class="text-muted">This text appears at the top of the guest dashboard page.</small>
            </div>
        </form>
    </x-slot:body>
    <x-slot:footer>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="welcomeForm" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Save
        </button>
    </x-slot:footer>
</x-modal>

<x-modal id="programModal" title="{{ isset($editProgram) ? 'Edit Program' : 'Add Program' }}" size="md">
    <x-slot:body>
        <form action="{{ route('admin.pages.dynamic.update') }}" method="POST" id="programForm">
            @csrf
            <input type="hidden" name="section" value="programs">
            <input type="hidden" name="index" id="programIndex" value="">
            <div class="mb-3">
                <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" id="programTitle" class="form-control" placeholder="Program title" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" id="programDescription" class="form-control" rows="3" placeholder="Brief description of the program"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Date</label>
                <input type="date" name="date" id="programDate" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Link</label>
                <input type="url" name="link" id="programLink" class="form-control" placeholder="https://example.com">
            </div>
        </form>
    </x-slot:body>
    <x-slot:footer>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="programForm" class="btn btn-primary" id="programSubmitBtn">
            <i class="fas fa-save me-2"></i>Save
        </button>
    </x-slot:footer>
</x-modal>

<x-modal id="activityModal" title="{{ isset($editActivity) ? 'Edit Activity' : 'Add Activity' }}" size="md">
    <x-slot:body>
        <form action="{{ route('admin.pages.dynamic.update') }}" method="POST" id="activityForm">
            @csrf
            <input type="hidden" name="section" value="activities">
            <input type="hidden" name="index" id="activityIndex" value="">
            <div class="mb-3">
                <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" id="activityTitle" class="form-control" placeholder="Activity title" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" id="activityDescription" class="form-control" rows="3" placeholder="Brief description of the activity or event"></textarea>
            </div>
        </form>
    </x-slot:body>
    <x-slot:footer>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="activityForm" class="btn btn-primary" id="activitySubmitBtn">
            <i class="fas fa-save me-2"></i>Save
        </button>
    </x-slot:footer>
</x-modal>

<form id="deleteProgramForm" method="POST" style="display:none">
    @csrf
    <input type="hidden" name="section" value="programs">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="index" id="deleteProgramIndex" value="">
</form>

<form id="deleteActivityForm" method="POST" style="display:none">
    @csrf
    <input type="hidden" name="section" value="activities">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="index" id="deleteActivityIndex" value="">
</form>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.edit-program-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('programIndex').value = this.dataset.index;
                document.getElementById('programTitle').value = this.dataset.title;
                document.getElementById('programDescription').value = this.dataset.description;
                document.getElementById('programDate').value = this.dataset.date;
                document.getElementById('programLink').value = this.dataset.link;
                document.getElementById('programSubmitBtn').innerHTML = '<i class="fas fa-save me-2"></i>Update';
                const modal = new bootstrap.Modal(document.getElementById('programModal'));
                modal.show();
            });
        });

        document.querySelectorAll('.edit-activity-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('activityIndex').value = this.dataset.index;
                document.getElementById('activityTitle').value = this.dataset.title;
                document.getElementById('activityDescription').value = this.dataset.description;
                document.getElementById('activitySubmitBtn').innerHTML = '<i class="fas fa-save me-2"></i>Update';
                const modal = new bootstrap.Modal(document.getElementById('activityModal'));
                modal.show();
            });
        });

        document.querySelectorAll('.delete-program-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = this.dataset.index;
                Swal.fire({
                    title: 'Delete Program?',
                    text: 'Are you sure you want to delete this program?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('deleteProgramIndex').value = index;
                        document.getElementById('deleteProgramForm').action = '{{ route('admin.pages.dynamic.update') }}';
                        document.getElementById('deleteProgramForm').submit();
                    }
                });
            });
        });

        document.querySelectorAll('.delete-activity-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = this.dataset.index;
                Swal.fire({
                    title: 'Delete Activity?',
                    text: 'Are you sure you want to delete this activity?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('deleteActivityIndex').value = index;
                        document.getElementById('deleteActivityForm').action = '{{ route('admin.pages.dynamic.update') }}';
                        document.getElementById('deleteActivityForm').submit();
                    }
                });
            });
        });

        const programModal = document.getElementById('programModal');
        if (programModal) {
            programModal.addEventListener('hidden.bs.modal', function() {
                document.getElementById('programIndex').value = '';
                document.getElementById('programTitle').value = '';
                document.getElementById('programDescription').value = '';
                document.getElementById('programDate').value = '';
                document.getElementById('programLink').value = '';
                document.getElementById('programSubmitBtn').innerHTML = '<i class="fas fa-save me-2"></i>Save';
            });
        }

        const activityModal = document.getElementById('activityModal');
        if (activityModal) {
            activityModal.addEventListener('hidden.bs.modal', function() {
                document.getElementById('activityIndex').value = '';
                document.getElementById('activityTitle').value = '';
                document.getElementById('activityDescription').value = '';
                document.getElementById('activitySubmitBtn').innerHTML = '<i class="fas fa-save me-2"></i>Save';
            });
        }
    });
</script>
@endpush
@endsection
