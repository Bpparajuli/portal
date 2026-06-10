@extends('layouts.staff')

@section('title', 'Staff Profile - ' . $staff->name)
@section('page-title', 'Staff Profile')

@section('content')
<div class="container-lg py-4">

    <div class="rounded-4 p-4 mb-4"
        style="background:linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);box-shadow:0 4px 20px rgba(13,202,240,0.2);">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                    style="width:48px;height:48px;background:rgba(255,255,255,0.2);">
                    <i class="fas fa-user-tie fa-lg text-white"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-white">{{ $staff->name }}</h4>
                    <p class="mb-0 small" style="color:rgba(255,255,255,0.7);">{{ $staff->email }}</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('agent.staff.edit', $staff->slug) }}" class="btn btn-sm px-3 text-white"
                    style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('agent.users.show', $agent->slug) }}" class="btn btn-sm px-3"
                    style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.2);">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4 text-center">
                    <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-3"
                        style="width:80px;height:80px;background:linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);">
                        <i class="fas fa-user-tie fa-2x text-white"></i>
                    </div>
                    <h5 class="fw-bold mb-1">{{ $staff->name }}</h5>
                    <p class="text-muted small mb-2">{{ $staff->email }}</p>
                    <span class="badge {{ $staff->active ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                        <i class="fas fa-circle me-1" style="font-size:0.6rem;"></i>
                        {{ $staff->active ? 'Active' : 'Inactive' }}
                    </span>
                    <hr>
                    <div class="text-start small">
                        <div class="mb-2">
                            <span class="text-muted">Contact:</span>
                            <span class="fw-semibold float-end">{{ $staff->contact ?? 'N/A' }}</span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted">Address:</span>
                            <span class="fw-semibold float-end">{{ $staff->address ?? 'N/A' }}</span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted">Role:</span>
                            <span class="fw-semibold float-end text-capitalize">{{ $staff->role }}</span>
                        </div>
                        <div>
                            <span class="text-muted">Managed By:</span>
                            <span class="fw-semibold float-end">{{ $agent->business_name ?? $agent->name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="px-4 py-3" style="background:linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);">
                    <h6 class="fw-bold mb-0 text-white"><i class="fas fa-activity me-2"></i>Recent Activity</h6>
                    <small class="text-white-50">Staff member's recent actions</small>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($activities as $act)
                            <div class="list-group-item border-0 py-3 px-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                        style="width:32px;height:32px;background:rgba(13,202,240,0.1);">
                                        <i class="fas fa-circle" style="color:#0dcaf0;font-size:0.6rem;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 small">{{ $act->description }}</p>
                                        <small class="text-muted">
                                            <i class="far fa-clock me-1"></i>{{ $act->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No activity recorded yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
