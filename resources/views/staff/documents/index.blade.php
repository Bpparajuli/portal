@extends('layouts.staff')

@section('staff-content')
<div class="container-fluid p-4">
    <x-page-header :title="$student->full_name . ' - Documents'" subtitle="Manage student documents">
        <x-slot:actions>
            <a href="{{ route('staff.student.show', $student) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to Student
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="row g-4">
        @foreach($allDocumentTypes as $type)
        <div class="col-md-6 col-lg-4">
            @php $doc = $predefinedDocs->firstWhere('document_type', $type); @endphp
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
                        <a href="{{ route('staff.documents.download', [$student, $doc]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download"></i>
                        </a>
                        <form action="{{ route('staff.documents.destroy', [$student, $doc]) }}" method="POST" onsubmit="return confirm('Delete this document?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                    @else
                    <form action="{{ route('staff.documents.store', $student) }}" method="POST" enctype="multipart/form-data" class="mt-2">
                        @csrf
                        <input type="hidden" name="document_type" value="{{ $type }}">
                        <div class="input-group input-group-sm">
                            <input type="file" name="file" class="form-control form-control-sm" required accept=".jpg,.jpeg,.png,.pdf">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-upload"></i></button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($otherDocs->isNotEmpty())
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white"><h6 class="mb-0">Other Documents</h6></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr><th>Name</th><th>Uploaded</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($otherDocs as $doc)
                    <tr>
                        <td>{{ $doc->custom_name ?? $doc->document_type }}</td>
                        <td class="small text-muted">{{ $doc->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('staff.documents.download', [$student, $doc]) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i></a>
                            <form action="{{ route('staff.documents.destroy', [$student, $doc]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white"><h6 class="mb-0">Upload Other Document</h6></div>
        <div class="card-body">
            <form action="{{ route('staff.documents.storeOther', $student) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="custom_name" class="form-control form-control-sm" placeholder="Document name" required>
                    </div>
                    <div class="col-md-4">
                        <input type="file" name="file" class="form-control form-control-sm" required accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-upload me-1"></i>Upload</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
