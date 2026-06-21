@extends('layouts.admin')
@section('admin-content')
<div class="container-fluid p-4">
    <x-page-header title="Activity Log">
        <x-slot:actions>
            <form method="GET" class="d-flex gap-2 align-items-end flex-wrap">
                <div>
                    <label class="form-label small text-muted mb-1">Type</label>
                    <select name="type" class="form-select form-select-sm" style="min-width:130px">
                        <option value="">All Types</option>
                        @foreach($types as $t)
                            <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($t)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label small text-muted mb-1">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div>
                    <label class="form-label small text-muted mb-1">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="{{ route('admin.activities.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
                </div>
            </form>
        </x-slot:actions>
    </x-page-header>

    @if($activities->count())
    <div class="d-flex justify-content-end mb-3 gap-2">
        <x-confirm-delete
            action="admin.activities.clearAll"
            label="Clear All"
            title="Delete ALL activity logs?"
            message="This will permanently delete all activity logs. This action cannot be undone."
            mode="form"
            class="btn btn-outline-danger btn-sm"
        />
    </div>
    @endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small">
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Student</th>
                        <th style="width:100px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                    <tr>
                        <td class="text-nowrap small">{{ $activity->created_at->format('M d, H:i') }}</td>
                        <td><span class="fw-medium small">{{ $activity->user?->name ?? 'System' }}</span></td>
                        <td>
                            <span class="badge bg-info bg-opacity-10 text-info text-capitalize">{{ str_replace('_', ' ', $activity->type) }}</span>
                        </td>
                        <td class="small">{{ $activity->description }}</td>
                        <td class="small">{{ $activity->student?->full_name ?? '—' }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-primary" title="View"
                                    onclick="showActivity({{ $activity->id }})"><i class="fas fa-eye"></i></button>
                                <x-confirm-delete
                                    action="admin.activities.destroy"
                                    :id="$activity->id"
                                    label=""
                                    title="Delete Activity?"
                                    message="This will permanently delete this activity record."
                                    mode="form"
                                    class="btn btn-sm btn-outline-danger"
                                />
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <x-empty-state icon="fa-history" title="No activities found" description="No activity records match your current filters." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($activities->hasPages())
        <div class="card-footer bg-white">{{ $activities->links() }}</div>
        @endif
    </div>
</div>

<div class="modal fade" id="activityModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2 text-primary"></i>Activity Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="activityModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const activities = @json($activities->items());
function showActivity(id) {
    const a = activities.find(x => x.id === id);
    if (!a) return;
    let html = `<table class="table table-bordered mb-0">
        <tr><th class="bg-light" style="width:160px">Type</th><td><span class="badge bg-info bg-opacity-10 text-info text-capitalize">${(a.type || '').replace(/_/g, ' ')}</span></td></tr>
        <tr><th class="bg-light">User</th><td>${a.user?.name || 'System'}</td></tr>
        <tr><th class="bg-light">Description</th><td>${a.description || ''}</td></tr>
        <tr><th class="bg-light">Date/Time</th><td>${new Date(a.created_at).toLocaleString()}</td></tr>`;
    if (a.student) html += `<tr><th class="bg-light">Related Student</th><td><a href="/admin/students/${a.student.id}" class="text-decoration-none">${a.student.first_name || ''} ${a.student.last_name || ''}</a></td></tr>`;
    if (a.application) html += `<tr><th class="bg-light">Related Application</th><td><a href="/admin/applications/${a.application.id}" class="text-decoration-none">${a.application.application_number || '#' + a.application.id}</a></td></tr>`;
    html += `</table>`;
    document.getElementById('activityModalBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('activityModal')).show();
}
</script>
@endpush
@endsection
