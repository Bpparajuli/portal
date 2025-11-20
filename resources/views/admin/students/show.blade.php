@extends('layouts.app')

@section('content')
<div class="p-3">
    <div class="row">

        {{-- Left Column: Profile Card --}}
        <div class="col-lg-4">
            <div class="card sticky-top" style="top:20px;" data-state="#about">
                {{-- Avatar --}}
                <img class="card-avatar" src="{{ $student->students_photo ? asset('storage/' . $student->students_photo) : asset('images/default-user.png') }}" alt="avatar" />

                {{-- Name and Course --}}
                <h1 class="card-fullname">{{ $student->first_name }} {{ $student->last_name }}</h1>
                <h2 class="card-jobtitle">{{ $student->agent?->business_name ?? $student->agent?->username ?? 'No Agent Selected' }}</h2>

                {{-- Card Main --}}
                <div class="card-main">
                    <div class="card-section is-active" id="about">
                        <div class="card-content text-start">
                            <div class="card-subtitle">PROFILE INFO</div>
                            <p><strong>University:</strong> {{ $student->university?->name ?? '-' }}</p>
                            <p><strong>Preferred Country:</strong> {{ $student->preferred_country ?? '-' }}</p>
                            <p><strong>Preferred Course:</strong> {{ $student->preferred_course ?? '-' }}</p>
                        </div>

                        {{-- Actions --}}
                        @php
                        // Document status calculation
                        $requiredDocumentTypes = ['passport','id','transcript','financial','other'];
                        $uploadedTypes = $student->documents->pluck('document_type')
                        ->map(fn($t) => strtolower(str_replace(' ', '', $t)))
                        ->toArray();
                        $allDocumentsUploaded = count(array_diff($requiredDocumentTypes, $uploadedTypes)) === 0;
                        $completedDocsCount = $student->documents->where('status','completed')->count();

                        $documentStatus = ($allDocumentsUploaded && $completedDocsCount == count($requiredDocumentTypes))
                        ? 'Completed'
                        : (count($uploadedTypes) == 0 ? 'Not Uploaded' : 'Incomplete');
                        @endphp
                        <div class="card-social">
                            <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-dark btn-sm">✏️ Edit</a>
                            @if($allDocumentsUploaded)
                            <a href="{{ route('admin.applications.create') }}?student_id={{ $student->id }}" class="btn btn-sm btn-success">
                                <i class="fa-solid fa-paper-plane me-1"></i> Apply Now
                            </a>
                            @else
                            <a href="{{ route('admin.documents.index', $student->id) }}" class="btn btn-sm btn-secondary ">
                                <i class="fa-solid fa-folder-open me-1"></i> View Docs
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Tabs --}}
        <div class="col-lg-8">
            {{-- Tabs --}}
            <ul class="nav nav-tabs custom-tabs mb-3" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#general">👤 Information</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#documents">📂 Documents</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#application">📑 Application</button></li>
            </ul>

            <div class="tab-content ">
                {{-- General --}}
                <div class="tab-pane fade show active" id="general">
                    <div class="bg-light rounded m-2 p-2 shadow-sm ">
                        <h5 class="mb-3">General Info</h5>
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
                            <div class="col-md-6 my-2"><strong>Permanent Address:</strong> {{ $student->permanent_address ?? '-' }}</div>
                            <div class="col-md-6 my-2"><strong>Temporary Address:</strong> {{ $student->temporary_address ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="bg-light rounded m-2 p-2 shadow-sm" id="general">
                        <h5 class="mb-3">Academic Info</h5>
                        <div class="row">
                            <div class="col-md-6 my-2"><strong>Qualification:</strong> {{ $student->qualification ?? '-' }}</div>
                            <div class="col-md-6 my-2"><strong>Passed Year:</strong> {{ $student->passed_year ?? '-' }}</div>
                            <div class="col-md-6 my-2"><strong>Gap Years:</strong> {{ $student->gap ?? '-' }}</div>
                            <div class="col-md-6 my-2"><strong>Last Grades:</strong> {{ $student->last_grades ?? '-' }}</div>
                            <div class="col-md-6 my-2"><strong>Education Board:</strong> {{ $student->education_board ?? '-' }}</div>
                            <div class="col-md-6 my-2"><strong>Preferred Country:</strong> {{ $student->preferred_country ?? '-' }}</div>
                            <div class="col-md-6 my-2"><strong>Preferred Course:</strong> {{ $student->preferred_course ?? '-' }}</div>
                        </div>
                    </div>
                </div>
                {{-- Application --}}
                <div class="tab-pane fade" id="application">
                    <h5 class="mb-3">📄 Applications List with Details</h5>
                    @if($student->applications && $student->applications->isNotEmpty())
                    @foreach($student->applications as $application)
                    <div class="card shadow-sm bg-light mb-4 border-0 rounded-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-3 text-primary">
                                    Application Number: {{ $application->application_number ?? 'N/A' }}
                                </h6>
                                <h6 class="mb-3 text-primary">
                                    Application Submitted On: {{ $application->created_at->format('Y-m-d') }}
                                </h6>
                            </div>
                            {{-- University Info --}}
                            <div class=" row">
                                <div class="col-md-6 my-2"><strong>Country:</strong> {{ $application->university->country ?? 'N/A' }}</div>
                                <div class="col-md-6 my-2"><strong>City:</strong> {{ $application->university->city ?? 'N/A' }}</div>
                                <div class="col-md-6 my-2"><strong>University:</strong> {{ $application->university->name ?? 'N/A' }}</div>
                                <div class="col-md-6 my-2"><strong>Course:</strong> {{ $application->course->title ?? 'N/A' }}</div>
                                <div class="col-md-6 my-2"><strong>Duration:</strong> {{ $application->course->duration ?? 'N/A' }}</div>
                                <div class="col-md-6 my-2"><strong>Fee:</strong> {{ $application->course->fee ?? 'N/A' }}</div>
                            </div>
                            <div class="d-flex justify-content-between">
                                {{-- Application Staus  --}}
                                <div class="p-3 bg-white border rounded-3">
                                    <h6 class="fw-bold">📌 Application Status</h6>
                                    <div class="m-1 p-3 rounded-2 badge {{ $application->status_class }}">
                                        {{ $application->application_status }}
                                    </div>
                                </div>
                                {{-- SOP --}}
                                <div class="p-3 bg-white border rounded-3">
                                    <h6 class="fw-bold">📑 Statement of Purpose</h6>
                                    <div class="m-1">
                                        @if($application->sop_file)
                                        <a href="#" data-preview="{{ Storage::url($application->sop_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            👁️ SOP
                                        </a>
                                        @else
                                        <span class="text-muted">Not uploaded</span>
                                        @endif
                                    </div>
                                </div>
                                {{-- All documents --}}
                                <div class="p-3 bg-white border rounded-3">
                                    <h6 class="fw-bold">Actions</h6>
                                    <div class="m-1 d-flex gap-1">
                                        <div class="">
                                            <a href="{{ route('admin.applications.edit', $application->id) }}" class="btn p-2 border border-success">
                                                📝 Edit
                                            </a>
                                        </div>
                                        <div class="">
                                            <a href="{{ route('admin.applications.show', $application->id) }}" class="btn p-2 border border-secondary">
                                                👁️ View
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Application Message Section --}}
                            <div class="mt-4 p-3 bg-white border rounded-3">
                                <h5 class="fw-bold mb-3">💬 Messages</h5>
                                <div class="border rounded p-3 mb-3" style="max-height: 400px; overflow-y: auto;">
                                    @forelse($application->messages as $m)
                                    <div class="d-flex mb-2 {{ $m->type === 'agent' ? 'justify-content-start' : 'justify-content-end' }}">
                                        <div class="p-2 rounded" style="max-width:70%; background-color: {{ $m->type === 'agent' ? '#f1f1f1' : '#1a0262' }};
                                       color: {{ $m->type === 'agent' ? '#000' : '#fff' }};">
                                            <p class="mb-1 fw-semibold">{{ $m->message }}</p>
                                            <small><b>{{ $m->user->name ?? 'Unknown' }}</b> • {{ $m->created_at->format('d M Y, H:i') }}</small>
                                        </div>
                                    </div>
                                    @empty
                                    <p class="text-muted">No messages yet.</p>
                                    @endforelse
                                </div>
                                <form method="POST" action="{{ route('admin.applications.addMessage', $application) }}">
                                    @csrf
                                    <div class="d-flex">
                                        <textarea name="message" class="form-control me-2" rows="2" placeholder="Add a message..." required></textarea>
                                        <button type="submit" class="btn btn-primary btn-sm">Send</button>
                                    </div>
                                </form>
                            </div>
                            {{-- Actions --}}
                            <div class="mt-3">
                                <a href="{{ route('admin.applications.edit', $application->id) }}" class="btn btn-primary btn-sm">
                                    ✏️ Edit Application
                                </a>
                            </div>
                        </div>
                    </div>

                    @endforeach
                    @else
                    <p class="text-muted">Please Upload all necessary Documents and Add Application for this Student.</p>
                    @endif
                </div>

                {{-- Documents --}}
                <div class="tab-pane fade" id="documents">
                    {{-- Header --}}
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">📂 Documents</h6>
                        @if($allDocumentsUploaded)
                        <a href="{{ route('admin.applications.create') }}?student_id={{ $student->id }}" class="btn btn-sm btn-success">
                            <i class="fa-solid fa-paper-plane me-1"></i> Apply Now
                        </a>
                        @else
                        <a href="{{ route('admin.documents.index', $student->id) }}" class="btn btn-sm btn-secondary">
                            <i class="fa-solid fa-folder-open me-1"></i> View All Docs
                        </a>
                        @endif
                    </div>

                    @if($student->documents->isEmpty())
                    <p class="text-muted small mb-0">No documents uploaded yet.</p>
                    @else
                    <div class="row g-2">
                        @foreach($student->documents as $doc)
                        @php
                        $extension = pathinfo($doc->file_name, PATHINFO_EXTENSION);
                        $isImage = in_array(strtolower($extension), ['jpg','jpeg','png','gif','webp']);
                        $filePath = asset('storage/' . $doc->file_path);
                        @endphp
                        <div class="col-6 col-md-3">
                            <div class="card document-card shadow-sm p-1 text-center" style="font-size:0.8rem;">
                                {{-- Preview --}}
                                <a href="#" data-preview="{{ $filePath }}">
                                    @if($isImage)
                                    <img src="{{ $filePath }}" class="img-fluid rounded" alt="Doc" style="height:80px; object-fit:cover;">
                                    @else
                                    <div class="doc-placeholder py-3">
                                        <i class="fa fa-file fa-2x text-secondary"></i>
                                        <div>{{ strtoupper($extension) }}</div>
                                    </div>
                                    @endif
                                </a>

                                {{-- Info & Actions --}}
                                <div class="mt-1">
                                    <div class="text-truncate">{{ $doc->custom_name ?? ucwords(str_replace('_',' ', $doc->document_type)) }}</div>
                                    <small class="text-muted">{{ $doc->created_at->format('Y-m-d') }}</small>
                                </div>

                                <div class="d-flex px-2 justify-content-between gap-1 mt-1">
                                    <a href="{{ route('admin.documents.download', ['student'=>$student->id, 'document'=>$doc->id]) }}" class="btn btn-sm btn-success p-1">
                                        <i class="fa fa-download"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.documents.destroy', [$student->id, $doc->id]) }}" onsubmit="return confirm('Delete this document?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger p-1"><i class="fa fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

            </div>
            {{-- Row 2 Tabs 
            <ul class="nav nav-pills mb-3" id="row2Tabs" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#chat">💬 Chats</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#remarks">📝 Remarks</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#settings">⚙️ Settings</button></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="chat">
                    <div class="card shadow-sm p-3 rounded">
                        <h5>Chats</h5>
                         @if($student->chats->count())
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
            @endif
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
            <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">🗑️ Delete Student</button>
            </form>
        </div>
    </div>
</div>
--}}
</div>
</div>
</div>

@endsection
