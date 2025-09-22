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
                <h2 class="card-jobtitle">{{ $student->course?->title ?? 'No Course Selected' }}</h2>

                {{-- Card Main --}}
                <div class="card-main">
                    <div class="card-section is-active" id="about">
                        <div class="card-content text-start">
                            <div class="card-subtitle">PROFILE INFO</div>
                            <p class="card-desc">
                                <strong>Status:</strong>
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
                            <p><strong>University:</strong> {{ $student->university?->name ?? '-' }}</p>
                            <p><strong>Agent:</strong> {{ $student->agent?->business_name ?? $student->agent?->username ?? 'N/A' }}</p>
                            <p><strong>Preferred Country:</strong> {{ $student->preferred_country ?? '-' }}</p>
                            <p><strong>Preferred Course:</strong> {{ $student->preferred_course ?? '-' }}</p>
                            <p><strong>Follow Up Date:</strong> {{ $student->follow_up_date ?? '-' }}</p>
                        </div>

                        {{-- Actions --}}
                        <div class="card-social">
                            <a href="{{ route('agent.students.edit', $student->id) }}" class="btn btn-dark btn-sm w-100">✏️ Edit</a>
                            <a href="{{ route('agent.documents.index', $student->id) }}" class="btn btn-primary btn-sm w-100">📂 Upload Doc</a>
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
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#application">📑 Application</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#documents">📂 Documents</button></li>
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
                    <div class="card shadow-sm mb-4 border-0 rounded-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">

                                <h6 class="mb-3 text-primary">
                                    Application Number: {{ $application->application_number ?? 'N/A' }}
                                </h6>
                                <h6 class="mb-3 text-primary">
                                    Application Submitted On: {{ $application->created_at->format('Y-m-d') }}
                                </h6>
                            </div>
                            <div class="row g-4">
                                {{-- University Info --}}
                                <div class="col-md-8">
                                    <div class="p-3 bg-light rounded-3 h-100">
                                        <p><strong>University:</strong> {{ $application->university->name ?? 'N/A' }}</p>
                                        <p><strong>Course:</strong> {{ $application->course->title ?? 'N/A' }}</p>
                                        <p><strong>City:</strong> {{ $application->university->city ?? 'N/A' }}</p>
                                        <p><strong>Duration:</strong> {{ $application->course->duration ?? 'N/A' }}</p>
                                        <p><strong>Total Fee:</strong> {{ $application->course->fee ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class=" col-md-4">
                                    <div class="p-3 bg-light rounded-3 h-100">
                                        <h6 class="fw-bold">📌 Application Status</h6>
                                        <div class="m-2 p-3 rounded-2 badge {{ $application->status_class }}">
                                            {{ $application->application_status }}
                                        </div>
                                        {{-- SOP --}}
                                        @if($application->sop)
                                        {{-- For SOP --}}
                                        <a href="{{ route('agent.documents.index', $application->student->id) }}" class="btn btn-sm rounded btn-secondary m-2">
                                            📂 View All Documents
                                        </a>
                                        <a href="#" data-preview="{{ Storage::url($application->sop->file_path) }}" class="btn m-2 rounded-2 p-2 btn-sm">
                                            👁️ View SOP
                                        </a>

                                        @else
                                        <span class="text-muted">Not uploaded</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Application Info --}}

                            <div class="mt-4 p-3 bg-white border rounded-3">
                                <h5 class="fw-bold mb-3">💬 View Comments</h5>
                                <div class="comments-thread rounded p-3 mb-3" style="max-height:400px; overflow:auto;">
                                    @forelse($application->comments as $c)
                                    <div class="mb-2">
                                        <strong>{{ ucfirst($c->type) }} ({{ $c->user->name ?? 'Unknown' }}):</strong>
                                        <span>{{ $c->comment }}</span>
                                        <small class="text-muted d-block">{{ $c->created_at->format('d M Y, H:i') }}</small>
                                    </div>
                                    @empty
                                    <p class="text-muted">No comments yet.</p>
                                    @endforelse
                                </div>
                            </div>
                            {{-- Actions --}}
                            <div class="mt-3">
                                <a href="{{ route('agent.applications.edit', $application->id) }}" class="btn btn-primary btn-sm">
                                    ✏️ Edit Application
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <p class="text-muted">No applications found for this student.</p>
                    @endif
                </div>


                {{-- Documents --}}
                <div class="tab-pane fade" id="documents">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>📂 Documents</h5>
                        <a href="{{ route('agent.documents.index', $student->id) }}" class="btn btn-primary btn-sm">+ Upload Document</a>
                    </div>

                    @if($student->documents->isEmpty())
                    <p>No documents uploaded yet.</p>
                    @else
                    <div class="row">
                        @foreach($student->documents as $doc)
                        @php
                        $extension = pathinfo($doc->file_name, PATHINFO_EXTENSION);
                        $isImage = in_array(strtolower($extension), ['jpg','jpeg','png','gif','webp']);
                        $filePath = asset('storage/' . $doc->file_path);
                        @endphp
                        <div class="col-md-4 mb-3">
                            <div class="card document-card shadow-sm">
                                @if($isImage)
                                <img src="{{ $filePath }}" class="card-img-top doc-preview" data-src="{{ $filePath }}" alt="Document Preview">
                                @else
                                <div class="doc-placeholder text-center p-4">
                                    <i class="fa fa-file fa-3x text-secondary"></i>
                                    <p class="mt-2">{{ strtoupper($extension) }}</p>
                                </div>
                                @endif

                                <div class="card-body">
                                    <h6 class="card-title">{{ $doc->document_type ?? '—' }}</h6>
                                    <p class="card-text text-truncate">{{ $doc->notes ?? '' }}</p>
                                    <p class="text-muted mb-1"><small>Uploaded by: {{ $doc->uploader->username ?? $doc->uploader->business_name ?? 'N/A' }}</small></p>
                                    <p class="text-muted mb-2"><small>{{ $doc->created_at->format('Y-m-d') }}</small></p>
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('agent.documents.download', ['student'=>$student->id, 'document'=>$doc->id]) }}" class="btn btn-sm btn-success"><i class="fa fa-download"></i></a>
                                        <form method="POST" action="{{ route('agent.documents.destroy', [$student->id, $doc->id]) }}" class="d-inline" onsubmit="return confirm('Delete this document?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button>
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
    </div>
</div>

<!-- Modal for Image Preview -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-body text-center">
                <img src="" class="img-fluid rounded" id="previewImage" alt="Preview">
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="fa fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JS for Modal -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const previews = document.querySelectorAll('.doc-preview');
        const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
        const previewImage = document.getElementById('previewImage');

        previews.forEach(img => {
            img.addEventListener('click', function() {
                previewImage.src = this.dataset.src;
                modal.show();
            });
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        // Check if URL has a hash
        const hash = window.location.hash;
        if (hash) {
            // Select the corresponding tab button
            const tabTriggerEl = document.querySelector(`.nav-link[data-bs-target="${hash}"]`);
            if (tabTriggerEl) {
                // Activate the tab using Bootstrap Tab class
                const tab = new bootstrap.Tab(tabTriggerEl);
                tab.show();

                // Scroll to the tab content smoothly
                const tabContentEl = document.querySelector(hash);
                if (tabContentEl) {
                    setTimeout(() => {
                        tabContentEl.scrollIntoView({
                            behavior: "smooth"
                        });
                    }, 100); // slight delay to ensure tab is shown
                }
            }
        }
    });

</script>

</script>

@endsection
