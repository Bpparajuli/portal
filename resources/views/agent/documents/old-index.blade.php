@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/document.css') }}">
@section('content')

<div class="upload-section container">
    <h2 class="mb-4">Manage Documents for {{ trim($student->first_name . ' ' . $student->last_name) }}</h2>

    {{-- -------------------- Predefined Documents -------------------- --}}
    <h4>Compulsory Documents</h4>
    <div class="predefined-documents mb-4">
        @foreach($allDocumentTypes as $type)
        @php
        $uploaded = $predefinedDocs->firstWhere('document_type', $type);
        $extension = $uploaded ? pathinfo($uploaded->file_name, PATHINFO_EXTENSION) : null;
        $isImage = $uploaded && in_array(strtolower($extension), ['jpg','jpeg','png','gif','webp']);
        $filePath = $uploaded ? asset('storage/' . $uploaded->file_path) : null;
        @endphp

        <div class="uploaded-item d-flex align-items-center justify-content-between gap-2">
            <div><strong>{{ ucwords(str_replace('_',' ',$type)) }}</strong></div>

            @if(!$uploaded)
            {{-- Upload form inline --}}
            <form action="{{ route('agent.documents.store', $student->id) }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2 align-items-center flex-grow-1">
                @csrf
                <input type="file" name="file" class="form-control form-control-sm" required>
                <input type="hidden" name="document_type" value="{{ $type }}">
                <button type="submit" class="btn btn-success btn-sm">Upload</button>
            </form>
            @else
            {{-- Already uploaded --}}
            <div class="d-flex align-items-center gap-2">
                @if($isImage)
                <a href="#" class="img-preview-link" data-img="{{ $filePath }}">
                    <img src="{{ $filePath }}" style="max-width:50px; border-radius:3px;">
                </a>
                @else
                <i class="fa-solid fa-file-lines fa-lg text-muted"></i>
                @endif
                <span>Uploaded ✅</span>
            </div>

            <div class="actions d-flex gap-1">
                <a href="{{ route('agent.documents.download', [$student->id, $uploaded->id]) }}" class="btn text-primary btn-sm">
                    <i class="fa-solid fa-download"></i>
                </a>
                <form action="{{ route('agent.documents.destroy', [$student->id, $uploaded->id]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn text-danger btn-sm" onclick="return confirm('Delete this document?')">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    {{-- Apply Now Button (only if all compulsory documents uploaded) --}}
    @if(count($predefinedDocs) === count($allDocumentTypes))
    <div class="apply-now-box text-center p-3 bg-light rounded shadow-sm mb-4">
        <h5 class="text-success mb-2">✅ All required documents have been uploaded</h5>
        <a href="{{ route('agent.applications.create') }}?student_id={{ $student->id }}" class="btn btn-lg btn-success px-4">
            <i class="fa-solid fa-paper-plane me-2"></i> Apply Now
        </a>
    </div>
    @endif

    <hr>

    {{-- -------------------- Other Documents -------------------- --}}
    <h4>Other Documents</h4>
    <div class="other-documents mb-4">
        {{-- Upload new other document inline --}}
        <form action="{{ route('agent.documents.storeOther', $student->id) }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center gap-2 mb-2">
            @csrf
            <input type="file" name="file" class="form-control form-control-sm" required>
            <input type="text" name="custom_name" class="form-control form-control-sm" placeholder="Document Name" required>
            <button type="submit" class="btn btn-success btn-sm">Upload</button>
        </form>

        {{-- Display uploaded other documents --}}
        @foreach($otherDocs as $doc)
        @php
        $extension = pathinfo($doc->file_name, PATHINFO_EXTENSION);
        $isImage = in_array(strtolower($extension), ['jpg','jpeg','png','gif','webp']);
        $filePath = asset('storage/' . $doc->file_path);
        @endphp
        <div class="uploaded-item d-flex align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2 flex-grow-1">
                @if($isImage)
                <a href="#" class="img-preview-link" data-img="{{ $filePath }}">
                    <img src="{{ $filePath }}" style="max-width:50px; border-radius:3px;">
                </a>
                @else
                <i class="fa-solid fa-file-lines fa-lg text-muted"></i>
                @endif
                <span>{{ $doc->document_type }}</span>
            </div>
            <div class="actions d-flex gap-1">
                <a href="{{ route('agent.documents.download', [$student->id, $doc->id]) }}" class="btn text-primary btn-sm">
                    <i class="fa-solid fa-download"></i>
                </a>
                <form action="{{ route('agent.documents.destroy', [$student->id, $doc->id]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm text-danger" onclick="return confirm('Delete this document?')">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Image Preview Modal --}}
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-light">
            <div class="modal-body text-center p-3">
                <img src="" id="modalImage" class="img-fluid rounded" alt="Preview">
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- JS for modal --}}
<script>
    const links = document.querySelectorAll('.img-preview-link');
    const modalEl = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');

    links.forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            const imgUrl = link.dataset.img;
            if (imgUrl) {
                if (typeof bootstrap !== 'undefined' && modalEl) {
                    modalImg.src = imgUrl;
                    let modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (!modalInstance) modalInstance = new bootstrap.Modal(modalEl);
                    modalInstance.show();
                    modalEl.addEventListener('hidden.bs.modal', function handler() {
                        modalImg.src = '';
                        modalEl.removeEventListener('hidden.bs.modal', handler);
                    });
                } else {
                    window.open(imgUrl, '_blank');
                }
            }
        });
    });

</script>

@endsection
