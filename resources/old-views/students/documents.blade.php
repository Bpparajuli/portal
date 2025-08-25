@extends('layout.app')

@section('content')
<div class="p-2">
    <h3>Documents for Application #{{ $application->id }} - {{ $application->university->name }}</h3>

    <ul>
        @foreach($application->documents as $doc)
        <li><a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank">{{ $doc->file_name }}</a></li>
        @endforeach
    </ul>

    <form action="{{ route('application.documents.store', $application->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="documents[]" multiple class="form-control mb-2">
        <button type="submit" class="btn btn-primary">Upload More Documents</button>
    </form>
</div>
@endsection
