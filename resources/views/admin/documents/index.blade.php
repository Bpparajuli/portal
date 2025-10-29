@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/document.css') }}">

@section('content')
<div class="upload-section container">
    <h2 class="mb-4">Manage Documents for {{ trim($student->first_name . ' ' . $student->last_name) }}</h2>

    {{-- Upload Form --}}
    @if(count($availableTypes) > 0)
    <form action="{{ route('admin.documents.store', $student->id) }}" method="POST" enctype="multipart/form-data" class="upload-form mb-4">
        @csrf

        <div class="upload-box d-flex align-items-center gap-3 p-3 rounded" onclick="document.getElementById('document').click()">
            <div>
                <p class="mb-0">Drag & drop <br> Or</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('document').click(); return false;">Click to browse</button>
            </div>
            <input type="file" name="file" id="document" hidden required>
            <small class="text-muted ms-auto">* Max file size: 50MB</small>
        </div>

        <div class="d-flex align-items-start gap-3 mt-3">
            <div id="file-preview" style="display:none;">
                <strong>Selected File:</strong> <span id="file-name"></span>
                <img id="img-preview" style="max-width:150px; display:none; margin-top:10px; border-radius:5px;">
            </div>

            <div class="form-group mt-0 flex-grow-1">
                <label>Select Document Type</label>
                <select name="document_type" class="form-control" required>
                    <option value="">-- Choose Type --</option>
                    @foreach($availableTypes as $type)
                    <option value="{{ $type }}">{{ ucwords(str_replace('_',' ', $type)) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="btn-group mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-success">Upload</button>
                <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </form>
    @endif

    {{-- Uploaded Documents --}}
    <h3 class="mb-3">Uploaded Documents</h3>
    <div class="uploaded-files-grid">
        @foreach($documents as $doc)
        @php
        $extension = pathinfo($doc->file_name, PATHINFO_EXTENSION);
        $isImage = in_array(strtolower($extension), ['jpg','jpeg','png','gif','webp']);
        $filePath = asset('storage/' . $doc->file_path);
        @endphp

        <div class="uploaded-file card p-2">
            @if($isImage)
            <a href="#" class="img-preview-link" data-img="{{ $filePath }}" aria-label="Open image preview">
                <img src="{{ $filePath }}" class="card-img-top doc-preview" alt="Document Preview">
            </a>
            @else
            <div class="doc-placeholder text-center p-3">
                <i class="fa-solid fa-file-lines fa-3x text-muted" aria-hidden="true"></i>
                <p class="mt-2">{{ strtoupper($extension) }}</p>
            </div>
            @endif

            <p class="text-center mt-2">{{ ucwords(str_replace('_',' ', $doc->document_type)) }}</p>

            <div class="file-actions d-flex justify-content-center gap-2 mt-2">
                <a href="{{ route('admin.documents.download', [$student->id, $doc->id]) }}" class="btn btn-success btn-sm">
                    <i class="fa-solid fa-download me-1"></i> Download
                </a>

                <form action="{{ route('admin.documents.destroy', [$student->id, $doc->id]) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this document?')">
                        <i class="fa-solid fa-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Image Modal --}}
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

<script>
    const docInput = document.getElementById('document');
    if (docInput) {
        docInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('file-preview');
            const fileName = document.getElementById('file-name');
            const imgPreview = document.getElementById('img-preview');

            if (file) {
                preview.style.display = 'block';
                fileName.textContent = file.name;
                if (file.type.startsWith('image/')) {
                    imgPreview.src = URL.createObjectURL(file);
                    imgPreview.style.display = 'block';
                } else {
                    imgPreview.style.display = 'none';
                }
            }
        });
    }

    (function() {
        const links = document.querySelectorAll('.img-preview-link');
        const modalEl = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const imgUrl = this.dataset.img;
                if (!imgUrl) return;

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
            });
        });
    })();

</script>
@endsection
