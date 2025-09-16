@foreach($requiredDocs as $key => $label)
<div class="doc-item">
    <strong>{{ $label }}:</strong>
    @if(isset($uploadedDocs[$key]) && count($uploadedDocs[$key]) > 0)
    <span class="text-success">Uploaded ✅</span>
    <ul>
        @foreach($uploadedDocs[$key] as $doc)
        <li>
            <a href="{{ Storage::url($doc->file_path) }}" target="_blank">{{ $doc->file_name }}</a>
        </li>
        @endforeach
    </ul>
    @else
    <span class="text-danger">Missing ❌</span>
    <form action="{{ route('agent.documents.store', $student->id) }}" method="POST" enctype="multipart/form-data" class="upload-form">
        @csrf
        <input type="hidden" name="document_type" value="{{ $key }}">
        <input type="file" name="file" required>
        <button type="submit" class="btn btn-sm btn-primary">Upload</button>
    </form>
    @endif
</div>
@endforeach
