@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/document.css') }}">

@section('content')
<div class="upload-section container">
    {{-- ================= Header ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="mb-1">Manage Documents for <span class="text-primary"> <a href="{{ route('agent.students.show', $student->id) }}">{{ $student->first_name }} {{ $student->last_name }}</a></span></h3>
            <small class="text-muted">
                <a href="{{ route('agent.students.applications', $student->id) }}">
                    @php
                    $count = $student->applications->count();
                    @endphp

                    @if($count === 0)
                    No applications
                    @elseif($count === 1)
                    1 Application submitted
                    @else
                    {{ $count }} Applications submitted
                    @endif
                </a>
            </small>
        </div>

    </div>
    {{-- -------------------- Predefined Documents -------------------- --}}
    @php
    $counter = 1;
    @endphp
    <h4>Compulsory Documents</h4>
    <div class="predefined-documents mb-4">
        @foreach($allDocumentTypes as $type)
        @php
        $uploaded = $predefinedDocs->firstWhere('document_type', $type);
        @endphp


        <div class="uploaded-item d-flex align-items-center justify-content-between border rounded p-2 mb-2 bg-light">
            {{-- LEFT: Number + Document Type --}}
            <div class="doc-label" style="width: 25%;">
                <strong>{{ $counter }}. {{ ucwords(str_replace('_',' ',$type)) }}</strong>
            </div>

            {{-- CENTER: Upload Form or Preview --}}
            <div class="doc-content text-center flex-grow-1" style="width: 50%;">
                @if(!$uploaded)
                {{-- Upload Form --}}
                <form action="{{ route('agent.documents.store', $student->id) }}" method="POST" enctype="multipart/form-data" class="d-inline-flex align-items-center gap-2">
                    @csrf
                    <input type="hidden" name="document_type" value="{{ $type }}">
                    <input type="file" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif" required class="form-control form-control-sm" style="width: 250px;">
                    <button type="submit" class="btn btn-success btn-sm">Upload</button>
                </form>
                @else
                @php
                $extension = pathinfo($uploaded->file_name, PATHINFO_EXTENSION);
                $isImage = in_array(strtolower($extension), ['jpg','jpeg','png','gif','webp']);
                $filePath = asset('storage/' . $uploaded->file_path);
                @endphp
                <div class="d-inline-flex align-items-center gap-2">
                    <a href="#" data-preview="{{ $filePath }}">
                        @if($isImage)
                        <img src="{{ $filePath }}" style="width:50px; height:50px; object-fit:cover; border-radius:3px;">
                        @else
                        <i class="fa-solid fa-file-lines fa-lg text-muted"></i>
                        @endif
                    </a>
                    <span class="text-truncate" style="max-width: 200px;">{{ basename($uploaded->file_name) }}</span>
                </div>
                @endif
            </div>

            {{-- RIGHT: Actions --}}
            <div class="actions text-end" style="width: 20%;">
                @if($uploaded)
                <a href="{{ route('agent.documents.download', [$student->id, $uploaded->id]) }}" class="btn text-primary btn-sm" title="Download">
                    <i class="fa-solid fa-download"></i>
                </a>
                <form action="{{ route('agent.documents.destroy', [$student->id, $uploaded->id]) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn text-danger btn-sm" title="Delete" onclick="return confirm('Delete this document?')">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @php $counter++; @endphp

        @endforeach
    </div>

    {{-- ✅ Apply Now if all compulsory uploaded --}}
    @if(count($predefinedDocs) === count($allDocumentTypes))
    <div class="apply-now-box text-center p-3 bg-light rounded shadow-sm mb-4">
        <h5 class="text-success mb-2">✅ 10/10 All required documents have been uploaded</h5>
        <a href="{{ route('agent.applications.create') }}?student_id={{ $student->id }}" class="btn btn-lg btn-success px-4">
            <i class="fa-solid fa-paper-plane me-2"></i> Apply Now
        </a>
    </div>
    @endif

    <hr>

    {{-- -------------------- Other Documents -------------------- --}}
    <h4 class="bg-primary p-2 text-white rounded-1">Other Documents</h4>
    <div class="other-documents mb-4">
        {{-- Upload new other document --}}
        <form action="{{ route('agent.documents.storeOther', $student->id) }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center gap-2 mb-3">
            @csrf
            <x-form.file name="file" label="select file" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif" />
            <input type="text" name="custom_name" class="form-control form-control-sm" placeholder="Document Name" required>
            <button type="submit" class="btn btn-success btn-sm">Upload</button>
        </form>

        {{-- Display uploaded other documents --}}
        @forelse($otherDocs as $doc)
        @php
        $extension = pathinfo($doc->file_name, PATHINFO_EXTENSION);
        $isImage = in_array(strtolower($extension), ['jpg','jpeg','png','gif','webp']);
        $filePath = asset('storage/' . $doc->file_path);
        @endphp
        <div class="uploaded-item d-flex align-items-center justify-content-between gap-2 mb-2 border rounded p-2 bg-light">
            <div class="d-flex align-items-center gap-2 flex-grow-1">
                <a href="#" data-preview="{{ $filePath }}">
                    @if($isImage)
                    <img src="{{ $filePath }}" style="max-width:50px; border-radius:3px;">
                    @else
                    <i class="fa-solid fa-file-lines fa-lg text-muted"></i>
                    @endif
                </a>
                <span>{{ $doc->custom_name ?? $doc->document_type }}</span>
            </div>
            <div class="actions d-flex gap-1">
                <a href="{{ route('agent.documents.download', [$student->id, $doc->id]) }}" class="btn text-primary btn-sm" title="Download">
                    <i class="fa-solid fa-download"></i>
                </a>
                <form action="{{ route('agent.documents.destroy', [$student->id, $doc->id]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm text-danger" title="Delete" onclick="return confirm('Delete this document?')">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <p class="text-muted">No other documents uploaded yet.</p>
        @endforelse
    </div>
</div>
@endsection
