@php
    $user = auth()->user();
    $isMgmt = $user->is_admin || $user->is_admin_staff;
    $isStaff = $user->is_staff && !$user->is_admin_staff;
    $isAgent = $user->is_agent || $user->is_agent_staff;
    $layout = $isStaff ? 'layouts.staff' : ($isMgmt ? 'layouts.admin' : 'layouts.agent');
    $section = $isStaff ? 'staff-content' : ($isMgmt ? 'admin-content' : 'agent-content');
    $prefix = $isStaff ? 'staff' : ($isMgmt ? 'admin' : 'agent');
    $apps = $student->applications ?? collect();
    $docs = $student->documents ?? collect();
    $showRemarks = !$isAgent && $student->remarks;
@endphp

@extends($layout)
@section('title', 'Student: ' . $student->full_name)
@section('page-title', 'Student: ' . $student->full_name)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/students.css') }}">
<style>
.file-preview-trigger{cursor:pointer;}
.file-preview-trigger:hover{opacity:0.85;}
</style>
@endpush

@section($section)

{{-- ═══════ GRADIENT HEADER ═══════ --}}
<div class="show-gradient-header">
    <div class="show-header-inner">
        <div class="show-header-left">
            <h1>{{ $student->full_name }}</h1>
            <div class="show-header-contact">
                @if($student->email)
                    <a href="mailto:{{ $student->email }}"><i class="fa-solid fa-envelope"></i>{{ $student->email }}</a>
                @endif
                @if($student->phone_number)
                    <a href="tel:{{ $student->phone_number }}"><i class="fa-solid fa-phone"></i>{{ $student->phone_number }}</a>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$student->phone_number) }}" target="_blank"><i class="fab fa-whatsapp"></i>WhatsApp</a>
                @endif
            </div>
        </div>
        <div class="show-header-actions">
            <a href="{{ route($prefix . '.students.edit', $student) }}" class="btn"><i class="fas fa-edit me-1"></i>Edit</a>
            @if(!$isStaff)
            <a href="{{ route($prefix . '.documents.index', $student) }}" class="btn"><i class="fas fa-folder-open me-1"></i>Docs</a>
            @endif
            @can('delete', $student)
            <x-confirm-delete url="{{ route($prefix . '.students.destroy', $student->id) }}" label="Delete" title="Delete {{ $student->full_name }}?" message="This will permanently delete this student and all associated data." class="btn-del" />
            @endcan
            <a href="{{ route($prefix . '.students.index') }}" class="btn"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- ═══════ LEFT SIDEBAR — Profile Card ═══════ --}}
    <div class="col-lg-4">
        <div class="profile-card">
            <div class="profile-card-photo">
                @if($student->students_photo && Storage::disk('public')->exists($student->students_photo))
                    <img src="{{ Storage::url($student->students_photo) }}" alt="">
                @else
                    <div class="photo-placeholder"><i class="fas fa-user-graduate"></i></div>
                @endif
            </div>
            @if($student->agent && !$isAgent)
                <a href="{{ route($prefix . '.users.show', $student->agent->slug ?? $student->agent->id) }}" class="profile-card-agent">{{ $student->agent->business_name ?? $student->agent->name }}</a>
            @endif
            <div class="profile-card-name">{{ $student->full_name }}</div>
            @if($student->preferred_country)
                <div class="profile-card-country">{{ $student->preferred_country }}</div>
            @endif

            <div class="profile-quick-stats">
                <div class="profile-quick-stat">
                    <div class="stat-icon"><i class="fas fa-folder-open"></i></div>
                    <div class="stat-num">{{ $docs->count() }}</div>
                    <div class="stat-lbl">Documents</div>
                </div>
                <div class="profile-quick-stat">
                    <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
                    <div class="stat-num">{{ $apps->count() }}</div>
                    <div class="stat-lbl">Applications</div>
                </div>
                @if($isMgmt)
                <div class="profile-quick-stat">
                    <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="stat-num">{{ number_format((float)($student->revenues()->sum('amount') ?? 0), 0) }}</div>
                    <div class="stat-lbl">Revenue</div>
                </div>
                @endif
            </div>

            @if($showRemarks)
            <div class="profile-remarks-section">
                <h6><i class="fas fa-pen me-1"></i>Remarks</h6>
                <div class="remarks-text">{{ $student->remarks }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- ═══════ RIGHT CONTENT — Tabbed Cards ═══════ --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius:14px;overflow:hidden;">
            {{-- Tabs --}}
            <div class="show-tabs">
                <button class="show-tab active" data-tab="overview"><i class="fas fa-id-card me-1"></i>Overview</button>
                <button class="show-tab" data-tab="applications">Applications <span class="tab-badge">{{ $apps->count() }}</span></button>
                <button class="show-tab" data-tab="documents">Documents <span class="tab-badge">{{ $docs->count() }}</span></button>
            </div>

            {{-- TAB: Overview --}}
            <div class="show-tab-pane active" id="tab-overview">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="info-mini-card">
                            <h6 style="color:var(--primary);"><i class="fas fa-user me-1"></i>Personal</h6>
                            <hr>
                            <p>
                                <strong>DOB:</strong> {{ $student->dob?->format('M d, Y') ?? 'N/A' }}<br>
                                <strong>Gender:</strong> {{ $student->gender ?? 'N/A' }}<br>
                                <strong>Nationality:</strong> {{ $student->nationality ?? 'N/A' }}<br>
                                <strong>Marital Status:</strong> {{ $student->marital_status ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-mini-card">
                            <h6 style="color:#10b981;"><i class="fas fa-passport me-1"></i>Passport</h6>
                            <hr>
                            <p>
                                <strong>Number:</strong> {{ $student->passport_number ?? 'N/A' }}<br>
                                <strong>Expiry:</strong> {{ $student->passport_expiry?->format('M d, Y') ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-mini-card">
                            <h6 style="color:#f59e0b;"><i class="fas fa-graduation-cap me-1"></i>Education</h6>
                            <hr>
                            <p>
                                <strong>Qualification:</strong> {{ $student->qualification ?? 'N/A' }}<br>
                                <strong>Year:</strong> {{ $student->passed_year ?? 'N/A' }}<br>
                                <strong>Grades:</strong> {{ $student->last_grades ?? 'N/A' }}<br>
                                <strong>Board:</strong> {{ $student->education_board ?? 'N/A' }}<br>
                                <strong>Gap:</strong> {{ $student->gap ?? '0' }} years
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-mini-card">
                            <h6 style="color:#06b6d4;"><i class="fas fa-globe me-1"></i>Preferences</h6>
                            <hr>
                            <p>
                                <strong>Country:</strong> {{ $student->preferred_country ?? 'N/A' }}<br>
                                <strong>City:</strong> {{ $student->preferred_city ?? 'N/A' }}<br>
                                <strong>Course:</strong> {{ $student->preferred_course ?? 'N/A' }}<br>
                                <strong>University:</strong> {{ $student->preferred_university ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="info-mini-card">
                            <h6 style="color:#6b7280;"><i class="fas fa-home me-1"></i>Addresses</h6>
                            <hr>
                            <p>
                                <strong>Permanent:</strong> {{ $student->permanent_address ?? 'N/A' }}<br>
                                <strong>Temporary:</strong> {{ $student->temporary_address ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB: Applications --}}
            <div class="show-tab-pane" id="tab-applications">
                @if(!$isStaff)
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h6 class="fw-bold mb-0" style="color:var(--primary);"><i class="fas fa-file-alt me-1"></i>Applications</h6>
                    <a href="{{ route($prefix . '.applications.create', ['student_id' => $student->id]) }}" class="btn btn-sm btn-primary" style="border-radius:8px;">+ New Application</a>
                </div>
                @endif
                @if($apps->isNotEmpty())
                    @foreach($apps as $app)
                    <div class="app-show-card">
                        <div class="app-show-card-header">
                            <span><i class="fas fa-ticket-alt me-1"></i>#{{ $app->application_number ?? $app->id }}</span>
                            <span><i class="far fa-calendar-alt me-1"></i>{{ $app->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="app-show-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-2"><span class="text-muted small text-uppercase" style="font-size:0.6rem;letter-spacing:0.03em;">University</span><div class="fw-semibold" style="font-size:0.82rem;">{{ $app->university?->name ?? 'N/A' }}</div></div>
                                    <div class="mb-2"><span class="text-muted small text-uppercase" style="font-size:0.6rem;letter-spacing:0.03em;">Course</span><div class="fw-semibold" style="font-size:0.82rem;">{{ $app->course?->title ?? 'N/A' }}</div></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <div class="bg-light rounded-3 p-2 flex-fill text-center">
                                            <div class="small text-muted" style="font-size:0.6rem;">Status</div>
                                            <span class="badge rounded-pill mt-1" style="background:{{ $app->status?->bg_color ?? '#6c757d' }};color:{{ $app->status?->text_color ?? '#fff' }};font-size:0.7rem;">{{ $app->status?->name ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route($prefix . '.applications.show', $app) }}" class="btn btn-sm btn-outline-info rounded-pill" style="font-size:0.68rem;"><i class="fas fa-eye me-1"></i>View</a>
                                        <a href="{{ route($prefix . '.applications.edit', $app) }}" class="btn btn-sm btn-outline-success rounded-pill" style="font-size:0.68rem;"><i class="fas fa-edit me-1"></i>Edit</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted" style="font-size:0.85rem;">No applications yet.</p>
                        @if(!$isStaff)
                        <a href="{{ route($prefix . '.applications.create', ['student_id' => $student->id]) }}" class="btn btn-sm btn-primary rounded-pill">Create Application</a>
                        @endif
                    </div>
                @endif
            </div>

            {{-- TAB: Documents --}}
            <div class="show-tab-pane" id="tab-documents">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h6 class="fw-bold mb-0" style="color:var(--primary);"><i class="fas fa-folder-open me-1"></i>Documents</h6>
                    <a href="{{ route($prefix . '.documents.index', $student) }}" class="btn btn-sm btn-outline-warning rounded-pill" style="font-size:0.68rem;"><i class="fas fa-upload me-1"></i>Manage</a>
                </div>
                @if($docs->isNotEmpty())
                <div class="row g-3">
                    @foreach($docs as $doc)
                        @php
                            $ext = pathinfo($doc->file_name ?? $doc->file_path, PATHINFO_EXTENSION);
                            $isImage = in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp']);
                            $fileUrl = Storage::url($doc->file_path);
                        @endphp
                        <div class="col-md-4 col-6">
                            <div class="doc-show-card">
                                <a href="{{ $fileUrl }}" target="_blank" class="text-decoration-none">
                                    <div class="doc-show-card-img">
                                        @if($isImage)<img src="{{ $fileUrl }}" alt="">@else<i class="fas fa-file-pdf fa-3x text-danger"></i>@endif
                                    </div>
                                </a>
                                <div class="doc-show-card-body">
                                    <div class="doc-title">{{ ucfirst(str_replace('_',' ',$doc->document_type)) }}</div>
                                    <div class="doc-meta"><i class="far fa-calendar-alt me-1"></i>{{ $doc->created_at->format('M d, Y') }}</div>
                                </div>
                                <div class="doc-show-card-actions">
                                    <a href="{{ route($prefix . '.documents.download', ['student' => $student->id, 'document' => $doc->id]) }}" class="btn btn-outline-success"><i class="fas fa-download"></i></a>
                                    <x-confirm-delete
                                        url="{{ route($prefix . '.documents.destroy', [$student->id, $doc->id]) }}"
                                        label=""
                                        title="Delete this document?"
                                        message="This action cannot be undone."
                                        class="btn btn-outline-danger"
                                    />
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted" style="font-size:0.85rem;">No documents uploaded.</p>
                        <a href="{{ route($prefix . '.documents.index', $student) }}" class="btn btn-sm btn-warning rounded-pill">Upload Documents</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var tabs = document.querySelectorAll('.show-tab');
    var panes = document.querySelectorAll('.show-tab-pane');
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            var target = this.dataset.tab;
            tabs.forEach(function(t) { t.classList.remove('active'); });
            panes.forEach(function(p) { p.classList.remove('active'); });
            this.classList.add('active');
            document.getElementById('tab-' + target).classList.add('active');
        });
    });
})();
</script>
@endpush
@endsection