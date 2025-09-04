@extends('layouts.app')

@section('content')
<div class="my-4">
    <div class="row">
        {{-- Left Column: Profile --}}
        <div class="col-lg-4">
            <div class="card shadow-sm mb-3 p-3 rounded text-center">
                @if($student->students_photo)
                <img src="{{ asset($student->students_photo) }}" class="img-fluid rounded-circle mb-3" alt="Student Photo" width="250" height="250">
                @else
                <img src="{{ asset('images/default-user.png') }}" class="img-fluid rounded-circle mb-3" alt="No Photo" width="250" height="250">
                @endif

                <h5>{{ $student->first_name }} {{ $student->last_name }}</h5>
                <p><strong>Status:</strong>
                    <span class="badge 
                        @if($student->student_status == 'accepted') bg-success
                        @elseif($student->student_status == 'rejected') bg-danger
                        @elseif($student->student_status == 'created') bg-secondary
                        @elseif($student->student_status == 'viewed') bg-info
                        @elseif($student->student_status == 'applied to university') bg-warning
                        @elseif($student->student_status == 'applied to another university') bg-primary
                        @elseif($student->student_status == 'forwarded to embassy') bg-dark
                        @else bg-secondary @endif">
                        {{ ucfirst($student->student_status ?? 'N/A') }}
                    </span>
                </p>
                <hr>
                <div class="text-start">
                    <p><strong>Course:</strong> {{ $student->course?->title ?? '-' }}</p>
                    <p><strong>University:</strong> {{ $student->university?->name ?? '-' }}</p>
                    <p><strong>Agent:</strong> {{ $student->agent?->business_name ?? $student->agent?->username ?? 'N/A' }}</p>
                </div>
                <hr>
                <a href="{{ route('agent.students.edit', $student->id) }}" class="btn btn-warning w-100 mb-2">✏️ Edit</a>
                <a href="{{ route('agent.documents.create', $student->id) }}" class="btn btn-primary w-100">📄 Upload Doc</a>
            </div>
        </div>

        {{-- Right Column: Tabs --}}
        <div class="col-lg-8">
            {{-- Row 1 Tabs --}}
            <ul class="nav nav-pills mb-3" id="row1Tabs" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#general">👤 General</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#academic">🎓 Academic</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#application">📑 Application</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#documents">📄 Documents</button></li>
            </ul>

            <div class="tab-content mb-4">
                {{-- General --}}
                <div class="tab-pane fade show active" id="general">
                    <div class="card shadow-sm p-3 rounded">
                        <h5>General Info</h5>
                        <div class="row">
                            <div class="col-md-6 my-2"><strong>Name:</strong> {{ $student->first_name }} {{ $student->last_name }}</div>
                            <div class="col-md-6 my-2"><strong>Email:</strong> {{ $student->email }}</div>
                            <div class="col-md-6 my-2"><strong>Phone:</strong> {{ $student->phone_number }}</div>
                            <div class="col-md-6 my-2"><strong>DOB:</strong> {{ $student->dob ?? '-' }}</div>
                            <div class="col-md-6 my-2"><strong>Gender:</strong> {{ $student->gender ?? '-' }}</div>
                            <div class="col-md-6 my-2"><strong>Nationality:</strong> {{ $student->nationality ?? '-' }}</div>
                            <div class="col-md-6 my-2"><strong>Passport No:</strong> {{ $student->passport_number ?? '-' }}</div>
                            <div class="col-md-6 my-2"><strong>Expiry Date:</strong> {{ $student->passport_expiry ?? '-' }}</div>
                            <div class="col-md-6 my-2"><strong>Marital Status:</strong> {{ $student->marital_status ?? '-' }}</div>
                            <div class="col-md-6 my-2"><strong>Follow-up Date:</strong> {{ $student->follow_up_date ?? '-' }}</div>
                            <div class="col-md-6 my-2"><strong>Permanent Address:</strong> {{ $student->permanent_address ?? '-' }}</div>
                            <div class="col-md-6 my-2"><strong>Temporary Address:</strong> {{ $student->temporary_address ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Academic --}}
                <div class="tab-pane fade" id="academic">
                    <div class="card shadow-sm p-3 rounded">
                        <h5>Academic Info</h5>
                        <div class="row">
                            <div class="col-md-6 my-2"><strong>Qualification:</strong> {{ $student->qualification ?? '-' }}</div>
                            <div class="col-md-6 my-2"><strong>Passed Year:</strong> {{ $student->passed_year ?? '-' }}</div>
                            <div class="col-md-6 my-2"><strong>Gap Years:</strong> {{ $student->gap ?? '-' }}</div>
                            <div class="col-md-6 my-2"><strong>Last Grades:</strong> {{ $student->last_grades ?? '-' }}</div>
                            <div class="col-md-6 my-2"><strong>Education Board:</strong> {{ $student->education_board ?? '-' }}</div>
                            <div class="col-md-6 my-2"><strong>Preferred Country:</strong> {{ $student->preferred_country ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Application --}}
                <div class="tab-pane fade" id="application">
                    <div class="card shadow-sm p-3 rounded">
                        <h5>Application Details</h5>
                        <p><strong>Agent:</strong> {{ $student->agent?->business_name ?? 'N/A' }}</p>
                        <p><strong>University:</strong> {{ $student->university?->name ?? 'N/A' }}</p>
                        <p><strong>Course:</strong> {{ $student->course?->title ?? 'N/A' }}</p>
                        <p><strong>Status:</strong> {{ ucfirst($student->student_status) ?? '-' }}</p>
                        <p><strong>Notes:</strong> {{ $student->notes ?? '-' }}</p>
                        <p><strong>Created At:</strong> {{ $student->created_at->format('Y-m-d') }}</p>
                        <p><strong>Updated At:</strong> {{ $student->updated_at->format('Y-m-d') }}</p>
                    </div>
                </div>

                {{-- Documents --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>📂 Documents</h5>
                    <a href="{{ route('agent.students.documents.create', $student->id) }}" class="btn btn-primary btn-sm">
                        + Upload Document
                    </a>
                </div>

                @if($student->documents->isEmpty())
                <p>No documents uploaded yet.</p>
                @else
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>File</th>
                            <th>Notes</th>
                            <th>Uploaded By</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($student->documents as $doc)
                        <tr>
                            <td>{{ $doc->document_type ?? '—' }}</td>
                            <td>{{ $doc->file_name }}</td>
                            <td>{{ $doc->notes }}</td>
                            <td>{{ $doc->uploader->username ?? $doc->uploader->business_name }}</td>
                            <td>{{ $doc->created_at->format('Y-m-d') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.documents.download', $doc->id) }}" class="btn btn-sm btn-outline-primary">⬇️</a>
                                <a href="{{ route('admin.students.documents.edit', [$student->id, $doc->id]) }}" class="btn btn-sm btn-outline-warning">✏️</a>
                                <form method="POST" action="{{ route('admin.students.documents.destroy', [$student->id, $doc->id]) }}" class="d-inline" onsubmit="return confirm('Delete this document?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">🗑</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif

            </div>

            {{-- Row 2 Tabs --}}
            <ul class="nav nav-pills mb-3" id="row2Tabs" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#chat">💬 Chats</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#remarks">📝 Remarks</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#settings">⚙️ Settings</button></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="chat">
                    <div class="card shadow-sm p-3 rounded">
                        <h5>Chats</h5>
                        {{-- @if($student->chats->count())
                        <ul class="list-group">
                            @foreach($student->chats as $chat)
                            <li class="list-group-item">
                                <strong>{{ $chat->user->username ?? $chat->user->business_name ?? 'Unknown' }}:</strong>
                        {{ $chat->message }}
                        <span class="text-muted float-end">{{ $chat->created_at->diffForHumans() }}</span>
                        </li>
                        @endforeach
                        </ul>
                        @else
                        <p>No chats available.</p>
                        @endif --}}
                    </div>
                </div>

                <div class="tab-pane fade" id="remarks">
                    <div class="card shadow-sm p-3 rounded">
                        <h5>Remarks</h5>
                        {{ $student->notes ?? 'No remarks.' }}
                    </div>
                </div>

                <div class="tab-pane fade" id="settings">
                    <div class="card shadow-sm p-3 rounded">
                        <h5>Settings</h5>
                        <form action="{{ route('agent.students.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">🗑️ Delete Student</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
