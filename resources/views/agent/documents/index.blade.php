@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/document.css') }}">
@section('content')
<div class="upload-section container">
    <h2 class="mb-4">Upload Documents for {{ $student->first_name }} {{ $student->last_name }}</h2>
    {{-- Upload Form --}}
    <form action="{{ route('agent.documents.store', $student->id) }}" method="POST" enctype="multipart/form-data" class="upload-form mb-5">
        @csrf
        <div class="upload-box d-flex align-items-center gap-3 p-3 border rounded" style="cursor:pointer;" onclick="document.getElementById('document').click()">
            <p class="mb-0">Drag & drop <br> Or</p>
            <button type="button" class="btn btn-primary">Click to browse</button>
            <input type="file" name="file" id="document" hidden required>
            <small class="text-muted ms-auto">* Max file size: 50MB</small>
        </div>

        <div class="d-flex align-items-center justify-content-center gap-3 mt-3">
            {{-- File Preview --}}
            <div id="file-preview" style="display:none;">
                <strong>Selected File:</strong> <span id="file-name"></span>
                <img id="img-preview" style="max-width:150px; display:none; margin-top:10px; border-radius:5px;">
            </div>

            {{-- Document Type --}}
            <div class="form-group mt-0 flex-grow-1">
                <label>Select Document Type</label>
                @php
                $uploadedTypes = $student->documents->pluck('document_type')->toArray();
                @endphp
                <select name="document_type" class="form-control" required>
                    <option value="">-- Choose Type --</option>
                    <option value="passport" @if(in_array('passport', $uploadedTypes)) disabled @endif>Passport</option>
                    <option value="id" @if(in_array('id', $uploadedTypes)) disabled @endif>National ID</option>
                    <option value="transcript" @if(in_array('transcript', $uploadedTypes)) disabled @endif>Transcript</option>
                    <option value="financial" @if(in_array('financial', $uploadedTypes)) disabled @endif>Financial Document</option>
                    <option value="other" @if(in_array('other', $uploadedTypes)) disabled @endif>Other</option>
                </select>
            </div>

            {{-- Buttons --}}
            <div class="btn-group mt-3 d-flex flex-row gap-2">
                <a href="{{ route('agent.students.show', $student->id) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </div>
    </form>

    {{-- Uploaded Documents --}}
    <h3 class="mb-3">Uploaded Documents</h3>
    <div class="uploaded-files-grid d-flex flex-wrap gap-3">
        @foreach($student->documents as $doc)
        @php
        $extension = pathinfo($doc->file_name, PATHINFO_EXTENSION);
        $isImage = in_array(strtolower($extension), ['jpg','jpeg','png','gif','webp']);
        $filePath = asset('storage/' . $doc->file_path);
        @endphp

        <div class="uploaded-file card p-2" style="width:180px;">
            @if($isImage)
            <img src="{{ $filePath }}" class="card-img-top doc-preview mb-2" alt="Document Preview" style="border-radius:5px;">
            @else
            <div class="doc-placeholder text-center p-4 border rounded mb-2">
                <i class="bi bi-file-earmark-text" style="font-size:48px;"></i>
                <p class="mt-2">{{ strtoupper($extension) }}</p>
            </div>
            @endif

            <p class="text-center mt-1 small">{{ $doc->document_type }}<br>{{ $doc->file_name }}</p>

            <div class="file-actions d-flex justify-content-center gap-2 mt-2 flex-wrap">
                <a href="{{ $filePath }}" target="_blank" class="btn btn-info btn-sm">👁 View</a>
                <a href="{{ route('agent.documents.download', [$student->id, $doc->id]) }}" class="btn btn-success btn-sm">⬇ Download</a>
                <form action="{{ route('agent.documents.destroy', [$student->id, $doc->id]) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">🗑 Delete</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- JS for file preview --}}
<script>
    document.getElementById('document').addEventListener('change', function(event) {
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

</script>

@endsection
