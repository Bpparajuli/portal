@extends('layouts.admin')

@section('admin-content')
<div class="container py-4">

    {{-- Profile Header --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body d-flex align-items-center">
            <div class="me-4">
                @if($user->business_logo)
                <img src="{{ Storage::url($user->business_logo) }}" alt="Logo" width="120" height="120" class="rounded-circle border shadow-sm">
                @else
                <div class="no-logo">No Logo</div>
                @endif
            </div>
            <div>
                <h3 class="mb-1">{{ $user->business_name }}</h3>
                <p class="mb-0"><strong>Owner:</strong> {{ $user->owner_name ?? 'N/A' }}</p>
                <p class="mb-0"><strong>Contact:</strong> {{ $user->contact ?? 'N/A' }}</p>
                <p class="mb-0"><strong>Email:</strong> {{ $user->email }}</p>
                <p class="mb-0"><strong>Role:</strong> {{ $user->is_admin ? 'Admin' : 'Agent' }}</p>
                <span class="badge {{ $user->active ? 'bg-success' : 'bg-secondary' }}">
                    {{ $user->active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Stats Section --}}
    <div class="row mb-4 text-center">
        <div class="col-md-4">
            <div class="stat-card">
                <h4>{{ $user->students_count ?? 0 }}</h4>
                <p>Students</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <h4>{{ $user->applications_count ?? 0 }}</h4>
                <p>Total Applications</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <h4>{{ $user->pending_applications ?? 0 }}</h4>
                <p>Pending Applications</p>
            </div>
        </div>
    </div>

    {{-- Students List --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            Students
        </div>
        <div class="card-body">
            @if(isset($students) && $students->count())
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
                    @foreach($students as $student)
                    <tr>
                        <td>
                            <a href="{{ route('admin.students.show', $student->id) }}">
                                {{ trim($student->first_name . ' ' . $student->last_name) }}
                            </a>
                        </td>
                        <td>{{ $student->email }}</td>
                        <td>{{ $student->applications_count }}</td>
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

    {{-- Applications List --}}
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            Applications
        </div>
        <div class="card-body">
            @if(isset($applications) && $applications->count())
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
                    @foreach($applications as $app)
                    <tr>
                        <td><a href="{{ route('admin.applications.show', $app->id) }}">
                                {{ $app->student->first_name . ' ' . $app->student->last_name }}
                            </a></td>
                        <td> {{ $app->course->title ?? $app->course->name ?? 'N/A' }}</td>
                        <td>{{ $app->university->name ?? 'N/A'}}
                        </td>
                        <td>
                            <span class="badge {{ $app->status_class ?? 'bg-light text-muted' }}">
                                {{ $app->application_status }}
                            </span>
                        </td>
                        <td>{{ $app->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-muted">No applications submitted by this agent’s students.</p>
            @endif
        </div>
    </div>
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
