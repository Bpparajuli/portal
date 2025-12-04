@extends('layouts.agent')

@section('content')

<div class="container py-4">

    {{-- Profile Header --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center">

            {{-- User Info --}}
            <div>
                <h3 class="mb-1">{{ $user->business_name ?? $user->username }}</h3>
                <p class="mb-0"><strong>Owner:</strong> {{ $user->owner_name ?? 'N/A' }}</p>
                <p class="mb-0"><strong>Contact:</strong> {{ $user->contact ?? 'N/A' }}</p>
                <p class="mb-0"><strong>Email:</strong> {{ $user->email }}</p>
                <p class="mb-0"><strong>Address:</strong> {{ $user->address }}</p>
            </div>

            {{-- Files Section --}}
            <div class="d-flex align-items-start gap-3">
                {{-- Registration --}}
                <div class="registration position-relative" style="width:180px; height:200px; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                    @if($user->registration)
                    @php
                    $regUrl = Storage::url($user->registration);
                    $regExt = strtolower(pathinfo($user->registration, PATHINFO_EXTENSION));
                    $isRegImage = in_array($regExt, ['jpg','jpeg','png','gif','webp','bmp']);
                    @endphp
                    @if($isRegImage)
                    <img src="{{ $regUrl }}" alt="Registration" class="rounded border shadow-sm" style="max-height:150px;">
                    @else
                    <a href="{{ $regUrl }}" target="_blank">
                        <div class="file-preview p-2 border rounded shadow-sm text-center" style="width:100%;">
                            <i class="fas fa-file-alt fa-2x mb-1"></i><br>
                            <span>{{ basename($user->registration) }}</span>
                        </div>
                    </a>
                    @endif
                    @else
                    <div class="no-logo text-center border rounded shadow-sm" style="width:100%; height:150px; display:flex; align-items:center; justify-content:center;">
                        Registration not uploaded
                    </div>
                    @endif
                </div>

                {{-- PAN --}}
                <div class="pan position-relative" style="width:180px; height:200px; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                    @if($user->pan)
                    @php
                    $panUrl = Storage::url($user->pan);
                    $panExt = strtolower(pathinfo($user->pan, PATHINFO_EXTENSION));
                    $isPanImage = in_array($panExt, ['jpg','jpeg','png','gif','webp','bmp']);
                    @endphp
                    @if($isPanImage)
                    <img src="{{ $panUrl }}" alt="PAN" class="rounded border shadow-sm" style="max-height:150px;">
                    @else
                    <a href="{{ $panUrl }}" target="_blank">
                        <div class="file-preview p-2 border rounded shadow-sm text-center" style="width:100%;">
                            <i class="fas fa-file-alt fa-2x mb-1"></i><br>
                            <span>{{ basename($user->pan) }}</span>
                        </div>
                    </a>
                    @endif
                    @else
                    <div class="no-logo text-center border rounded shadow-sm" style="width:100%; height:150px; display:flex; align-items:center; justify-content:center;">
                        PAN not uploaded
                    </div>
                    @endif
                </div>

                {{-- Agreement --}}
                <div class="agreement position-relative" style="width:180px; height:200px; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                    @if($user->agreement_file)
                    @php
                    $agrUrl = Storage::url($user->agreement_file);
                    $agrExt = strtolower(pathinfo($user->agreement_file, PATHINFO_EXTENSION));
                    $isAgrImage = in_array($agrExt, ['jpg','jpeg','png','gif','webp','bmp']);
                    @endphp
                    @if($isAgrImage)
                    <img src="{{ $agrUrl }}" alt="Agreement" class="rounded border shadow-sm" style="max-height:150px;">
                    @else
                    <a href="{{ $agrUrl }}" target="_blank">
                        <div class="file-preview p-2 border rounded shadow-sm text-center" style="width:100%;">
                            <i class="fas fa-file-alt fa-2x mb-1"></i><br>
                            <span>{{ basename($user->agreement_file) }}</span>
                        </div>
                    </a>
                    @endif
                    @else
                    <div class="no-logo text-center border rounded shadow-sm" style="width:100%; height:150px; display:flex; align-items:center; justify-content:center;">
                        Agreement not uploaded
                    </div>
                    @endif

                    {{-- Agreement Status Badge --}}
                    <span class="badge 
                @if($user->agreement_status == 'verified') bg-success
                @elseif($user->agreement_status == 'not_uploaded') bg-warning
                @else bg-primary
                @endif" style="position:absolute; bottom:20px;">
                        {{ ucfirst($user->agreement_status) }}
                    </span>
                </div>

                {{-- Business Logo --}}
                <div class="logo position-relative" style="width:180px; height:200px; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                    @if($user->business_logo)
                    <img src="{{ Storage::url($user->business_logo) }}" alt="Logo" class="rounded border shadow-sm" style="max-height:150px;">
                    @else
                    <div class="no-logo text-center border rounded shadow-sm" style="width:100%; height:150px; display:flex; align-items:center; justify-content:center;">
                        No Logo
                    </div>
                    @endif
                    {{-- User Active Status --}}
                    <span class="badge {{ $user->active ? 'bg-success' : 'bg-secondary' }} mt-2" style="position:absolute; bottom:20px;">
                        {{ $user->active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>

        </div>
        {{-- Stats Section --}}
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="stats-row">
                <a href="{{ route('agent.students.index') }}" class="stat-link">
                    <div class="stat-card">
                        <div class="stat-left">
                            <h6>Total Students</h6>
                            <h4>{{ $user->students->count() }}</h4>
                        </div>
                        <div class="icon text-primary"><i class="fa fa-users"></i></div>
                    </div>
                </a>
                <a href="{{ route('agent.applications.index') }}" class="stat-link">
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
            {{-- Edit Button --}}
            <div class="mt-2 align-right">
                <a href="{{ route('agent.users.edit', $user->slug) }}" class="btn btn-primary btn-sm">✏️ Edit Profile</a>
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
