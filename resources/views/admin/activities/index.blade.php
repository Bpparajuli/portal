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
        <form action="{{ route('admin.activities.clearAll') }}" method="POST" onsubmit="return confirm('Delete ALL activity logs? This cannot be undone.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt me-1"></i>Clear All</button>
        </form>
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
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.activities.show', $activity) }}" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                <form action="{{ route('admin.activities.destroy', $activity) }}" method="POST" onsubmit="return confirm('Delete this activity record?')" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
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
@endsection