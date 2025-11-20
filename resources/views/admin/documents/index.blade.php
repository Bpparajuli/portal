@extends('layouts.app')
@section('title', 'Manage Documents')
<link rel="stylesheet" href="{{ asset('css/document.css') }}">

@section('content')
<div class="container py-4">

    {{-- ================= Header ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="mb-1">Manage Documents for <span class="text-primary">{{ $student->first_name }} {{ $student->last_name }}</span></h3>
            <small class="text-muted">
                Agent: <a href="{{ route('admin.users.show', $student->agent->id) }}">{{ $student->agent->business_name }}</a> |
                <a href="{{ route('admin.students.show', $student->id) }}">View Student</a> |
                <a href="{{ route('admin.applications.index') }}?student_id={{ $student->id }}">View Applications</a>
            </small>
        </div>
        <a href="{{ route('admin.documents.downloadAll', $student->id) }}" class="btn btn-success btn-md">
            <i class="fa-solid fa-download"> Download All
            </i>
        </a>
    </div>

    @php
    $counter = 1;
    @endphp

    {{-- ================= Compulsory Documents ================= --}}
    <h4>Compulsory Documents</h4>
    <div class="predefined-documents mb-4">
        @foreach($allDocumentTypes as $type)
        @php
        $uploaded = $predefinedDocs->firstWhere('document_type', $type);
        if($uploaded){
        $extension = pathinfo($uploaded->file_name, PATHINFO_EXTENSION);
        $isImage = in_array(strtolower($extension), ['jpg','jpeg','png','gif','webp']);
        $filePath = asset('storage/' . $uploaded->file_path);
        }
        @endphp

        <div class="uploaded-item d-flex align-items-center justify-content-between border rounded p-2 mb-2 bg-light">
            {{-- LEFT: Number + Document Type --}}
            <div class="doc-label" style="width: 25%;">
                <strong>{{ $counter }}. {{ ucwords(str_replace('_',' ',$type)) }}</strong>
            </div>

            {{-- CENTER: Upload Form or Preview --}}
            <div class="doc-content text-center flex-grow-1" style="width: 50%;">
                @if(!$uploaded)
                <div class="text-muted">Not uploaded yet</div>
                @else
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
                <a href="{{ route('admin.documents.download', [$student->id, $uploaded->id]) }}" class="btn text-primary btn-sm" title="Download">
                    <i class="fa-solid fa-download"></i>
                </a>
                <form action="{{ route('admin.documents.destroy', [$student->id, $uploaded->id]) }}" method="POST" class="d-inline">
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

    {{-- ================= Other Documents ================= --}}
    <h4>Other Documents</h4>
    <div class="other-documents mb-4">
        {{-- Display Uploaded Other Documents --}}
        @forelse($otherDocs as $doc)
        @php
        $extension = pathinfo($doc->file_name, PATHINFO_EXTENSION);
        $isImage = in_array(strtolower($extension), ['jpg','jpeg','png','gif','webp']);
        $filePath = asset('storage/' . $doc->file_path);
        @endphp
        <div class="uploaded-item d-flex align-items-center justify-content-between gap-2 mb-2 border rounded p-2 bg-light">
            {{-- LEFT: Number + Document Name --}}
            <div class="doc-label" style="width: 25%;">
                <strong>{{ $counter }}. {{ $doc->custom_name ?? $doc->document_type }}</strong>
            </div>

            {{-- CENTER: Preview --}}
            <div class="doc-content text-center flex-grow-1" style="width: 50%;">
                <a href="#" data-preview="{{ $filePath }}">
                    @if($isImage)
                    <img src="{{ $filePath }}" style="max-width:50px; border-radius:3px;">
                    @else
                    <i class="fa-solid fa-file-lines fa-lg text-muted"></i>
                    @endif
                </a>
            </div>

            {{-- RIGHT: Actions --}}
            <div class="actions d-flex gap-1" style="width: 20%;">
                <a href="{{ route('admin.documents.download', [$student->id, $doc->id]) }}" class="btn text-primary btn-sm" title="Download">
                    <i class="fa-solid fa-download"></i>
                </a>
                <form action="{{ route('admin.documents.destroy', [$student->id, $doc->id]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm text-danger" title="Delete" onclick="return confirm('Delete this document?')">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @php $counter++; @endphp
        @empty
        <p class="text-muted">No other documents uploaded yet.</p>
        @endforelse
    </div>

</div>
@endsection
