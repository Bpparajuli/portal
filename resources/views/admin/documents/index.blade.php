@extends('layouts.app')
@section('title', 'Manage Documents')

@section('content')
<style>
    .doc-page { max-width: 1000px; margin: 0 auto; }

    .doc-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        transition: all var(--transition-fast);
    }

    .doc-card:hover {
        box-shadow: var(--shadow-md);
    }

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
        width: 28px;
        height: 28px;
        border-radius: var(--radius-full);
        background: var(--gray-100);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--text-muted);
        flex-shrink: 0;
    }

    .doc-name {
        flex: 1;
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--text-color);
        min-width: 0;
    }

    .doc-status {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.2rem 0.65rem;
        border-radius: var(--radius-full);
        font-size: 0.7rem;
        font-weight: 600;
    }

    .doc-status.uploaded { background: var(--success-soft); color: var(--success-dark); }
    .doc-status.pending { background: var(--accent-soft); color: var(--accent-dark); }
    .doc-status.missing { background: var(--gray-100); color: var(--text-muted); }

    .doc-preview {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-sm);
        object-fit: cover;
        border: 1px solid var(--border);
        flex-shrink: 0;
    }

    .doc-preview-placeholder {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-sm);
        background: var(--gray-100);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .progress-ring {
        position: relative;
        width: 56px;
        height: 56px;
    }

    .progress-ring svg {
        transform: rotate(-90deg);
    }

    .progress-ring .bg { fill: none; stroke: var(--gray-200); stroke-width: 4; }
    .progress-ring .fg { fill: none; stroke: var(--primary); stroke-width: 4; stroke-linecap: round; transition: stroke-dasharray 0.5s ease; }

    .progress-text {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--primary);
    }
</style>

<div class="doc-page">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-folder-open text-primary me-2"></i>Documents
            </h4>
            <p class="text-muted small mb-0">
                <a href="{{ route('admin.students.show', $student->id) }}" class="text-primary fw-semibold">
                    {{ $student->first_name }} {{ $student->last_name }}
                </a>
                @if($student->agent)
                    &middot; Agent: {{ $student->agent->business_name }}
                @endif
                &middot;
                <a href="{{ route('admin.students.applications', $student->id) }}">Applications</a>
                ({{ $student->applications->count() }})
            </p>
        </div>
        <a href="{{ route('admin.documents.downloadAll', $student->id) }}" class="btn btn-primary">
            <i class="fas fa-download me-1"></i>Download All
        </a>
    </div>

    @php
        $totalDocs = count($allDocumentTypes);
        $uploadedCount = $predefinedDocs->count();
        $progressPercent = $totalDocs > 0 ? round(($uploadedCount / $totalDocs) * 100) : 0;
        $circumference = 2 * pi() * 22;
    @endphp

    <!-- Progress Overview -->
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

    <!-- Compulsory Documents -->
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
                    $uploaded = $predefinedDocs->firstWhere('document_type', $type);
                    $ext = $uploaded ? pathinfo($uploaded->file_name, PATHINFO_EXTENSION) : '';
                    $isImage = in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp']);
                    $filePath = $uploaded ? asset('storage/' . $uploaded->file_path) : '';
                @endphp
                <div class="doc-item">
                    <span class="doc-number">{{ $loop->iteration }}</span>
                    <div class="doc-name">
                        {{ ucwords(str_replace('_', ' ', $type)) }}
                    </div>
                    @if(!$uploaded)
                        <span class="doc-status missing">
                            <i class="fas fa-times-circle"></i>Not Uploaded
                        </span>
                        <div style="width:48px;flex-shrink:0;"></div>
                    @else
                        <span class="doc-status uploaded">
                            <i class="fas fa-check-circle"></i>Uploaded
                        </span>
                        <div>
                            @if($isImage)
                                <img src="{{ $filePath }}" class="doc-preview" alt="{{ $type }}">
                            @else
                                <div class="doc-preview-placeholder">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                            @endif
                        </div>
                    @endif
                    <div class="d-flex gap-1">
                        @if($uploaded)
                            <a href="{{ route('admin.documents.download', [$student->id, $uploaded->id]) }}"
                               class="btn btn-sm btn-ghost" title="Download">
                                <i class="fas fa-download"></i>
                            </a>
                            <form action="{{ route('admin.documents.destroy', [$student->id, $uploaded->id]) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this document?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-ghost text-danger" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Other Documents -->
    <div class="doc-card">
        <div class="px-4 py-3 border-bottom">
            <h5 class="fw-bold mb-0">
                <i class="fas fa-folder text-primary me-2"></i>Other Documents
            </h5>
        </div>
        <div>
            @forelse($otherDocs as $doc)
                @php
                    $ext = pathinfo($doc->file_name, PATHINFO_EXTENSION);
                    $isImage = in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp']);
                    $filePath = asset('storage/' . $doc->file_path);
                @endphp
                <div class="doc-item">
                    <span class="doc-number">{{ $loop->iteration + $totalDocs }}</span>
                    <div class="doc-name">
                        {{ $doc->custom_name ?? $doc->document_type }}
                    </div>
                    <div>
                        @if($isImage)
                            <img src="{{ $filePath }}" class="doc-preview" alt="">
                        @else
                            <div class="doc-preview-placeholder">
                                <i class="fas fa-file-alt"></i>
                            </div>
                        @endif
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.documents.download', [$student->id, $doc->id]) }}"
                           class="btn btn-sm btn-ghost" title="Download">
                            <i class="fas fa-download"></i>
                        </a>
                        <form action="{{ route('admin.documents.destroy', [$student->id, $doc->id]) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this document?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-ghost text-danger" title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
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

@push('scripts')
<script>
    // Preview on click
    document.querySelectorAll('[data-preview]').forEach(el => {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.dataset.preview;
            Swal.fire({
                imageUrl: url,
                imageAlt: 'Document preview',
                showCloseButton: true,
                showConfirmButton: false,
                width: 'auto',
                padding: '0'
            });
        });
    });
</script>
@endpush
