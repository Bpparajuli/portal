@php
    $alertTypes = [
        'success' => ['icon' => 'fa-check-circle', 'color' => 'success'],
        'error'   => ['icon' => 'fa-times-circle',   'color' => 'danger'],
        'warning' => ['icon' => 'fa-exclamation-triangle', 'color' => 'warning'],
        'info'    => ['icon' => 'fa-info-circle',    'color' => 'info'],
    ];
@endphp

@if (session()->hasAny(array_keys($alertTypes)) || $errors->any() || session('duplicate_student') || session('student_created'))
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100; width: 420px; max-width: 95%;">

        @foreach ($alertTypes as $key => $def)
            @if (session($key))
                <div class="alert alert-{{ $def['color'] }} alert-custom alert-dismissible fade show mb-3" role="alert">
                    <div class="d-flex align-items-start gap-3">
                        <div class="alert-icon"><i class="fas {{ $def['icon'] }}"></i></div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ ucfirst($key) }}</div>
                            <div class="small">{{ session($key) }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif
        @endforeach

        @if ($errors->any())
            <div class="alert alert-danger alert-custom alert-dismissible fade show mb-3" role="alert">
                <div class="d-flex align-items-start gap-3">
                    <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold mb-2">Please fix the following:</div>
                        <ul class="small ps-3 mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if (session('duplicate_student'))
            @php $dup = session('duplicate_student'); @endphp
            <div class="alert alert-warning alert-custom alert-dismissible fade show mb-3" role="alert">
                <div class="d-flex align-items-start gap-3">
                    <div class="alert-icon"><i class="fas fa-user-clock"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold mb-2">Duplicate Student Found</div>
                        <div class="small">
                            <div><strong>Name:</strong> {{ $dup->full_name }}</div>
                            <div><strong>Phone:</strong> {{ $dup->phone_number ?? 'N/A' }}</div>
                            <div><strong>Email:</strong> {{ $dup->email ?? 'N/A' }}</div>
                        </div>
                        <div class="mt-2 d-flex gap-2">
                            <a href="{{ route('crm.student.show', $dup) }}" class="btn btn-sm btn-dark rounded-pill">View Profile</a>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" data-bs-dismiss="alert">Dismiss</button>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if (session('student_created'))
            @php $ns = session('student_created'); @endphp
            <div class="alert alert-success alert-custom alert-dismissible fade show mb-3" role="alert">
                <div class="d-flex align-items-start gap-3">
                    <div class="alert-icon"><i class="fas fa-user-check"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold mb-2">Student Added Successfully</div>
                        <div class="small">
                            <div><strong>Name:</strong> {{ $ns->full_name }}</div>
                            <div><strong>Phone:</strong> {{ $ns->phone_number ?? 'N/A' }}</div>
                            <div><strong>Email:</strong> {{ $ns->email ?? 'N/A' }}</div>
                            <div><strong>Source:</strong> {{ $ns->source ?? 'manual' }}</div>
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('crm.student.show', $ns) }}" class="btn btn-sm btn-success rounded-pill">Open Profile</a>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

    </div>
@endif
