@extends('layouts.app')

@section('content')
<div class=" mt-4">
    <div class="d-flex justify-content-between">
        <h4>Documents for {{ $student->first_name }} {{ $student->last_name }}</h4>
        <a href="{{ route('admin.documents.create', $student->id) }}" class="btn btn-success">Upload Document</a>
    </div>

    <div class="mt-3">
        @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

        @if($documents->isEmpty())
        <p class="text-muted">No documents uploaded yet.</p>
        @else
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Type</th>
                        <th>Uploaded By</th>
                        <th>Uploaded At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $doc)
                    <tr>
                        <td><a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank">{{ $doc->file_name }}</a></td>
                        <td>{{ $doc->document_type ?? '-' }}</td>
                        <td>{{ $doc->uploader?->business_name ?? $doc->uploader?->username ?? 'System' }}</td>
                        <td>{{ $doc->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.documents.download', $doc->id) }}" class="btn btn-sm btn-outline-primary">Download</a>

                            <form action="{{ route('admin.documents.destroy', $doc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete doc?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
