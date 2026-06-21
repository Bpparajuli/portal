@push('styles')
    <style>
        .bottom-row {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1.4fr;
            gap: 1.25rem;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }

        .bottom-row>.hd-card {
            min-width: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .pipeline-progress-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-size: .68rem;
        }

        .pipeline-progress-item .pp-name {
            font-weight: 600;
            color: #334155;
            min-width: 100px;
            flex-shrink: 0;
        }

        .pipeline-bar {
            flex: 1;
            height: 6px;
            background: #f1f5f9;
            border-radius: 10px;
            overflow: hidden;
        }

        .pipeline-bar-fill {
            height: 100%;
            border-radius: 10px;
            transition: width .4s ease;
        }

        .pipeline-progress-item .pp-count {
            color: #64748b;
            min-width: 50px;
            text-align: right;
            flex-shrink: 0;
        }

        .qa-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            background: #f8fafc;
            border-radius: var(--hd-radius);
            margin-bottom: 6px;
            cursor: pointer;
            transition: all .12s;
        }

        .qa-item:hover {
            background: #fff;
            transform: translateX(3px);
            box-shadow: var(--hd-shadow);
        }

        .qa-icon {
            width: 32px;
            height: 32px;
            border-radius: var(--hd-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            flex-shrink: 0;
        }

        .qa-label {
            font-size: .78rem;
            font-weight: 600;
            line-height: 1.2;
            color: #1e293b;
        }

        .qa-desc {
            font-size: .6rem;
            color: #6b7280;
        }

        .weekly-task-container {
            flex: 1;
            overflow-y: auto;
        }

        .weekly-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f1f5f9;
        }

        .weekly-summary-item {
            text-align: center;
        }

        .weekly-summary-item .num {
            font-size: 1.1rem;
            font-weight: 700;
            display: block;
        }

        .weekly-summary-item .lbl {
            font-size: .58rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .weekly-summary-bar {
            height: 4px;
            border-radius: 4px;
            overflow: hidden;
            display: flex;
            margin-bottom: 12px;
        }

        .weekly-table {
            width: 100%;
            font-size: .68rem;
            border-collapse: collapse;
        }

        .weekly-table th,
        .weekly-table td {
            padding: 5px 6px;
            text-align: center;
            border-bottom: 1px solid #f1f5f9;
        }

        .weekly-table th:first-child,
        .weekly-table td:first-child {
            text-align: left;
        }

        .staff-row {
            cursor: pointer;
            transition: background .12s;
        }

        .staff-row:hover {
            background: #f1f5f9;
        }

        .task-day {
            padding: 7px 10px;
            border-radius: var(--hd-radius);
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            font-size: .7rem;
            transition: all .12s;
            cursor: pointer;
        }

        .task-day:hover {
            background: #fff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .05);
        }

        .task-day.today {
            background: linear-gradient(135deg, #fef3c7, #fffbeb);
            border-left: 3px solid #f59e0b;
        }

        .task-day .day-name {
            font-weight: 600;
            font-size: .78rem;
            color: #1e293b;
        }

        .task-stat {
            text-align: center;
            min-width: 38px;
        }

        .task-stat .count {
            font-size: .9rem;
            font-weight: 700;
            display: block;
            line-height: 1.2;
        }

        .task-stat .label {
            font-size: .5rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .2px;
        }

        .task-stat.overdue .count {
            color: #dc2626;
        }

        .task-stat.completed .count {
            color: #10b981;
        }

        .task-stat.upcoming .count {
            color: #3b82f6;
        }

        @media (max-width: 992px) {
            .bottom-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }
    </style>
@endpush

<div class="bottom-row">
    {{-- Pipeline Strategy --}}
    <div class="hd-card">
        <div class="hd-card-title"><i class="fas fa-chart-line" style="color:var(--crm-primary);"></i> Pipeline Strategy
        </div>
        @php
            $pipelineStages = $stages
                ->filter(function ($s) use ($students) {
                    return isset($students[$s->id]) && $students[$s->id]->isNotEmpty();
                })
                ->sortByDesc(function ($s) use ($students) {
                    return $students[$s->id]->count();
                })
                ->values();
            $totalSt = $stats['total_students'] ?? 1;
            $maxStageCount = 0;
            $maxStageName = '';
            foreach ($pipelineStages as $stage) {
                $count = $students[$stage->id]->count();
                if ($count > $maxStageCount) {
                    $maxStageCount = $count;
                    $maxStageName = $stage->name;
                }
            }
        @endphp
        @foreach ($pipelineStages->take(10) as $stage)
            @php
                $colCount = isset($students[$stage->id]) ? $students[$stage->id]->count() : 0;
                $percent = $totalSt > 0 ? ($colCount / $totalSt) * 100 : 0;
            @endphp
            <div class="pipeline-progress-item">
                <span class="pp-name">{{ $stage->name }}</span>
                <div class="pipeline-bar">
                    <div class="pipeline-bar-fill" style="width: {{ $percent }}%; background: {{ $stage->color }};">
                    </div>
                </div>
                <span class="pp-count">{{ $colCount }} / {{ $totalSt }}</span>
            </div>
        @endforeach

    </div>

    {{-- Quick Actions --}}
    <div class="hd-card">
        <div class="hd-card-title"><i class="fas fa-bolt" style="color:#f59e0b;"></i> Quick Actions</div>
        <div style="flex:1;display:flex;flex-direction:column;justify-content:center;">
            <div class="qa-item" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                <div class="qa-icon" style="background:#e0e7ff;color:var(--crm-primary);"><i
                        class="fas fa-user-plus"></i></div>
                <div>
                    <div class="qa-label">Add Student</div>
                    <div class="qa-desc">Create a new profile</div>
                </div>
            </div>
            <div class="qa-item" onclick="setView('calendar')">
                <div class="qa-icon" style="background:#fed7aa;color:#d97706;"><i class="fas fa-calendar-alt"></i></div>
                <div>
                    <div class="qa-label">Calendar View</div>
                    <div class="qa-desc">See all tasks</div>
                </div>
            </div>
            <div class="qa-item"
                onclick="window.location.href='{{ route('crm.dashboard', ['activity_filter' => 'overdue']) }}'">
                <div class="qa-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="qa-label">Overdue Tasks ({{ $stats['overdue'] ?? 0 }})</div>
                    <div class="qa-desc">Needs attention</div>
                </div>
            </div>
            <div class="qa-item" onclick="window.location.href='{{ route('crm.export') }}'">
                <div class="qa-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-download"></i></div>
                <div>
                    <div class="qa-label">Export Data</div>
                    <div class="qa-desc">Download student list</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Weekly Task Review --}}
    <div class="hd-card">
        <div class="hd-card-title"><i class="fas fa-calendar-week" style="color:#8b5cf6;"></i> Weekly Task Review</div>
        <div id="weeklyTaskContainer" class="weekly-task-container">
            <div class="text-center py-4">
                <div class="crm-loader-spinner" style="width:28px;height:28px;margin:0 auto;"></div>
                <div style="font-size:.7rem;color:#6b7280;margin-top:8px;">Loading tasks...</div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        window.CrmWeeklyReview = (function() {
            'use strict';
            var CRM = window.CrmCore?.getInstance();
            var sl = function() {
                CRM?.showLoader();
            };
            var hl = function() {
                CRM?.hideLoader();
            };
            var csrf = function() {
                return CRM ? CRM.getCsrfToken() : document.querySelector('meta[name="csrf-token"]')?.content;
            };
            var esc = function(s) {
                return CRM ? CRM.escapeHtml(s) : (s ? String(s) : '');
            };

            async function load() {
                var c = document.getElementById('weeklyTaskContainer');
                if (!c) return;
                sl();
                try {
                    var r = await fetch('{{ route('crm.weekly.tasks') }}', {
                        headers: {
                            'X-CSRF-TOKEN': csrf()
                        }
                    });
                    var d = await r.json();
                    hl();
                    if (d.is_admin) {
                        var staff = d.staff_data || [];
                        if (!staff.length) {
                            c.innerHTML =
                                '<div class="text-center text-muted py-4" style="font-size:.75rem;"><i class="fas fa-users fa-2x mb-2 d-block"></i>No staff members</div>';
                            return;
                        }
                        var html =
                            '<table class="weekly-table"><thead><tr><th>Staff</th><th style="color:#dc2626;">Overdue</th><th style="color:#10b981;">Done</th><th style="color:#3b82f6;">Upcoming</th><th>Total</th></tr></thead><tbody>';
                        staff.forEach(function(s, i) {
                            var did = 'sd-' + i;
                            var sn = (s.staff_name || '').split(' ').slice(0, 2).join(' ');
                            html += '<tr class="staff-row" data-detail="' + did + '" style="' + (s
                                    .total_all > 0 ? 'cursor:pointer' : '') + '">' +
                                '<td><strong>' + esc(sn) + '</strong>' + (s.total_all === 0 ?
                                    ' <span class="badge bg-secondary" style="font-size:.55rem;">No Tasks</span>' :
                                    '') + '</td>' +
                                '<td class="' + (s.total_overdue > 0 ? 'text-danger fw-bold' :
                                    'text-muted') + '">' + s.total_overdue + '</td>' +
                                '<td class="' + (s.total_completed > 0 ? 'text-success fw-bold' :
                                    'text-muted') + '">' + s.total_completed + '</td>' +
                                '<td class="' + (s.total_upcoming > 0 ? 'text-primary fw-bold' :
                                    'text-muted') + '">' + s.total_upcoming + '</td>' +
                                '<td class="fw-bold">' + s.total_all + '</td></tr>';
                            html += '<tr id="' + did +
                                '" style="display:none;"><td colspan="5" style="padding:0;"><div class="p-2" style="background:#f8fafc;font-size:.65rem;color:#6b7280;">Click a staff row to see daily breakdown</div></td></tr>';
                        });
                        html += '</tbody></table>';
                        c.innerHTML = html;
                        document.querySelectorAll('.staff-row').forEach(function(row) {
                            row.addEventListener('click', function() {
                                var el = document.getElementById(this.dataset.detail);
                                if (el) {
                                    var vis = el.style.display !== 'none';
                                    el.style.display = vis ? 'none' : 'table-row';
                                    this.style.background = vis ? '' : '#f1f5f9';
                                }
                            });
                        });
                    } else {
                        var wd = d.week_data,
                            days = wd?.days || [];
                        if (!days.length) {
                            c.innerHTML =
                                '<div class="text-center text-muted py-4" style="font-size:.75rem;"><i class="fas fa-calendar-check fa-2x mb-2 d-block"></i>No tasks this week</div>';
                            return;
                        }
                        var totalAll = wd.total_all || 0,
                            totalCompleted = wd.total_completed || 0,
                            totalOverdue = wd.total_overdue || 0;
                        var donePct = totalAll > 0 ? (totalCompleted / totalAll * 100) : 0;
                        var overduePct = totalAll > 0 ? (totalOverdue / totalAll * 100) : 0;
                        var html = '<div class="weekly-summary">' +
                            '<div class="weekly-summary-item"><span class="num">' + totalAll +
                            '</span><span class="lbl">Total</span></div>' +
                            '<div class="weekly-summary-item"><span class="num" style="color:#10b981;">' +
                            totalCompleted + '</span><span class="lbl">Done</span></div>' +
                            '<div class="weekly-summary-item"><span class="num" style="color:#dc2626;">' +
                            totalOverdue + '</span><span class="lbl">Overdue</span></div>' +
                            '</div>' +
                            '<div class="weekly-summary-bar">' +
                            (donePct > 0 ? '<div style="flex:' + donePct + ';background:#10b981;"></div>' :
                            '') +
                            (overduePct > 0 ? '<div style="flex:' + overduePct +
                                ';background:#dc2626;"></div>' : '') +
                            (100 - donePct - overduePct > 0 ? '<div style="flex:' + (100 - donePct -
                                overduePct) + ';background:#e2e8f0;"></div>' : '') +
                            '</div>';
                        days.forEach(function(day) {
                            html += '<div class="task-day ' + (day.is_today ? 'today' : '') +
                                '" onclick="window.setView(\'calendar\')">' +
                                '<div class="day-name">' + esc(day.day) + (day.is_today ?
                                    ' <span class="badge bg-warning" style="font-size:.5rem;vertical-align:middle;">Today</span>' :
                                    '') + '</div>' +
                                '<div style="display:flex;gap:8px;">' +
                                '<span class="task-stat overdue"><span class="count">' + day.overdue +
                                '</span><span class="label">Overdue</span></span>' +
                                '<span class="task-stat completed"><span class="count">' + day
                                .completed + '</span><span class="label">Done</span></span>' +
                                '<span class="task-stat upcoming"><span class="count">' + day.upcoming +
                                '</span><span class="label">Next</span></span>' +
                                '</div></div>';
                        });
                        c.innerHTML = html;
                    }
                } catch (err) {
                    hl();
                    c.innerHTML =
                        '<div class="text-center text-muted py-4" style="font-size:.75rem;"><i class="fas fa-exclamation-circle fa-2x mb-2 d-block"></i>Failed to load</div>';
                }
            }
            return {
                load: load
            };
        })();

        document.addEventListener('DOMContentLoaded', function() {
            if ((document.querySelector('[name="view"]')?.value || 'kanban') === 'kanban') {
                setTimeout(function() {
                    window.CrmWeeklyReview?.load();
                }, 150);
            }
        });
    </script>
@endpush
