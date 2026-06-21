@php
    $role = $role ?? (auth()->user()->is_admin_staff ? 'admin' : auth()->user()->role);
    $routePrefix = $routePrefix ?? $role;
    $canUpload = in_array($role, ['admin', 'agent', 'staff']);
    $canDelete = in_array($role, ['admin', 'agent', 'staff']);
    $canDownloadAll = $role === 'admin' || $role === 'staff';
    $showApplyNow = $role === 'agent';
    $totalDocs = count($allDocumentTypes);
    $uploadedCount = $predefinedDocs->count();
    $progressPercent = $totalDocs > 0 ? round(($uploadedCount / $totalDocs) * 100) : 0;
    $circumference = 2 * pi() * 22;
@endphp

@extends('layouts.app')
@section('page-title', 'Manage Documents')
@section('title', 'Manage Documents')
@section('content')

<link href="{{ asset('css/styles.css') }}" rel="stylesheet">
<style>
    .doc-page { max-width: 1000px; margin: 0 auto; }
    .doc-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        transition: all var(--transition-fast);
    }
    .doc-card:hover { box-shadow: var(--shadow-md); }
    .doc-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.875rem 1.25rem;
        border-bottom: 1px solid var(--border-light);
        transition: background var(--transition-fast);
    }
    .doc-item:last-child { border-bottom: none; }
    .doc-item:hover { background: var(--bg-hover); }
    .doc-number {
        width: 28px; height: 28px;
        border-radius: var(--radius-full);
        background: var(--gray-100);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.75rem; font-weight: 700; color: var(--text-muted);
        flex-shrink: 0;
    }
    .doc-name { flex: 1; font-weight: 600; font-size: 0.9rem; color: var(--text-color); min-width: 0; }
    .doc-status {
        display: flex; align-items: center; gap: 0.35rem;
        padding: 0.2rem 0.65rem; border-radius: var(--radius-full);
        font-size: 0.7rem; font-weight: 600;
    }
    .doc-status.uploaded { background: var(--success-soft); color: var(--success-dark); }
    .doc-status.pending { background: var(--accent-soft); color: var(--accent-dark); }
    .doc-status.missing { background: var(--gray-100); color: var(--text-muted); }
    .doc-preview {
        width: 48px; height: 48px;
        border-radius: var(--radius-sm); object-fit: cover;
        border: 1px solid var(--border); flex-shrink: 0;
    }
    .doc-preview-placeholder {
        width: 48px; height: 48px;
        border-radius: var(--radius-sm); background: var(--gray-100);
        display: flex; align-items: center; justify-content: center;
        color: var(--text-muted); font-size: 1.25rem; flex-shrink: 0;
    }
    .progress-ring { position: relative; width: 56px; height: 56px; }
    .progress-ring svg { transform: rotate(-90deg); }
    .progress-ring .bg { fill: none; stroke: var(--gray-200); stroke-width: 4; }
    .progress-ring .fg { fill: none; stroke: var(--primary); stroke-width: 4; stroke-linecap: round; transition: stroke-dasharray 0.5s ease; }
    .progress-text {
        position: absolute; inset: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.7rem; font-weight: 700; color: var(--primary);
    }
</style>

<div class="doc-page">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-folder-open text-primary me-2"></i>Documents
            </h4>
            <p class="text-muted small mb-0">
                @if($role === 'admin')
                    <a href="{{ route("{$routePrefix}.students.show", $student->id) }}" class="text-primary fw-semibold">
                        {{ $student->first_name }} {{ $student->last_name }}
                    </a>
                    @if($student->agent)
                        &middot; Agent: {{ $student->agent->business_name }}
                    @endif
                    &middot;
                    <a href="{{ route("{$routePrefix}.students.applications", $student->id) }}">Applications</a>
                    ({{ $student->applications->count() }})
                @elseif($role === 'agent')
                    <a href="{{ route("{$routePrefix}.students.show", $student->id) }}" class="text-primary fw-semibold">
                        {{ $student->first_name }} {{ $student->last_name }}
                    </a>
                    &middot;
                    @php $count = $student->applications->count(); @endphp
                    @if($count === 0)
                        No applications
                    @elseif($count === 1)
                        1 Application submitted
                    @else
                        {{ $count }} Applications submitted
                    @endif
                @elseif($role === 'staff')
                    <a href="{{ route("{$routePrefix}.students.show", $student->id) }}" class="text-primary fw-semibold">
                        {{ $student->first_name }} {{ $student->last_name }}
                    </a>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            @if($role !== 'staff')
                <a href="{{ route("{$routePrefix}.students.show", $student->id) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            @endif
            @if($canDownloadAll)
                <a href="{{ route("{$routePrefix}.documents.downloadAll", $student->id) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-download me-1"></i>Download All
                </a>
            @endif
        </div>
    </div>

    {{-- Progress Overview (admin gets full stats, others get simple) --}}
    @if($role === 'admin')
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="doc-card p-3 text-center">
                    <div class="progress-ring mx-auto mb-2">
                        <svg width="56" height="56">
                            <circle class="bg" cx="28" cy="28" r="22"/>
                            <circle class="fg" cx="28" cy="28" r="22"
                                    stroke-dasharray="{{ $circumference }}"
                                    stroke-dashoffset="{{ $circumference - ($progressPercent / 100) * $circumference }}"/>
                        </svg>
                        <div class="progress-text">{{ $progressPercent }}%</div>
                    </div>
                    <div class="small text-muted">Documents Complete</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="doc-card p-3">
                    <div class="fw-bold fs-4" style="color:var(--success);">{{ $uploadedCount }}</div>
                    <div class="small text-muted">Uploaded</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="doc-card p-3">
                    <div class="fw-bold fs-4" style="color:var(--warning);">{{ $totalDocs - $uploadedCount }}</div>
                    <div class="small text-muted">Pending</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="doc-card p-3">
                    <div class="fw-bold fs-4" style="color:var(--primary);">{{ $otherDocs->count() }}</div>
                    <div class="small text-muted">Other Documents</div>
                </div>
            </div>
        </div>
    @endif

    {{-- Required Documents --}}
    @if($role === 'staff')
        <div class="row g-4 mb-4">
            @foreach($allDocumentTypes as $type)
                @php $doc = $predefinedDocs->firstWhere('document_type', $type); @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0 text-capitalize">{{ str_replace('_', ' ', $type) }}</h6>
                                @if($doc)
                                    <span class="badge bg-success bg-opacity-10 text-success">Uploaded</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning">Missing</span>
                                @endif
                            </div>
                            @if($doc)
                                <div class="d-flex gap-2 mt-2">
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary previewable" data-url="{{ asset('storage/' . $doc->file_path) }}" data-filename="{{ $doc->file_name }}" data-preview-type="document"><i class="fas fa-eye"></i></a>
                                    @if($canDelete)
                                    <x-confirm-delete
                                        url="{{ route("{$routePrefix}.documents.destroy", [$student, $doc]) }}"
                                        label="Delete"
                                        title="Delete this document?"
                                        message="This action cannot be undone."
                                        class="btn btn-sm btn-outline-danger"
                                    />
                                    @endif
                                </div>
                            @elseif($canUpload)
                                <form action="{{ route("{$routePrefix}.documents.store", $student) }}" method="POST" enctype="multipart/form-data" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="document_type" value="{{ $type }}">
                                    <div class="input-group input-group-sm">
                                        <input type="file" name="file" class="form-control" required accept=".jpg,.jpeg,.png,.pdf">
                                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-upload"></i></button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="doc-card mb-4">
            <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">
                    <i class="fas fa-star text-warning me-2"></i>Required Documents
                </h5>
                <span class="badge bg-soft-primary">{{ $uploadedCount }}/{{ $totalDocs }}</span>
            </div>
            <div>
                @foreach($allDocumentTypes as $type)
                    @php
                        $doc = $predefinedDocs->firstWhere('document_type', $type);
                        $ext = $doc ? pathinfo($doc->file_name, PATHINFO_EXTENSION) : '';
                        $isImage = in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp']);
                        $filePath = $doc ? asset('storage/' . $doc->file_path) : '';
                    @endphp
                    <div class="doc-item">
                        <span class="doc-number">{{ $loop->iteration }}</span>
                        <div class="doc-name">{{ ucwords(str_replace('_', ' ', $type)) }}</div>

                        @if(!$doc)
                            <span class="doc-status missing"><i class="fas fa-times-circle"></i>Not Uploaded</span>
                            @if($canUpload)
                                <form action="{{ route("{$routePrefix}.documents.store", $student->id) }}" method="POST" enctype="multipart/form-data" class="d-inline-flex align-items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="document_type" value="{{ $type }}">
                                    <input type="file" name="file" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif" class="form-control form-control-sm" style="width:200px;">
                                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-upload"></i></button>
                                </form>
                            @endif
                            <div style="width:48px;flex-shrink:0;"></div>
                        @else
                            <span class="doc-status uploaded"><i class="fas fa-check-circle"></i>Uploaded</span>
                            <div>
                                @if($isImage)
                                    <a href="{{ $filePath }}" target="_blank" class="d-inline-block previewable" data-url="{{ $filePath }}" data-filename="{{ $doc->file_name }}" data-preview-type="document">
                                        <img src="{{ $filePath }}" class="doc-preview" alt="{{ $type }}">
                                    </a>
                                @else
                                    <a href="{{ $filePath }}" target="_blank" class="doc-preview-placeholder previewable" data-url="{{ $filePath }}" data-filename="{{ $doc->file_name }}" data-preview-type="document">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                @endif
                            </div>
                        @endif

                        @if($doc)
                            <div class="d-flex gap-1">
                                <a href="{{ route("{$routePrefix}.documents.download", [$student->id, $doc->id]) }}" class="btn btn-sm btn-ghost" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                @if($canDelete)
                                    <x-confirm-delete
                                        url="{{ route("{$routePrefix}.documents.destroy", [$student->id, $doc->id]) }}"
                                        label="Delete"
                                        title="Delete this document?"
                                        message="This action cannot be undone."
                                        class="btn btn-sm btn-ghost text-danger"
                                    />
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Apply Now (agent only) --}}
    @if($showApplyNow && count($predefinedDocs) === count($allDocumentTypes))
        <div class="apply-now-box text-center p-3 bg-light rounded shadow-sm mb-4">
            <h5 class="text-success mb-2">{{ $uploadedCount }}/{{ $totalDocs }} All required documents have been uploaded</h5>
            <a href="{{ route("{$routePrefix}.applications.create") }}?student_id={{ $student->id }}" class="btn btn-lg btn-success px-4">
                <i class="fa-solid fa-paper-plane me-2"></i> Apply Now
            </a>
        </div>
    @endif

    {{-- Other Documents --}}
    <div class="doc-card">
        <div class="px-4 py-3 border-bottom">
            <h5 class="fw-bold mb-0">
                <i class="fas fa-folder text-primary me-2"></i>Other Documents
            </h5>
        </div>
        @if($canUpload)
        <div class="px-4 py-3 border-bottom bg-light">
            <form action="{{ route("{$routePrefix}.documents.storeOther", $student->id) }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center gap-2 flex-wrap">
                @csrf
                <input type="text" name="custom_name" class="form-control form-control-sm" placeholder="Document name" required style="width:200px;">
                <input type="file" name="file" class="form-control form-control-sm" required accept=".jpg,.jpeg,.png,.pdf" style="width:200px;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-upload me-1"></i>Upload</button>
            </form>
        </div>
        @endif
        <div>
            @forelse($otherDocs as $doc)
                @php
                    $ext = pathinfo($doc->file_name, PATHINFO_EXTENSION);
                    $isImage = in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp']);
                    $filePath = asset('storage/' . $doc->file_path);
                @endphp
                <div class="doc-item">
                    <span class="doc-number">{{ $loop->iteration + $totalDocs }}</span>
                    <div class="doc-name">{{ $doc->custom_name ?? $doc->document_type }}</div>
                    <div>
                        @if($isImage)
                            <a href="{{ $filePath }}" target="_blank" class="d-inline-block previewable" data-url="{{ $filePath }}" data-filename="{{ $doc->file_name }}" data-preview-type="document">
                                <img src="{{ $filePath }}" class="doc-preview" alt="">
                            </a>
                        @else
                            <a href="{{ $filePath }}" target="_blank" class="doc-preview-placeholder previewable" data-url="{{ $filePath }}" data-filename="{{ $doc->file_name }}" data-preview-type="document">
                                <i class="fas fa-file-alt"></i>
                            </a>
                        @endif
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route("{$routePrefix}.documents.download", [$student->id, $doc->id]) }}" class="btn btn-sm btn-ghost" title="Download">
                            <i class="fas fa-download"></i>
                        </a>
                        @if($canDelete)
                            <x-confirm-delete
                                url="{{ route("{$routePrefix}.documents.destroy", [$student->id, $doc->id]) }}"
                                label="Delete"
                                title="Delete this document?"
                                message="This action cannot be undone."
                                class="btn btn-sm btn-ghost text-danger"
                            />
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-folder-open fa-2x mb-2" style="opacity:0.3;"></i>
                    <p class="mb-0">No additional documents uploaded</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
