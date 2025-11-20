@extends('layouts.agent')

@section('agent-content')
<div class="container py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">📄 Application Details</h2>
        <a href="{{ route('agent.applications.index') }}" class="btn btn-outline-secondary btn-sm">
            ⬅ Back to Applications
        </a>
    </div>

    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-body">

            {{-- Application Summary --}}
            <div class="d-flex justify-content-between mb-3">
                <h6 class="text-primary"><strong>Application No:</strong> {{ $application->application_number ?? 'N/A' }}</h6>
                <h6 class="text-primary"><strong>Submitted On:</strong> {{ $application->created_at->format('Y-m-d') }}</h6>
            </div>

            {{-- Status Badge --}}
            <div class="status-card mb-3">
                <span class="badge {{ $application->statusClass }}">{{ $application->application_status }}</span>
            </div>

            {{-- Student & University Info --}}
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="d-flex p-3 gap-2 bg-light justify-content-between rounded-3">
                        <div>
                            @if ($application->student->students_photo && Storage::disk('public')->exists($application->student->students_photo))
                            <img src="{{ Storage::url($application->student->students_photo) }}" alt="Profile" class="rounded border" style="width:150px; height:150px; object-fit:cover;">
                            @else
                            <div class="rounded bg-secondary d-flex align-items-center justify-content-center border" style="width:150px; height:150px;">
                                <i class="fa fa-user text-white" style="font-size:24px;"></i>
                            </div>
                            @endif
                        </div>
                        <div>
                            <h5 class="fw-bold">Student Info</h5>
                            <p><strong>Name:</strong> {{ $application->student->first_name ?? 'N/A' }} {{ $application->student->last_name ?? '' }}</p>
                            <p><strong>Email:</strong> {{ $application->student->email ?? 'N/A' }}</p>
                            <p><strong>Contact:</strong> {{ $application->student->phone_number ?? 'N/A' }}</p>
                            <p><strong>Permanent Address:</strong> {{ $application->student->permanent_address ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <h5 class="fw-bold">🏛 University Info</h5>
                        <p><strong>University:</strong> {{ $application->university->name ?? 'N/A' }} </p>
                        <p><strong>Course:</strong> {{ $application->course->title ?? $application->course->name ?? 'N/A' }}</p>
                        <p><strong>Country:</strong> {{ $application->university->country ?? 'N/A' }}</p>
                        <p><strong>City:</strong> {{ $application->university->city ?? 'N/A' }} </p>
                        <p><strong>Agent Name:</strong> {{ $application->student->agent?->business_name ?? $application->student->agent?->username ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            {{-- Application Progress --}}
            @php
            $currentIndex = array_search($application->application_status, $status);
            $currentIndex = $currentIndex !== false ? $currentIndex : 0;
            $totalStatus = count($status);
            $progressPercent = $totalStatus > 1 ? round(($currentIndex / ($totalStatus - 1)) * 100, 2) : 100;
            @endphp

            <div class="app-progress mt-4" style="--status: {{ $totalStatus }}; --progress: {{ $progressPercent }}%;">
                <div class="progress-bar-wrap">
                    <div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progressPercent }}">
                        <div class="progress-fill" aria-hidden="true"></div>

                        {{-- Current Step Box --}}
                        <div class="progress-box" style="background-color: {{ $application->statusClass ? '' : '#ffc107' }}">
                            <strong class="progress-box-title">{{ $status[$currentIndex] }}</strong>
                            <div class="progress-box-sub">Step {{ $currentIndex + 1 }} of {{ $totalStatus }}</div>
                        </div>
                    </div>

                    {{-- Step markers --}}
                    <ul class="status-list">
                        @foreach($status as $i => $step)
                        @php
                        $state = $i < $currentIndex ? 'completed' : ($i===$currentIndex ? 'current' : 'upcoming' ); $colorClass=$statusColors[$step] ?? 'bg-light text-dark' ; @endphp <li class="step-item {{ $state }}" data-step="{{ $i + 1 }}">
                            <span class="step-dot {{ $colorClass }}">
                                @if($i < $currentIndex) &#10003; @else {{ $i + 1 }} @endif </span>
                                    </li>
                                    @endforeach
                    </ul>
                </div>
            </div>

            {{-- SOP --}}
            <div class="mt-4 p-3 bg-white border rounded-3">
                <h5 class="fw-bold">📑 Statement of Purpose (SOP)</h5>
                @if($application->sop_file)
                <a href="{{ Storage::url($application->sop_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    👁️ SOP
                </a>
                @else
                <p class="text-muted mb-0">No SOP uploaded.</p>
                @endif
                <a href="{{ route('agent.documents.index', $application->student->id) }}" class="btn btn-sm btn-outline-secondary ms-2">
                    📁 View All Documents
                </a>
            </div>

            {{-- Messages --}}
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

                <form method="POST" action="{{ route('agent.applications.addMessage', $application) }}">
                    @csrf
                    <div class="d-flex">
                        <textarea name="message" class="form-control me-2" rows="2" placeholder="Add a message..." required></textarea>
                        <button type="submit" class="btn btn-primary btn-sm">Send</button>
                    </div>
                </form>
            </div>

            {{-- Actions --}}
            <div class="mt-4 d-flex justify-content-between">
                <a href="{{ route('agent.applications.edit', $application->id) }}" class="btn btn-primary">
                    ✏️ Edit Application
                </a>

                @if(!$application->withdrawn_at)
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#withdrawModal">
                    Withdraw Application
                </button>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Withdraw Modal --}}
<div class="modal fade" id="withdrawModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('agent.applications.withdraw', $application) }}">
            @csrf
            @method('PATCH')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Withdraw</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Enter your password to confirm withdrawal:</p>
                    <input type="password" name="password" class="form-control mb-2" required>
                    <textarea name="reason" class="form-control" placeholder="Reason (optional)"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Withdraw</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
