@extends('layouts.agent')

@section('content')

<div class="container py-4">

    {{-- Profile Header --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center">

            <div>
                <h3 class="mb-1">{{ $user->business_name ?? $user->username }}</h3>
                <p class="mb-0"><strong>Owner:</strong> {{ $user->owner_name ?? 'N/A' }}</p>
                <p class="mb-0"><strong>Contact:</strong> {{ $user->contact ?? 'N/A' }}</p>
                <p class="mb-0"><strong>Email:</strong> {{ $user->email }}</p>
                <p class="mb-0"><strong>Address:</strong> {{ $user->address }}</p>
            </div>
            <div>
                @if($user->agreement_file)
                @php
                $fileUrl = Storage::url($user->agreement_file);
                $extension = strtolower(pathinfo($user->agreement_file, PATHINFO_EXTENSION));
                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                @endphp

                @if($isImage)
                {{-- Display image --}}
                <img src="{{ $fileUrl }}" alt="Agreement file" width="200px" height="auto" class="rounded border shadow-sm mb-2">
                @else
                {{-- Display file icon or name --}}
                <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-secondary mt-1" data-preview="{{ $fileUrl }}">
                    <div class="file-preview p-2 mb-2 border rounded shadow-sm" style="width: 200px; text-align:center;">
                        <i class="fas fa-file-alt fa-2x mb-1"></i>
                        <br>
                        <span>{{ basename($user->agreement_file) }}</span>
                    </div>
                </a>
                @endif
                @else
                <div class="no-logo text-center" style="width:200px; height:100px; background:#eee; display:flex; align-items:center; justify-content:center;">
                    Agreement file Not_uploaded
                </div>

                @endif
                <br>
                {{-- Status Badge --}}
                <a href="{{ route('admin.users.edit', $user->business_name_slug) }}" class="text-decoration-none">
                    <span class="badge 
                                @if($user->agreement_status == 'verified') bg-success
                                @elseif($user->agreement_status == 'not_uploaded') bg-warning
                                @else bg-primary
                                @endif">
                        {{ ucfirst($user->agreement_status) }}
                    </span>
                </a>
            </div>
            <div>
                @if($user->business_logo)
                <img src="{{ Storage::url($user->business_logo) }}" alt="Logo" width="200px" height="auto" class="rounded border shadow-sm">
                @else
                <div class="no-logo text-center" style="width:120px;height:120px;line-height:120px;background:#eee;">No Logo</div>
                @endif
                <br>
                <p class="badge {{ $user->active ? 'bg-success' : 'bg-secondary' }}">
                    {{ $user->active ? 'Active' : 'Inactive' }}
                </p>
            </div>
        </div>
        {{-- Stats Section --}}
        <div class="d-flex justify-content-between align-items-center">
            <div class="stats-row ">
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
                <a href="{{ route('agent.users.edit', $user->business_name_slug) }}" class="btn btn-primary btn-sm">✏️ Edit Profile</a>
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
            List of Students document Activities </div>
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
