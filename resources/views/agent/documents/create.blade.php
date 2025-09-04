{{-- students/documents/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">Upload Document for {{ $student->name }}</div>
    <div class="card-body">
        <form action="{{ route('admin.students.documents.store', $student->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label for="document_type">Document Type</label>
                <select name="document_type" id="document_type" class="form-control">
                    <option value="passport">Passport</option>
                    <option value="transcript">Transcript</option>
                    <option value="financial">Financial Statement</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="file">File</label>
                <input type="file" name="file" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="notes">Notes</label>
                <textarea name="notes" class="form-control"></textarea>
            </div>

            <button type="submit" class="btn btn-success">Upload</button>
            <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
