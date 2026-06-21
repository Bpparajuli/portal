@extends('layouts.staff')
@section('title', 'My Activity')
@section('page-title', 'My Activity')
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --staff-primary: #1a0262;
    --staff-accent: #820b5c;
}
.activity-page {
    font-family:'Inter',sans-serif; max-width:860px; margin:0 auto; padding:1.5rem 0;
}
.activity-card {
    background:#fff; border-radius:16px; overflow:hidden;
    box-shadow:0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
}
.activity-card-header {
    padding:.85rem 1.25rem; border-bottom:1px solid #f3f4f6;
    display:flex; align-items:center; gap:.65rem;
}
.activity-card-header h5 { font-size:.9rem; font-weight:700; margin:0; }
.activity-header-icon {
    width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.75rem;
}
.activity-item {
    display:flex; align-items:flex-start; gap:.85rem; padding:.85rem 1.25rem;
    border-bottom:1px solid #f3f4f6; transition:background .15s;
}
.activity-item:last-child { border-bottom:none; }
.activity-item:hover { background:#fafafa; }
.activity-icon {
    width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;
    flex-shrink:0; font-size:.75rem; margin-top:2px;
}
.activity-body { flex:1; min-width:0; }
.activity-desc { font-size:.82rem; line-height:1.4; }
.activity-desc a { color:inherit; text-decoration:none; }
.activity-desc a:hover { color:var(--staff-accent); }
.activity-meta {
    display:flex; align-items:center; gap:.75rem; margin-top:2px;
    font-size:.65rem; color:#9ca3af;
}
.activity-type-badge {
    display:inline-block; font-size:.6rem; font-weight:600; padding:.15rem .5rem;
    border-radius:20px; text-transform:capitalize;
}
</style>
@endpush

@section('staff-content')
<div class="activity-page">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem;">
        <div>
            <h4 style="font-weight:800;font-size:1.3rem;margin:0;color:var(--staff-primary);">My Activity</h4>
            <p style="font-size:.78rem;color:#6b7280;margin:2px 0 0;">All actions you&rsquo;ve performed across the system</p>
        </div>
        <a href="{{ route('staff.dashboard') }}" class="btn btn-sm btn-outline-secondary px-3">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <div class="activity-card">
        <div class="activity-card-header" style="background:linear-gradient(135deg, #eef2ff 0%, #fff 100%);">
            <div class="activity-header-icon" style="background:#6366f1;color:#fff;">
                <i class="fas fa-history"></i>
            </div>
            <h5>Activity Log</h5>
            <span class="badge bg-secondary ms-auto" style="font-size:.6rem;font-weight:600;">{{ $activities->total() }} total</span>
        </div>

        @forelse($activities as $act)
            @php
                $iconMap = [
                    'task_completed' => ['icon' => 'fa-check-circle', 'bg' => '#d1fae5', 'fg' => '#059669', 'label' => 'Task Completed'],
                    'task_created' => ['icon' => 'fa-plus-circle', 'bg' => '#e0e7ff', 'fg' => '#4f46e5', 'label' => 'Task Created'],
                    'note_added' => ['icon' => 'fa-sticky-note', 'bg' => '#fef3c7', 'fg' => '#d97706', 'label' => 'Note Added'],
                    'revenue_added' => ['icon' => 'fa-dollar-sign', 'bg' => '#d1fae5', 'fg' => '#059669', 'label' => 'Revenue Added'],
                    'stage_changed' => ['icon' => 'fa-random', 'bg' => '#fce7f3', 'fg' => '#db2777', 'label' => 'Stage Changed'],
                    'document_uploaded' => ['icon' => 'fa-file-upload', 'bg' => '#dbeafe', 'fg' => '#2563eb', 'label' => 'Document Uploaded'],
                ];
                $ico = $iconMap[$act->type] ?? ['icon' => 'fa-circle', 'bg' => '#f3f4f6', 'fg' => '#6b7280', 'label' => $act->type];
            @endphp
            <div class="activity-item">
                <div class="activity-icon" style="background:{{ $ico['bg'] }};color:{{ $ico['fg'] }};">
                    <i class="fas {{ $ico['icon'] }}"></i>
                </div>
                <div class="activity-body">
                    <div class="activity-desc">
                        @if($act->link)
                            <a href="{{ $act->link }}">{{ $act->description }}</a>
                        @else
                            {{ $act->description }}
                        @endif
                    </div>
                    <div class="activity-meta">
                        <span class="activity-type-badge" style="background:{{ $ico['bg'] }};color:{{ $ico['fg'] }};">{{ $ico['label'] }}</span>
                        <span><i class="far fa-clock me-1"></i>{{ $act->created_at->diffForHumans() }}</span>
                        @if($act->student)
                            <span><i class="fas fa-user-graduate me-1"></i>{{ $act->student->first_name }} {{ $act->student->last_name }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div style="text-align:center;padding:3rem 1.25rem;color:#6b7280;">
                <i class="fas fa-inbox" style="font-size:2rem;color:#d1d5db;display:block;margin-bottom:.75rem;"></i>
                <p style="font-size:.9rem;margin:0;">No activity recorded yet</p>
                <p style="font-size:.75rem;margin-top:.25rem;">Actions you perform will appear here</p>
            </div>
        @endforelse

        @if($activities->hasPages())
            <div style="padding:.85rem 1.25rem;border-top:1px solid #f3f4f6;">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
