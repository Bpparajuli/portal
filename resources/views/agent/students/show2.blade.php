@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="row">

        {{-- Left Column: Profile Card --}}
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <img src="{{ $student->students_photo ? asset('storage/' . $student->students_photo) : asset('images/default-user.png') }}" class="rounded-circle mb-3" width="150" height="150" alt="Profile Photo">
                    <h3 class="mb-1">{{ $student->first_name }} {{ $student->last_name }}</h3>
                    <p class="text-muted mb-2">{{ $student->course?->title ?? 'No Course Selected' }}</p>

                    <span class="badge 
                        @if($student->student_status == 'accepted') bg-success
                        @elseif($student->student_status == 'rejected') bg-danger
                        @elseif($student->student_status == 'created') bg-secondary
                        @elseif($student->student_status == 'viewed') bg-info
                        @elseif($student->student_status == 'applied to university') bg-warning
                        @elseif($student->student_status == 'applied to another university') bg-primary
                        @elseif($student->student_status == 'forwarded to embassy') bg-dark
                        @else bg-secondary @endif mb-3">
                        {{ ucfirst($student->student_status ?? 'N/A') }}
                    </span>

                    <div class="d-grid gap-2">
                        <a href="{{ route('agent.students.edit', $student->id) }}" class="btn btn-warning">✏️ Edit Student</a>
                        <a href="{{ route('agent.documents.index', $student->id) }}" class="btn btn-primary">📄 Manage Documents</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Details --}}
        <div class="col-lg-8">
            <div class="accordion" id="studentDetailsAccordion">

                {{-- Profile Info --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingProfile">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProfile" aria-expanded="true">
                            👤 Profile Info
                        </button>
                    </h2>
                    <div id="collapseProfile" class="accordion-collapse collapse show" data-bs-parent="#studentDetailsAccordion">
                        <div class="accordion-body">
                            <div class="row">
                                <div class="col-md-6 mb-2"><strong>DOB:</strong> {{ $student->dob ?? '-' }}</div>
                                <div class="col-md-6 mb-2"><strong>Gender:</strong> {{ $student->gender ?? '-' }}</div>
                                <div class="col-md-6 mb-2"><strong>Nationality:</strong> {{ $student->nationality ?? '-' }}</div>
                                <div class="col-md-6 mb-2"><strong>Marital Status:</strong> {{ $student->marital_status ?? '-' }}</div>
                                <div class="col-md-12 mb-2"><strong>Permanent Address:</strong> {{ $student->permanent_address ?? '-' }}</div>
                                <div class="col-md-12 mb-2"><strong>Temporary Address:</strong> {{ $student->temporary_address ?? '-' }}</div>
                                <div class="col-md-6 mb-2"><strong>Passport No:</strong> {{ $student->passport_number ?? '-' }}</div>
                                <div class="col-md-6 mb-2"><strong>Expiry Date:</strong> {{ $student->passport_expiry ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Academic Info --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingAcademic">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAcademic">
                            🎓 Academic Info
                        </button>
                    </h2>
                    <div id="collapseAcademic" class="accordion-collapse collapse" data-bs-parent="#studentDetailsAccordion">
                        <div class="accordion-body">
                            <div class="row">
                                <div class="col-md-6 mb-2"><strong>Qualification:</strong> {{ $student->qualification ?? '-' }}</div>
                                <div class="col-md-6 mb-2"><strong>Passed Year:</strong> {{ $student->passed_year ?? '-' }}</div>
                                <div class="col-md-6 mb-2"><strong>Gap Years:</strong> {{ $student->gap ?? '-' }}</div>
                                <div class="col-md-6 mb-2"><strong>Last Grades:</strong> {{ $student->last_grades ?? '-' }}</div>
                                <div class="col-md-6 mb-2"><strong>Education Board:</strong> {{ $student->education_board ?? '-' }}</div>
                                <div class="col-md-6 mb-2"><strong>Preferred Country:</strong> {{ $student->preferred_country ?? '-' }}</div>
                                <div class="col-md-6 mb-2"><strong>Preferred Course:</strong> {{ $student->preferred_course ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Application Info --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingApplication">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseApplication">
                            📑 Application Info
                        </button>
                    </h2>
                    <div id="collapseApplication" class="accordion-collapse collapse" data-bs-parent="#studentDetailsAccordion">
                        <div class="accordion-body">
                            <p><strong>Agent:</strong> {{ $student->agent?->business_name ?? 'N/A' }}</p>
                            <p><strong>University:</strong> {{ $student->university?->name ?? 'N/A' }}</p>
                            <p><strong>Course:</strong> {{ $student->course?->title ?? 'N/A' }}</p>
                            <p><strong>Status:</strong> {{ ucfirst($student->student_status ?? '-') }}</p>
                            <p><strong>Notes:</strong> {{ $student->notes ?? '-' }}</p>
                            <p><strong>Follow Up:</strong> {{ $student->follow_up_date ?? '-' }}</p>
                            <p><strong>Created At:</strong> {{ $student->created_at->format('Y-m-d') }}</p>
                            <p><strong>Updated At:</strong> {{ $student->updated_at->format('Y-m-d') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Documents --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingDocuments">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDocuments">
                            📂 Documents
                        </button>
                    </h2>
                    <div id="collapseDocuments" class="accordion-collapse collapse" data-bs-parent="#studentDetailsAccordion">
                        <div class="accordion-body">
                            <div class="mb-3">
                                <a href="{{ route('agent.documents.index', $student->id) }}" class="btn btn-primary btn-sm mb-2">+ Upload Document</a>
                            </div>

                            @if($student->documents->isEmpty())
                            <p>No documents uploaded yet.</p>
                            @else
                            <div class="row">
                                @foreach($student->documents as $doc)
                                @php
                                $ext = pathinfo($doc->file_name, PATHINFO_EXTENSION);
                                $isImage = in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp']);
                                @endphp
                                <div class="col-md-4 mb-3">
                                    <div class="card shadow-sm h-100">
                                        @if($isImage)
                                        <img src="{{ asset('storage/' . $doc->file_path) }}" class="card-img-top" style="height:150px; object-fit:cover;">
                                        @else
                                        <div class="text-center py-5">
                                            <i class="bi bi-file-earmark-text" style="font-size:48px;"></i>
                                            <p>{{ strtoupper($ext) }}</p>
                                        </div>
                                        @endif
                                        <div class="card-body">
                                            <h6 class="card-title">{{ $doc->document_type ?? '—' }}</h6>
                                            <p class="text-muted mb-1"><small>{{ $doc->notes ?? '' }}</small></p>
                                            <div class="d-flex justify-content-between">
                                                <a href="{{ route('agent.documents.download', ['student'=>$student->id, 'document'=>$doc->id]) }}" class="btn btn-sm btn-success">⬇ Download</a>
                                                <form method="POST" action="{{ route('agent.documents.destroy', [$student->id, $doc->id]) }}" onsubmit="return confirm('Delete this document?');">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger">🗑 Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div> {{-- End Accordion --}}
        </div>

    </div>
</div>
@endsection
