@extends('layouts.staff')
@section('title', 'My Dashboard')
@section('page-title', 'My Dashboard')
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --staff-primary: #1a0262;
    --staff-accent: #820b5c;
    --staff-card-shadow: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
    --staff-card-hover: 0 10px 40px rgba(0,0,0,.08), 0 2px 8px rgba(0,0,0,.06);
}
.staff-grid { display:grid; gap:1.25rem; }
.staff-grid-2 { grid-template-columns: 1fr 1fr; }
.staff-grid-3 { grid-template-columns: repeat(3, 1fr); }
@media (max-width:991px) { .staff-grid-2, .staff-grid-3 { grid-template-columns: 1fr; } }
@media (max-width:575px) { .staff-grid-2, .staff-grid-3 { grid-template-columns: 1fr; } }
.chart-card {
    background:#fff; border-radius:16px; padding:0;
    box-shadow:var(--staff-card-shadow); transition:all .25s; overflow:hidden;
}
.chart-card:hover { box-shadow:var(--staff-card-hover); }
.chart-card .sc-head {
    padding:.85rem 1.25rem; border-bottom:1px solid #f3f4f6;
    display:flex; justify-content:space-between; align-items:center;
}
.chart-card .sc-title {
    font-size:.85rem; font-weight:700; margin:0; display:flex; align-items:center; gap:.5rem;
}
.chart-card .sc-icon {
    width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;
    font-size:.75rem; flex-shrink:0;
}
.chart-body { padding:1rem 1.25rem 1.25rem; }
.chart-fill canvas { width:100%!important; min-height:200px!important; }
.chart-fill-sm canvas { width:100%!important; min-height:140px!important; }
.view-all-link {
    font-size:.72rem; font-weight:600; color:var(--staff-accent); text-decoration:none;
    display:inline-flex; align-items:center; gap:.35rem; transition:all .15s;
}
.view-all-link:hover { gap:.55rem; color:#a00b75; }

.status-pill {
    display:inline-flex; align-items:center; gap:.3rem;
    font-size:.65rem; font-weight:600; padding:.25rem .65rem;
    border-radius:20px; white-space:nowrap; text-decoration:none;
    transition:all .15s;
}
.status-pill:hover { opacity:.8; transform:translateY(-1px); }

.activity-item {
    display:flex; align-items:flex-start; gap:.75rem; padding:.7rem 0;
    border-bottom:1px solid #f3f4f6;
}
.activity-item:last-child { border-bottom:none; }
.activity-icon {
    width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;
    flex-shrink:0; font-size:.7rem; margin-top:2px;
}
.activity-text { flex:1; min-width:0; font-size:.8rem; line-height:1.4; }
.activity-time { font-size:.65rem; color:#9ca3af; white-space:nowrap; margin-left:.5rem; }
</style>
@endpush

@section('staff-content')

<div class="container-fluid px-0" style="font-family:'Inter',sans-serif;">

    {{-- ═══════ HERO ═══════ --}}
    <div class="dash-hero">
        <div class="dash-hero-inner">
            <div>
                <div style="font-size:.78rem;color:rgba(255,255,255,.6);font-weight:500;margin-bottom:2px;">
                    {{ now()->format('l, F j, Y') }}
                </div>
                <div class="dash-hero-title" style="font-size:1.6rem;">Hey, {{ Auth::user()->name }} 👋</div>
                <div class="dash-hero-sub">Here&rsquo;s your activity overview</div>
            </div>
            <div class="d-flex gap-2 flex-wrap align-items-end">
                <a href="{{ route('staff.students.index') }}" class="btn btn-sm btn-glass"><i class="fas fa-users me-1"></i> Students</a>
                <a href="{{ route('staff.applications.index') }}" class="btn btn-sm btn-glass-accent"><i class="fas fa-file-alt me-1"></i> Applications</a>
            </div>
        </div>
    </div>

    {{-- ═══════ ROW 1: Monthly Task Report + Students by Stage ═══════ --}}
    <div class="staff-grid staff-grid-2" style="margin-bottom:1.25rem;">

        {{-- Monthly Task Report --}}
        <div class="chart-card">
            <div class="sc-head">
                <h5 class="sc-title"><span class="sc-icon" style="background:#eef2ff;color:#4f46e5;"><i class="fas fa-chart-line"></i></span> Monthly Task Report</h5>
            </div>
            <div class="chart-body chart-fill"><canvas id="monthlyChart"></canvas></div>
        </div>

        {{-- Students by Stage --}}
        <div class="chart-card">
            <div class="sc-head">
                <h5 class="sc-title"><span class="sc-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-layer-group"></i></span> Students by Stage</h5>
            </div>
            <div class="chart-body chart-fill"><canvas id="stageChart"></canvas></div>
        </div>
    </div>

    {{-- ═══════ ROW 2: Students by App Status + Weekly Task Report ═══════ --}}
    <div class="staff-grid staff-grid-2" style="margin-bottom:1.25rem;">

        {{-- Students by Application Status --}}
        <div class="chart-card">
            <div class="sc-head">
                <h5 class="sc-title"><span class="sc-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-chart-pie"></i></span> Students by Application Status</h5>
            </div>
            <div class="chart-body">
                @if(count($statuses))
                <div style="display:flex;flex-wrap:wrap;gap:.75rem;">
                    @foreach($statuses as $st)
                    <a href="{{ route('staff.students.index') }}?application_status_id={{ $st->id }}"
                       style="display:flex;flex-direction:column;align-items:center;gap:.2rem;min-width:64px;padding:.4rem .6rem;border-radius:10px;text-decoration:none;transition:all .15s;background:{{ $st->bg_color }}15;"
                       onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                        <span style="font-size:1.2rem;font-weight:800;color:{{ $st->text_color }};">{{ $st->count }}</span>
                        <span style="font-size:.65rem;font-weight:600;color:{{ $st->text_color }};text-align:center;">{{ $st->name }}</span>
                    </a>
                    @endforeach
                </div>
                @else
                <div style="text-align:center;padding:.75rem 0;color:#9ca3af;font-size:.8rem;">No applications yet</div>
                @endif
            </div>
        </div>

        {{-- Weekly Task Report (detailed) --}}
        <div class="chart-card">
            <div class="sc-head">
                <h5 class="sc-title"><span class="sc-icon" style="background:#fce7f3;color:#db2777;"><i class="fas fa-calendar-week"></i></span> Weekly Task Report</h5>
                <div class="d-flex gap-2">
                    <span class="badge bg-warning text-dark" style="font-size:.6rem;">{{ array_sum($weeklyTotals) }} total</span>
                </div>
            </div>
            <div class="chart-body chart-fill-sm"><canvas id="weeklyChart"></canvas></div>
        </div>
    </div>

    {{-- ═══════ ROW 3: My Activity (full width) ═══════ --}}
    <div class="chart-card" style="margin-bottom:1.25rem;">
        <div class="sc-head">
            <h5 class="sc-title"><span class="sc-icon" style="background:#e0e7ff;color:#6366f1;"><i class="fas fa-history"></i></span> My Activity</h5>
            <a href="{{ route('staff.activities') }}" class="view-all-link">
                View All Activity <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="chart-body" style="padding-top:.25rem;">
            @forelse($recentActivities as $act)
                <div class="activity-item">
                    @php
                        $iconMap = [
                            'task_completed' => ['icon' => 'fa-check-circle', 'bg' => '#d1fae5', 'fg' => '#059669'],
                            'task_created' => ['icon' => 'fa-plus-circle', 'bg' => '#e0e7ff', 'fg' => '#4f46e5'],
                            'note_added' => ['icon' => 'fa-sticky-note', 'bg' => '#fef3c7', 'fg' => '#d97706'],
                            'revenue_added' => ['icon' => 'fa-dollar-sign', 'bg' => '#d1fae5', 'fg' => '#059669'],
                            'stage_changed' => ['icon' => 'fa-random', 'bg' => '#fce7f3', 'fg' => '#db2777'],
                            'document_uploaded' => ['icon' => 'fa-file-upload', 'bg' => '#dbeafe', 'fg' => '#2563eb'],
                        ];
                        $ico = $iconMap[$act->type] ?? ['icon' => 'fa-circle', 'bg' => '#f3f4f6', 'fg' => '#6b7280'];
                    @endphp
                    <div class="activity-icon" style="background:{{ $ico['bg'] }};color:{{ $ico['fg'] }};">
                        <i class="fas {{ $ico['icon'] }}"></i>
                    </div>
                    <div class="activity-text">
                        @if($act->link)
                            <a href="{{ $act->link }}" style="color:inherit;text-decoration:none;">
                                <strong>{{ $act->description }}</strong>
                            </a>
                        @else
                            <strong>{{ $act->description }}</strong>
                        @endif
                    </div>
                    <div class="activity-time">{{ $act->created_at->diffForHumans() }}</div>
                </div>
            @empty
                <div style="text-align:center;padding:1.5rem 0;color:#6b7280;font-size:.82rem;">
                    <i class="fas fa-inbox" style="font-size:1.5rem;color:#d1d5db;display:block;margin-bottom:.5rem;"></i>
                    No activity yet
                </div>
            @endforelse
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var grid = 'rgba(0,0,0,0.04)';
    Chart.defaults.font.size = 10;
    Chart.defaults.color = '#6b7280';

    // Monthly Task Report
    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: @json($monthlyLabels),
            datasets: [
                { label:'Pending', data:@json($monthlyPending), backgroundColor:'rgba(251,191,36,.65)', borderColor:'#f59e0b', borderWidth:1, borderRadius:3 },
                { label:'Completed', data:@json($monthlyCompleted), backgroundColor:'rgba(16,185,129,.65)', borderColor:'#10b981', borderWidth:1, borderRadius:3 },
                { label:'Cancelled', data:@json($monthlyCancelled), backgroundColor:'rgba(239,68,68,.5)', borderColor:'#ef4444', borderWidth:1, borderRadius:3 },
            ]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            plugins: { legend:{ display:true, position:'bottom', labels:{ font:{size:9}, padding:6, boxWidth:10 } } },
            scales: {
                x: { stacked:true, grid:{display:false}, ticks:{maxRotation:45,font:{size:9}} },
                y: { stacked:true, beginAtZero:true, grid:{color:grid} }
            }
        }
    });

    // Students by Stage
    new Chart(document.getElementById('stageChart'), {
        type: 'bar',
        data: {
            labels: @json($stageLabels),
            datasets: [{
                label:'Students',
                data:@json($stageCounts),
                backgroundColor: @json($stageColors),
                borderColor: @json($stageColors),
                borderWidth:1,
                borderRadius:4,
            }]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            indexAxis:'y',
            plugins: { legend:{ display:false } },
            scales: {
                x: { beginAtZero:true, grid:{color:grid}, ticks:{stepSize:1} },
                y: { grid:{display:false} }
            }
        }
    });

    // Weekly Task Report
    new Chart(document.getElementById('weeklyChart'), {
        type: 'bar',
        data: {
            labels: @json($weeklyLabels),
            datasets: [
                { label:'Pending', data:@json($weeklyPending), backgroundColor:'rgba(251,191,36,.7)', borderColor:'#f59e0b', borderWidth:1, borderRadius:3 },
                { label:'Completed', data:@json($weeklyCompleted), backgroundColor:'rgba(16,185,129,.7)', borderColor:'#10b981', borderWidth:1, borderRadius:3 },
                { label:'Cancelled', data:@json($weeklyCancelled), backgroundColor:'rgba(239,68,68,.55)', borderColor:'#ef4444', borderWidth:1, borderRadius:3 },
            ]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            plugins: { legend:{ display:true, position:'bottom', labels:{ font:{size:9}, padding:6, boxWidth:10 } } },
            scales: {
                x: { stacked:true, grid:{display:false} },
                y: { stacked:true, beginAtZero:true, grid:{color:grid} }
            }
        }
    });
});
</script>
@endsection
