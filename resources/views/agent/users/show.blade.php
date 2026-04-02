@extends('layouts.agent')

@section('content')
<style>
    .file-box {
        width: 100%;
        /* width handled by grid */
        aspect-ratio: 4/4;
        /* keeps height proportional to width */
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        position: relative;
        background-color: #f8f9fa;
    }

    .file-box img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        /* scale image proportionally */
    }

    .file-box .file-preview,
    .file-box .no-logo {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    .file-box .badge {
        position: absolute;
        bottom: 5px;
    }

</style>

<div class="container-fluid py-4">

    {{-- Profile Header --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row">
                {{-- User Info --}}
                <div class="col-lg-4 col-md-12 mb-3">
                    <h3 class="bg-secondary text-white p-1 mb-1 rounded">{{ $user->business_name ?? $user->username }}</h3>
                    <p class="mb-0"><strong>Owner:</strong> {{ $user->owner_name ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Contact:</strong> {{ $user->contact ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Email:</strong> {{ $user->email }}</p>
                    <p class="mb-0"><strong>Address:</strong> {{ $user->address }}</p>
                </div>

                {{-- Files Section --}}
                <div class="col-lg-8 col-md-12">
                    <div class="row g-3">
                        @php
                        $files = [
                        ['label' => 'Registration', 'file' => $user->registration, 'status' => null],
                        ['label' => 'PAN', 'file' => $user->pan, 'status' => null],
                        ['label' => 'Agreement', 'file' => $user->agreement_file, 'status' => $user->agreement_status ?? 'not_uploaded'],
                        ['label' => 'Logo', 'file' => $user->business_logo, 'status' => $user->active ? 'Active' : 'Inactive'],
                        ];
                        @endphp

                        @foreach($files as $f)
                        <div class="col-lg-3 col-md-4 col-sm-6 col-6">
                            <div class="file-box">
                                @if($f['file'])
                                @php
                                $fileUrl = Storage::url($f['file']);
                                $ext = strtolower(pathinfo($f['file'], PATHINFO_EXTENSION));
                                $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp']);
                                @endphp

                                @if($isImage)
                                <a href="{{ $fileUrl }}" target="_blank">
                                    <img src="{{ $fileUrl }}" alt="{{ $f['label'] }}">
                                </a>
                                @else
                                <a href="{{ $fileUrl }}" target="_blank">
                                    <div class="file-preview">
                                        <i class="fas fa-file-alt fa-2x mb-1"></i>
                                        <span>{{ basename($f['file']) }}</span>
                                    </div>
                                </a>
                                @endif
                                @else
                                <div class="no-logo">{{ $f['label'] }} not uploaded</div>
                                @endif

                                {{-- Badge --}}
                                @if($f['status'])
                                <span class="badge 
                                            @if($f['status'] == 'verified' || $f['status'] === 'Active') bg-success
                                            @elseif($f['status'] == 'not_uploaded') bg-warning
                                            @else bg-primary
                                            @endif">
                                    {{ ucfirst($f['status']) }}
                                </span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Stats Row --}}
            <div class="row mt-3 align-items-center">
                <div class="col-md-8 d-flex flex-wrap gap-2">
                    <a href="{{ route('agent.students.index', $user->slug) }}" class="stat-link">
                        <div class="stat-card">
                            <div class="stat-left">
                                <h6>Total Students</h6>
                                <h4>{{ $user->students->count() }}</h4>
                            </div>
                            <div class="icon text-primary"><i class="fa fa-users"></i></div>
                        </div>
                    </a>
                    <a href="{{ route('agent.applications.index', $user->slug) }}" class="stat-link">
                        <div class="stat-card">
                            <div class="stat-left">
                                <h6>Applications Submitted</h6>
                                <h4>{{ $user->applications->count() }}</h4>
                            </div>
                            <div class="icon text-secondary"><i class="fa fa-vcard"></i></div>
                        </div>
                    </a>
                    <a href="#" class="stat-link">
                        <div class="stat-card">
                            <div class="stat-left">
                                <h6>Total Documents</h6>
                                <h4>{{ $user->documents->count() }}</h4>
                            </div>
                            <div class="icon text-primary"><i class="fa fa-university"></i></div>
                        </div>
                    </a>
                </div>

                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                    <a href="{{ route('agent.users.edit', $user->slug) }}" class="btn btn-primary btn-sm">✏️ Edit Profile</a>
                </div>
            </div>
        </div>
    </div>


    {{-- Students List --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            Students
        </div>
        <div class="card-body">
            @if($user->students && $user->students->count())
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Applications</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($user->students as $student)
                    <tr>
                        <td>
                            <a href="{{ route('agent.students.show', $student->id) }}">
                                {{ trim($student->first_name . ' ' . $student->last_name) }}
                            </a>
                        </td>
                        <td>{{ $student->email }}</td>
                        <td>{{ $student->applications->count() }}</td>
                        <td>{{ $student->created_at->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-muted">No students found for this agent.</p>
            @endif
        </div>
    </div>

    {{-- Documents List --}}
    <div class="accordion card" id="studentDocumentsAccordion">
        <div class="card-header bg-secondary text-white">
            List of Students document Activities
        </div>
        @foreach($user->students as $student)
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading-{{ $student->id }}">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $student->id }}" aria-expanded="false">
                    {{ $student->first_name }} {{ $student->last_name }}
                    <span class="badge bg-primary ms-3">{{ $student->documents->count() }} docs</span>
                </button>
            </h2>
            <div id="collapse-{{ $student->id }}" class="accordion-collapse collapse" data-bs-parent="#studentDocumentsAccordion">
                <div class="accordion-body">
                    @if($student->documents->count())
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>File</th>
                                <th>Uploaded</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($student->documents as $doc)
                            <tr>
                                <td>{{ ucfirst($doc->document_type) }}</td>
                                <td><a href="#" data-preview="{{ asset($doc->file_path) }}" target="_blank">{{ $doc->file_name }}</a></td>
                                <td>{{ $doc->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p class="text-muted">No documents uploaded.</p>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Applications List --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-success text-white">
            Applications
        </div>
        <div class="card-body">
            @php $apps = $user->applications ?? collect(); @endphp
            @if($apps->count())
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th>University</th>
                        <th>Status</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($apps as $app)
                    <tr>
                        <td><a href="{{ route('agent.applications.show', $app->id) }}">
                                {{ $app->student->first_name . ' ' . $app->student->last_name ?? 'N/A' }}
                            </a></td>
                        <td>{{ $app->course->title ?? 'N/A' }}</td>
                        <td>{{ $app->university->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge {{ $app->status_class ?? 'bg-light text-muted' }}">
                                {{ $app->application_status ?? 'N/A' }}
                            </span>
                        </td>
                        <td>{{ $app->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-muted">No applications submitted.</p>
            @endif
        </div>
    </div>

    {{-- Activities Section --}}
    <div class="activities-row">
        <div class="activity-card card">
            <h6>Students Activities</h6>
            <ul>
                @forelse($studentActivities as $act)
                <li>
                    <div>
                        @if($act->notifiable_id)
                        <a href="{{ route('agent.students.show', $act->notifiable_id) }}">{{ $act->description }}</a>
                        @else
                        {{ $act->description }}
                        @endif
                        <div class="time-text">{{ $act->created_at->diffForHumans() }}</div>
                    </div>
                </li>
                @empty
                <li>No students activities</li>
                @endforelse
            </ul>
        </div>
        <div class="activity-card card">
            <h6>Documents</h6>
            <ul>
                @forelse($documentActivities as $act)
                <li>
                    <div>
                        @if($act->notifiable_id)
                        <a href="{{ route('agent.documents.index', $act->notifiable_id) }}">
                            {{ $act->description }}
                        </a>
                        @else
                        {{ $act->description }}
                        @endif
                        <div class="time-text">{{ $act->created_at->diffForHumans() }}</div>
                    </div>
                </li>
                @empty
                <li>No document activities</li>
                @endforelse
            </ul>
        </div>
        <div class="activity-card card">
            <h6>Applications</h6>
            <ul>
                @forelse($applicationActivities as $act)
                <li>
                    <div>
                        @if($act->notifiable_id)
                        <a href="{{ route('agent.applications.show', $act->notifiable_id) }}">{{ $act->description }}</a>
                        @else
                        {{ $act->description }}
                        @endif
                        <div class="time-text">{{ $act->created_at->diffForHumans() }}</div>
                    </div>
                </li>
                @empty
                <li>No applications yet</li>
                @endforelse
            </ul>
        </div>
    </div>

</div>

@endsection
