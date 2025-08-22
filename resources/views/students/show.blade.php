@extends('layout.app')

@section('content')
<div class="mt-4">
    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-lg rounded">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Student Profile</h4>
        </div>
        <div class="card-body">
            @if(isset($student))
            <div class="row">
                {{-- Left Column --}}
                <div class="col-md-4 text-center">
                    <img src="{{ $student->profile_picture ?? 'https://via.placeholder.com/150' }}" alt="Profile Picture" class="img-fluid rounded-circle mb-3" width="150">
                    <h5>{{ $student->first_name }} {{ $student->last_name }}</h5>
                    <p class="text-muted">Student ID: {{ $student->id }}</p>
                </div>

                {{-- Right Column --}}
                <div class="col-md-8">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Full Name</th>
                                <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $student->email }}</td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td>{{ $student->phone }}</td>
                            </tr>
                            <tr>
                                <th>Address</th>
                                <td>{{ $student->address }}</td>
                            </tr>
                            <tr>
                                <th>Date of Birth</th>
                                <td>{{ $student->dob ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td>{{ $student->created_at->format('d M Y') }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('students.edit', $student->id) }}" class="btn btn-warning me-2">Edit Profile</a>
                        <a href="{{ route('students.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
            </div>
            @else
            <p class="text-danger">No student data found.</p>
            @endif
        </div>
    </div>
</div>
@endsection
</div>

<!-- Applications -->
<div class="tab-pane fade" id="applications" role="tabpanel">
    @include('students.applications', ['applications' => $student->applications])
</div>

<!-- Documents -->
<div class="tab-pane fade" id="documents" role="tabpanel">
    @include('students.documents', ['documents' => $student->documents])
</div>

<!-- Embassy Docs -->
<div class="tab-pane fade" id="embassy" role="tabpanel">
    @include('students.embassy_docs', ['embassyDocs' => $student->embassyDocs])
</div>

<!-- Chat -->
<div class="tab-pane fade" id="chat" role="tabpanel">
    @include('students.chat', ['chats' => $student->chats])
</div>

<!-- Notes -->
<div class="tab-pane fade" id="notes" role="tabpanel">
    @include('students.notes', ['notes' => $student->notes])
</div>
</div>
</div>
