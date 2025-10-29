@extends('layouts.admin')

@section('admin-content')
<div class="container p-2">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">📄 Application Details</h2>
        <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-secondary btn-sm">
            ⬅ Back to Applications
        </a>
    </div>

    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-body">

            {{-- Application Info --}}
            <div class="d-flex justify-content-between">
                <h6 class="mb-3 text-primary">
                    Application Number: {{ $application->application_number ?? 'N/A' }}
                </h6>
                <h6 class="mb-3 text-primary">
                    Submitted On: {{ optional($application->created_at)->format('Y-m-d') ?? 'N/A' }}
                </h6>
            </div>

            {{-- Student & University Info --}}
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <h5 class="fw-bold">👤 Student Info</h5>
                        <p><strong>Name:</strong> {{ $application->student->first_name ?? 'N/A' }} {{ $application->student->last_name ?? '' }}</p>
                        <p><strong>Email:</strong> {{ $application->student->email ?? 'N/A' }}</p>
                        <p><strong>Contact:</strong> {{ $application->student->phone_number ?? 'N/A' }}</p>
                        <p><strong>Permanent Address:</strong> {{ $application->student->permanent_address ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <h5 class="fw-bold">🏛 University Info</h5>
                        <p><strong>University:</strong> {{ $application->university->name ?? 'N/A' }}</p>
                        <p><strong>City:</strong> {{ $application->university->city ?? 'N/A' }}</p>
                        <p><strong>Course:</strong> {{ $application->course->title ?? $application->course->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            {{-- Application Progress --}}
            @php
            // ordered steps (same as your model)
            $steps = [
            'Application started',
            'Application viewed by Admin',
            'Applied to University',
            'Need to give the test',
            'Accepted by the University',
            'Rejected by the University',
            'Applied to another university',
            'Application forwarded to embassy',
            'Is on waiting list on Embassy',
            'Visa Approved',
            'Visa Rejected',
            'Lost',
            ];

            // current index from DB (0-based). fallback to 0 if not found.
            $currentIndex = array_search($application->application_status, $steps);
            if ($currentIndex === false) $currentIndex = 0;

            $totalSteps = count($steps);
            // compute progress percent (0..100). Avoid division by zero.
            $progressPercent = $totalSteps > 1
            ? round(($currentIndex / ($totalSteps - 1)) * 100, 2)
            : 100;
            @endphp

            <div class="app-progress mt-4" style="--steps: {{ $totalSteps }}; --progress: {{ $progressPercent }}%;">
                <div class="progress-bar-wrap">
                    <!-- Horizontal bar + fill -->
                    <div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progressPercent }}">
                        <div class="progress-fill" aria-hidden="true"></div>

                        <!-- Center box showing current step title -->
                        <div class="progress-box" aria-hidden="false">
                            <strong class="progress-box-title">{{ $steps[$currentIndex] }}</strong>
                            <div class="progress-box-sub">Step {{ $currentIndex + 1 }} of {{ $totalSteps }}</div>
                        </div>
                    </div>

                    <!-- Step markers (labels under the bar) -->
                    <ul class="steps-list" aria-hidden="false">
                        @foreach($steps as $i => $step)
                        @php
                        $state = $i < $currentIndex ? 'completed' : ($i===$currentIndex ? 'current' : 'upcoming' ); @endphp <li class="step-item {{ $state }}" data-step="{{ $i + 1 }}">
                            <span class="step-dot" aria-hidden="true">
                                @if($i < $currentIndex) &#10003; {{-- check mark for completed --}} @else {{ $i + 1 }} @endif </span>
                                    <div class="step-label">{{ $step }}</div>
                                    </li>
                                    @endforeach
                    </ul>
                </div>
            </div>

            {{-- Status Update Form --}}
            <div class="mt-4 p-3 bg-white border rounded-3">
                <h5 class="fw-bold mb-3">🛠 Update Application Status</h5>
                <form action="{{ route('admin.applications.update', $application->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row align-items-end">
                        <div class="col-md-8">
                            <select name="application_status" id="application_status" class="form-select">
                                @foreach(\App\Models\Application::STATUSES as $status)
                                <option value="{{ $status }}" {{ $application->application_status == $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mt-2 mt-md-0">
                            <button type="submit" class="btn btn-primary w-100">Update Status</button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- SOP Document --}}
            <div class="mt-4 p-3 bg-white border rounded-3">
                <h5 class="fw-bold">📑 Statement of Purpose (SOP)</h5>
                @if($application->sop && $application->sop->file_path)
                <a href="{{ Storage::url($application->sop->file_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">📂 View SOP</a>
                @else
                <span class="text-muted">Not uploaded</span>
                @endif
                <a href="{{ route('admin.documents.index', $application->student->id) }}" class="btn btn-sm btn-outline-secondary mt-1">📁 View All Documents</a>
            </div>

            {{-- Messages Thread --}}
            <div class="mt-4 p-3 bg-white border rounded-3">
                <h5 class="fw-bold mb-3">💬 Messages</h5>

                <div class="remarks-thread border rounded-1 p-3 m-2" style="max-height:400px; overflow:auto;">
                    @forelse($application->messages as $m)
                    <div class="d-flex mb-2 
                        @if($m->type === 'agent') justify-content-start @else justify-content-end @endif">

                        <div class="p-2 rounded" style="max-width:70%; 
                            background-color: {{ $m->type === 'agent' ? '#f1f1f1' : '#1a0262' }};
                            color: {{ $m->type === 'agent' ? '#000' : '#fff' }};">
                            <strong>
                                <p class="mb-1">{{ $m->message }}</p>
                            </strong>
                            <small class="text-dark"><b>{{ $m->user->name ?? 'Unknown' }}</b>:{{ $m->created_at->format('d M Y, H:i') }}</small>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted">No messages yet.</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('admin.applications.addmessage', $application->id) }}">
                    @csrf
                    <div class="d-flex">
                        <div class="m-2 w-100">
                            <textarea name="message" class="form-control" rows="2" placeholder="Add a message..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-success m-2 btn-sm">Send</button>
                    </div>
                </form>
            </div>

            {{-- Actions --}}
            <div class="m-2 d-flex gap-2 justify-content-between">
                <a href="{{ route('admin.applications.edit', $application->id) }}" class="btn btn-primary">✏️ Edit Application</a>

                @if(!$application->withdrawn_at)
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#withdrawModal">Withdraw Application</button>
                @endif
            </div>
        </div>

        {{-- Withdraw Modal --}}
        <div class="modal fade" id="withdrawModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('admin.applications.withdraw', $application) }}">
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
    </div>
</div>
@endsection
