@extends('layouts.app')

@section('content')
<div class="mt-4">
    <h4>Upload document for: <strong>{{ $student->first_name }} {{ $student->last_name }}</strong></h4>

    <form action="{{ route('admin.documents.store', $student->id) }}" method="POST" enctype="multipart/form-data" class="mt-3">
        @csrf

        <div class="mb-3">
            <label class="form-label">File</label>
            <input type="file" name="file" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Document type (optional)</label>
            <input type="text" name="document_type" class="form-control" placeholder="e.g. passport, transcript">
        </div>

        <button class="btn btn-success">Upload</button>
        <a href="{{ route('admin.documents.index', $student->id) }}" class="btn btn-outline-secondary">Back</a>
    </form>
</div>
@endsection
