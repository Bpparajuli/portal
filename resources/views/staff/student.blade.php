@extends('layouts.staff')

@section('content')
<style>
    :root {
        --crm-blue: #4e73df;
        --crm-border: #e3e6f0;
        --crm-text: #5a5c69;
    }

    body {
        background: #f8f9fc;
        color: var(--crm-text);
        font-size: 0.85rem;
    }

    /* Chevron Progress Bar */
    .chevron-bar {
        display: flex;
        height: 38px;
        background: #eaecf4;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .chevron-item {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding-left: 30px;
        position: relative;
        font-weight: 600;
        color: #858796;
        clip-path: polygon(90% 0%, 100% 50%, 90% 100%, 0% 100%, 10% 50%, 0% 0%);
        margin-left: -15px;
        cursor: pointer;
    }

    .chevron-item:first-child {
        clip-path: polygon(90% 0, 100% 50%, 90% 100%, 0% 100%, 0% 50%, 0% 0%);
        padding-left: 15px;
        margin-left: 0;
    }

    .chevron-item.active {
        background: var(--crm-blue);
        color: #fff;
        z-index: 2;
    }

    .chevron-item.completed {
        background: #1cc88a;
        color: #fff;
    }

    /* Activity Feed (Dashed Style) */
    .feed-container {
        position: relative;
        padding-left: 20px;
    }

    .feed-container::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        border-left: 2px dashed #d1d3e2;
    }

    .feed-item {
        position: relative;
        padding-bottom: 20px;
    }

    .feed-icon {
        position: absolute;
        left: -28px;
        top: 0;
        width: 16px;
        height: 16px;
        background: #fff;
        border: 2px solid var(--crm-blue);
        border-radius: 50%;
    }

    /* Sub-Tabs Navigation */
    .profile-tabs {
        border-bottom: 1px solid var(--crm-border);
        margin-bottom: 15px;
    }

    .profile-tabs .nav-link {
        border: none;
        color: #858796;
        font-weight: 600;
        padding: 10px 20px;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
    }

    .profile-tabs .nav-link.active {
        color: var(--crm-blue);
        border-bottom-color: var(--crm-blue);
        background: transparent;
    }

    .stat-card {
        border-left: 4px solid var(--crm-blue);
        padding: 10px 15px;
        background: #fff;
        border-radius: 4px;
        box-shadow: 0 .15rem 1.75rem 0 rgba(58, 59, 69, .1);
    }

</style>

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <div class="small text-muted mb-1">Students / Profiles / #{{ $student->uid }}</div>
            <h5 class="fw-bold mb-0 text-dark">{{ $student->name }}</h5>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-white border shadow-sm"><i class="fas fa-user-check me-1"></i> Assign</button>
            <button class="btn btn-sm btn-primary shadow-sm px-3"><i class="fas fa-plus me-1"></i> Add Activity</button>
        </div>
    </div>

    <div class="row mb-4">
        @foreach($student->stats as $stat)
        <div class="col-md-4">
            <div class="stat-card">
                <div class="small font-weight-bold text-uppercase mb-1" style="font-size: 10px;">{{ $stat['label'] }}</div>
                <div class="h5 mb-0 font-weight-bold {{ $stat['color'] }}">{{ $stat['value'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="chevron-bar shadow-sm">
        @foreach($student->steps as $step)
        @php
        $active = ($student->current_step == $step);
        $done = array_search($step, $student->steps) < array_search($student->current_step, $student->steps);
            @endphp
            <div class="chevron-item {{ $active ? 'active' : ($done ? 'completed' : '') }}">
                {{ $step }}
            </div>
            @endforeach
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($student->name) }}&background=4e73df&color=fff&size=80" class="rounded-circle mb-2 border p-1">
                        <div class="fw-bold text-dark">{{ $student->name }}</div>
                        <span class="badge bg-light text-primary border small">{{ $student->visa_type }}</span>
                    </div>

                    <div class="border-top pt-3">
                        <div class="row mb-2">
                            <div class="col-5 text-muted small">Assigned To</div>
                            <div class="col-7 fw-bold small text-end">{{ $student->assigned_to }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 text-muted small">Lead Source</div>
                            <div class="col-7 fw-bold small text-end">{{ $student->source }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 text-muted small">Country</div>
                            <div class="col-7 fw-bold small text-end">{{ $student->country }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 text-muted small">Phone</div>
                            <div class="col-7 fw-bold small text-end">{{ $student->phone }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <ul class="nav nav-tabs profile-tabs">
                <li class="nav-item"><a class="nav-link active" href="#">Activity Log</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Documents</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Timeline</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Notes</a></li>
            </ul>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="feed-container mt-2">
                        @foreach($student->activities as $act)
                        <div class="feed-item">
                            <div class="feed-icon"></div>
                            <div class="d-flex justify-content-between">
                                <div class="small">
                                    <span class="fw-bold text-dark">{{ $act['user'] }}</span>
                                    <span class="text-muted">{{ $act['msg'] }}</span>
                                </div>
                                <div class="text-muted" style="font-size: 11px;">{{ $act['time'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer bg-white text-center">
                    <button class="btn btn-link btn-sm text-decoration-none text-muted">View full audit trail</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
