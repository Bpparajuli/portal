@extends('layouts.admin')
@section('admin-content')
<div class="container-fluid p-4">
    <x-page-header title="Activity Log" />

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'all' ? 'active' : '' }}" href="{{ route('admin.activities.index', ['tab' => 'all']) }}">
                <i class="fas fa-list me-1"></i>All Activities
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'users' ? 'active' : '' }}" href="{{ route('admin.activities.index', ['tab' => 'users']) }}">
                <i class="fas fa-users me-1"></i>Per User
            </a>
        </li>
    </ul>

@if($tab === 'users')
    {{-- ========== PER-USER TAB ========== --}}
    <div class="row g-3">
        @forelse($userActivities as $ua)
        @php $user = $ua->user; @endphp
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $user?->name ?? 'Deleted User' }}</strong>
                        <span class="badge bg-secondary ms-2">{{ $ua->total_count }} actions</span>
                    </div>
                    <small class="text-muted">Last: {{ \Carbon\Carbon::parse($ua->last_activity)->diffForHumans() }}</small>
                </div>
                <div class="card-body p-2" style="max-height:300px;overflow-y:auto">
                    <ul class="list-unstyled mb-0 small">
                        @foreach($ua->recent as $act)
                        <li class="px-2 py-1 border-bottom border-light">
                            <span class="badge bg-info bg-opacity-10 text-info text-capitalize me-1" style="font-size:10px">{{ str_replace('_', ' ', $act->type) }}</span>
                            {{ \Illuminate\Support\Str::limit($act->description, 70) }}
                            <span class="text-muted float-end">{{ $act->created_at->format('M d, H:i') }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div class="card-footer bg-white text-end py-1">
                    <a href="{{ route('admin.activities.index', ['tab' => 'all', 'user_id' => $ua->user_id]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-filter me-1"></i>View all from {{ $user?->name ?? 'this user' }}
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <x-empty-state icon="fa-users" title="No user activity" description="No activity records found for any user." />
        </div>
        @endforelse
    </div>
    @if($userActivities->hasPages())
    <div class="mt-3">{{ $userActivities->links() }}</div>
    @endif

@else
    {{-- ========== ALL ACTIVITIES TAB ========== --}}
    <form method="GET" class="d-flex gap-2 align-items-end flex-wrap mb-3">
        <input type="hidden" name="tab" value="all">
        <div>
            <label class="form-label small text-muted mb-1">Search</label>
            <input type="text" name="search" class="form-control form-control-sm" style="min-width:180px" placeholder="Search description..." value="{{ request('search') }}">
        </div>
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
            <label class="form-label small text-muted mb-1">Person</label>
            <select name="user_id" class="form-select form-select-sm" style="min-width:150px">
                <option value="">All Users</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
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
            <a href="{{ route('admin.activities.index', ['tab' => 'all']) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
        </div>
    </form>

    @if($activities->count())
    <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
        <div>
            <span class="small text-muted" id="selectedCount">0 selected</span>
        </div>
        <div>
            <button type="button" class="btn btn-outline-danger btn-sm" id="deleteSelectedBtn" disabled onclick="bulkDelete()">
                <i class="fas fa-trash me-1"></i>Delete Selected
            </button>
        </div>
    </div>
    @endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small">
                    <tr>
                        <th style="width:40px"><input type="checkbox" id="selectAll" onchange="toggleAll(this)"></th>
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
                        <td><input type="checkbox" value="{{ $activity->id }}" class="activity-checkbox" onchange="updateSelected()"></td>
                        <td class="text-nowrap small">{{ $activity->created_at->format('M d, H:i') }}</td>
                        <td><span class="fw-medium small">{{ $activity->user?->name ?? 'System' }}</span></td>
                        <td>
                            <span class="badge bg-info bg-opacity-10 text-info text-capitalize">{{ str_replace('_', ' ', $activity->type) }}</span>
                        </td>
                        <td class="small">{{ $activity->description }}</td>
                        <td class="small">{{ $activity->student?->full_name ?? '—' }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                @php $viewUrl = $activity->view_url; @endphp
                                @if($viewUrl)
                                <a href="{{ $viewUrl }}" class="btn btn-sm btn-outline-primary" title="View" target="_blank"><i class="fas fa-eye"></i></a>
                                @else
                                <button type="button" class="btn btn-sm btn-outline-secondary" title="View"
                                    onclick="showActivity({{ $activity->id }})"><i class="fas fa-eye"></i></button>
                                @endif
                                <x-confirm-delete
                                    action="admin.activities.destroy"
                                    :id="$activity->id"
                                    label=""
                                    title="Delete Activity?"
                                    message="This will permanently delete this activity record."
                                    class="btn btn-sm btn-outline-danger"
                                />
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
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
@endif
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
const activities = @json($activities instanceof \Illuminate\Pagination\LengthAwarePaginator ? $activities->items() : []);
function toggleAll(source) {
    document.querySelectorAll('.activity-checkbox').forEach(cb => cb.checked = source.checked);
    updateSelected();
}
function updateSelected() {
    const checked = document.querySelectorAll('.activity-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = checked + ' selected';
    document.getElementById('deleteSelectedBtn').disabled = checked === 0;
}
function bulkDelete() {
    const checked = document.querySelectorAll('.activity-checkbox:checked');
    if (!checked.length) return;
    const count = checked.length;
    const ids = Array.from(checked).map(cb => cb.value);
    Swal.fire({
        title: 'Delete ' + count + ' activities?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete them!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (!result.isConfirmed) return;
        fetch('{{ route("admin.activities.bulkDestroy") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: data.message || 'Deleted.', timer: 1500, showConfirmButton: false });
                setTimeout(() => location.reload(), 1500);
            } else {
                Swal.fire({ icon: 'error', title: 'Error!', text: data.message || 'Something went wrong.' });
            }
        }).catch(() => {
            Swal.fire({ icon: 'error', title: 'Error!', text: 'Something went wrong.' });
        });
    });
}
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
